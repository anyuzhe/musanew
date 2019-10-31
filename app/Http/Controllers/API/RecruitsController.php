<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Models\Job;

class RecruitsController extends ApiBaseCommonController
{
    use SoftDeletes;

    public $model_name = Recruit::class;

    public function storeValidate()
    {
        return [
            [
                'need_num' => 'numeric|required|min:1|max:9999',
                'job_id' => 'numeric|required|min:1',
            ],
            [
                'job_id.required'=>'必须选择职位',
                'job_id.numeric'=>'必须选择职位',
                'job_id.min'=>'必须选择职位',
                'need_num.min'=>'招聘人数必须大于0',
                'need_num.max'=>'招聘人数必须小于99999',
            ]
        ];
    }

    public function authLimit(&$model)
    {
        $in_recruit = $this->request->get('in_recruit', null);
        $resume_id = $this->request->get('resume_id', null);
        $user = $this->getUser();
        if ($user) {
            $company = $this->getCurrentCompany();
            if ($company) {
//                $model = $model->where('company_id', $company->id)->whereIn('status', [1,4]);
                //委托了的招聘
                $has_entrust_ids = Entrust::pluck('company_job_recruit_id')->toArray();
                $model = $model->where('company_id', $company->id)->whereNotIn('id', $has_entrust_ids);
                if($in_recruit){
                    if($in_recruit==1){
                        $model = $model->whereIn('status', [1]);
                    }else{
                        $model = $model->whereIn('status', [4,5]);
                    }
                }else{
                    $model = $model->whereIn('status', [1,4,5]);
                }
                if($resume_id){
                    $model = $model->whereNotIn('id', RecruitResume::where('resume_id', $resume_id)->pluck('company_job_recruit_id')->toArray());
                }
            }else{
                $model = $model->where('id', 0);
            }
        }
        return null;
    }

    public function _after_get(&$recruits)
    {
        $recruits->load('job');
        $recruits->load('leading');
        $recruits->load('entrusts');

        $entrustRes = app()->build(EntrustsRepository::class);

        $job_ids = [];
        $recruits = $recruits->toArray();
        foreach ($recruits as $recruit) {
            $job_ids[] = $recruit['job']['id'];
        }
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($recruits as &$recruit) {
            $recruit['job'] = $jobs[$recruit['job']['id']];
            $recruit['residue_num'] = $recruit['need_num'] - $recruit['done_num'] - $recruit['wait_entry_num'];
            $recruit['residue_num'] = $recruit['residue_num']>0?$recruit['residue_num']:0;
            $recruit['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($recruit);
        }
        return $recruits;
    }

    public function _after_find(&$data)
    {
        $data->leading;
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            $entrust = Entrust::find($entrust_id);
            if($entrust){
                $data->status = app()->build(EntrustsRepository::class)->getStatusByEntrustAndRecruit($entrust->status, $data->status);
                $data->resume_num = $entrust->resume_num;
                $data->new_resume_num = $entrust->new_resume_num;
                $data->created_at = $entrust->created_at;
            }
        }
        if($data->company_id==$this->getCurrentCompany()->id){
            $data->is_party = 1;
        }else{
            $data->is_party = 0;
        }
        $data->job = app()->build(JobsRepository::class)->getData($data->job);

        $entrustRes = app()->build(EntrustsRepository::class);
        $data['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($data);
        $data['residue_num'] = $data['need_num'] - $data['done_num'] - $data['wait_entry_num'];
        $data['residue_num'] = $data['residue_num']>0?$data['residue_num']:0;
    }

    public function afterStore($obj, $data)
    {
        $id = $obj->id;
        if(isset($data['job_id'])){
            $job = Job::find($data['job_id']);
            if($job)
                $obj->company_id = $job->company_id;
        }
        $obj->creator_id = $this->getUser()->id;
        $obj->true_created_at = $obj->created_at;
        $obj->save();
        return $this->apiReturnJson(0);
    }

    //排序
    protected function modelGetSort(&$model)
    {
        $model = $model->orderBy('status','asc')->orderBy('updated_at','desc');
        return $model;
    }

    public function afterUpdate($id, $data)
    {
        if(isset($data['leading_id'])){
            Entrust::where('company_job_recruit_id', $id)->update(['leading_id'=>$data['leading_id']]);
        }
        return $this->apiReturnJson(0);
    }

    public function finish()
    {
        $id = $this->request->get('id');
        if(!$id){
            $id = $this->request->get('recruit_id');
        }
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            $entrust = Entrust::find($entrust_id);
            if(!$entrust)
                return $this->apiReturnJson(9999);
            $entrust->status = -1;
            $entrust->end_at = date('Y-m-d H:i:s');
            $entrust->save();
            app()->build(RecruitRepository::class)->generateEndLog($entrust->recruit, $entrust);
        }else{
            $obj = Recruit::find($id);
            if(!$obj)
                return $this->apiReturnJson(9999);
            $obj->status = 4;
            $obj->end_at = date('Y-m-d H:i:s');
            $obj->save();
            app()->build(RecruitRepository::class)->generateEndLog($obj);
        }
        return $this->apiReturnJson(0);
    }

    public function pause()
    {
        $id = $this->request->get('id');
        if(!$id){
            $id = $this->request->get('recruit_id');
        }
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            $entrust = Entrust::find($entrust_id);
            if(!$entrust)
                return $this->apiReturnJson(9999);
            $entrust->status = 6;
            $entrust->pause_at = date('Y-m-d H:i:s');
            $entrust->save();
            app()->build(RecruitRepository::class)->generateEndLog($entrust->recruit, $entrust);
        }else{
            $obj = Recruit::find($id);
            if(!$obj)
                return $this->apiReturnJson(9999);
            $obj->status = 6;
            $obj->pause_at = date('Y-m-d H:i:s');
            $obj->save();
            app()->build(RecruitRepository::class)->generateEndLog($obj);
        }
        return $this->apiReturnJson(0);
    }

    public function restart()
    {
        $id = $this->request->get('id');
        if(!$id){
            $id = $this->request->get('recruit_id');
        }
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            Entrust::where('id', $id)->where('id', $entrust_id)->update(['status'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        }else{
            $this->getModel()->where('id', $id)->update(['status'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        }
        return $this->apiReturnJson(0);
    }

    public function start()
    {
        $id = $this->request->get('id');
        if(!$id){
            $id = $this->request->get('recruit_id');
        }
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            Entrust::where('id', $id)->where('id', $entrust_id)->update(['status'=>1]);
        }else{
            $this->getModel()->where('id', $id)->update(['status'=>1]);
        }
        return $this->apiReturnJson(0);
    }

    public function outsourceSort(&$model)
    {
        $model = $model->orderBy("FIELD(status, 1) desc")->orderBy('updated_at','desc');
        return $model;
    }

    public function outsourceList()
    {
        $model = $this->getModel();

        $company = $this->getCurrentCompany();
        if ($company) {
            //委托了的招聘
            $has_entrust_ids = Entrust::pluck('company_job_recruit_id')->toArray();
            $model = $model->where('company_id', $company->id)->whereIn('id', $has_entrust_ids);
        }else{
            $model = $model->where('id', 0);
        }
        $model = $this->modelPipeline([
            'modelGetSearch',
            'modelGetSort',
        ],$model);
        $model_data = clone $model;
        $count = $model->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
            'outsourceSort',
            'modelByAfterGet',
        ],$model_data);

        $companies = Company::all()->keyBy('id')->toArray();

        $entrustRes = app()->build(EntrustsRepository::class);
        foreach ($list as &$v) {
            foreach ($v['entrusts'] as &$entrust) {

                if(isset($companies[$entrust['third_party_id']])){
                    $entrust['third_party'] = $companies[$entrust['third_party_id']];
                }else{
                    $entrust['third_party'] = null;
                }
                $entrust['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($v,$entrust);
                $entrust['status'] = $entrustRes->getStatusByEntrustAndRecruit($entrust['status'],$v['status']);

            }
        }

        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;

        return $this->apiReturnJson(0, $list,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }
}
