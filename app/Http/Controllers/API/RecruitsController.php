<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\UserBasicInfo;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
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

        //筛选
        $request = $this->request;
        $job_id = $request->get('job_id');
        $department_id = $request->get('department_id');
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');
        $leading_id = $request->get('leading_id');
        $third_party_id = $request->get('third_party_id');
        $recruit_search_status = $request->get('recruit_search_status');
        if($job_id){
            $model = $model->where('job_id', $job_id);
        }
        if($department_id){
            $department = CompanyDepartment::find($department_id);
            if($department->level==1){
                $departmentIds = [$department->id];
            }else{
                $departmentIds = $department->children->pluck('id')->toArray();
            }
            $jobIds = Job::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
            $model = $model->whereIn('job_id', $jobIds);
        }
        if($leading_id){
            $model = $model->where('leading_id', $leading_id);
        }
        if($start_at && !$end_at){
            $model = $model->where('created_at', '>=' ,$start_at);
        }elseif (!$start_at && $end_at){
            $model = $model->where('created_at', '<=' ,$end_at);
        }elseif ($start_at && $end_at){
            $model = $model->where('created_at', '>=' ,$start_at)->where('created_at', '<=' ,$end_at);
        }
        if($third_party_id){
            $_ids = Entrust::where('third_party_id', $third_party_id)->pluck('company_job_recruit_id')->toArray();
            $model = $model->whereIn('id', $_ids);
        }
        if($recruit_search_status){
            switch ($recruit_search_status){
                case 1:
                    $model = $model->whereIn('status', [2,3,7,4,5]);
                    break;
                case 2:
                    $model = $model->whereIn('status', [1,4,5]);
                    break;
                case 3:
                    $model = $model->whereIn('status', [4,5]);
                    break;
                case 4:
                    $model = $model->whereIn('status', [6]);
                    break;
                case 5:
                    $model = $model->where('is_public', 1)->whereNotIn('status', [6,7]);
                    break;
                case 6:
                    $model = $model->where('is_public', 0)->whereNotIn('status', [6,7]);
                    break;
            }
        }

        $in_recruit = $this->request->get('in_recruit', null);
        $resume_id = $this->request->get('resume_id', null);
        $user = $this->getUser();
        if ($user) {
            $company = $this->getCurrentCompany();
            if ($company) {
//                $model = $model->where('company_id', $company->id)->whereIn('status', [1,4]);
                //委托了的招聘
                $has_entrust_ids = Entrust::where('status','!=',-3)->pluck('company_job_recruit_id')->toArray();
                $model = $model->where('company_id', $company->id)->where(function ($query)use($has_entrust_ids){
                    $query->whereNotIn('id', $has_entrust_ids)->orWhereIn('status', [1]);
                });
                if($in_recruit){
                    if($in_recruit==1){
                        $model = $model->whereIn('status', [1]);
                    }else{
                        $model = $model->whereIn('status', [4,5]);
                    }
                }else{
                    $model = $model->whereIn('status', [1,4,5,6]);
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

        $leadIds = [];
        foreach ($recruits as $recruit) {
            foreach ($recruit->entrusts as $entrust) {
                $leadIds[] = $entrust->leading_id;
            }
        }
        $entrustRes = app()->build(EntrustsRepository::class);

        $job_ids = [];
        $recruits = $recruits->toArray();
        foreach ($recruits as $recruit) {
            $job_ids[] = $recruit['job']['id'];
        }
        $leads = UserBasicInfo::whereIn('user_id', $leadIds)->get()->keyBy('user_id')->toArray();
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($recruits as &$recruit) {
            foreach ($recruit['entrusts'] as &$entrust) {
                if(isset($leads[$entrust['leading_id']])){
                    $entrust['leading'] = $leads[$entrust['leading_id']];
                }else{
                    $entrust['leading'] = null;
                }
            }
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
//        $model = $model->orderBy('status','asc')->orderBy('updated_at','desc');
        $model = $model->orderByRaw("FIELD(status, 1, 2, 3 ,6 ,7 ,4 ,5) asc")->orderBy('updated_at', 'desc');

        return $model;
    }

    public function afterUpdate($id, $data)
    {
        if(isset($data['leading_id'])){
            Entrust::where('company_job_recruit_id', $id)->update(['leading_id'=>$data['leading_id']]);
        }
        return $this->apiReturnJson(0);
    }

    public function checkUpdate($id,$request)
    {
        $obj = Recruit::find($id);
        checkAuthByCompany($obj);
        $need_num = $request->get('need_num');
        if ($need_num<($obj->done_num + $obj->wait_entry_num)){
            return '需求人数必须大于或等于已存在的完成人数和待入职人数';
        }
        if($obj->done_num>=$need_num){
            //如果是委托 就不改成结束状态
//            if($obj->status==1 || $obj->status==6)
            $obj->status = 5;
            foreach ($obj->entrusts as $_entrust) {
                $_entrust->status = 2;
                $_entrust->end_at = date('Y-m-d H:i:s');
                $_entrust->save();
            }

            $obj->end_at = date('Y-m-d H:i:s');
            $obj->save();
        }
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

            checkAuthByCompany($entrust,false);
            $entrust->status = -1;
            $entrust->end_at = date('Y-m-d H:i:s');
            $entrust->save();
            app()->build(RecruitRepository::class)->generateEndLog($entrust->recruit, $entrust);
        }else{
            $obj = Recruit::find($id);
            if(!$obj)
                return $this->apiReturnJson(9999);

            checkAuthByCompany($obj,true);

            foreach ($obj->entrusts as $entrust) {
                $entrust->status = -1;
                $entrust->end_at = date('Y-m-d H:i:s');
                $entrust->save();
                app()->build(RecruitRepository::class)->generateEndLog($obj, $entrust);
            }
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
            checkAuthByCompany($entrust,false);
            $entrust->status = 6;
            $entrust->pause_at = date('Y-m-d H:i:s');
            $entrust->save();
//            app()->build(RecruitRepository::class)->generateEndLog($entrust->recruit, $entrust);
        }else{
            $obj = Recruit::find($id);
            if(!$obj)
                return $this->apiReturnJson(9999);
            checkAuthByCompany($obj);

            if($obj->status==1){
                $obj->status = 6;
            }elseif($obj->status==3){
                $obj->status = 7;
                foreach ($obj->entrusts as $entrust) {
                    if($entrust->status!=1)
                        continue;
                    $entrust->status = 6;
                    $entrust->pause_at = date('Y-m-d H:i:s');
                    $entrust->save();
                }
            }

            $obj->pause_at = date('Y-m-d H:i:s');
            $obj->save();
//            app()->build(RecruitRepository::class)->generateEndLog($obj);
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
            checkAuthByCompany(Entrust::find($id),false);
            Entrust::where('id', $id)->where('id', $entrust_id)->update(['status'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        }else{
            checkAuthByCompany(Recruit::find($id));
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
            checkAuthByCompany(Entrust::find($id),false);
            Entrust::where('id', $id)->where('id', $entrust_id)->update(['status'=>1]);
        }else{
            if(!is_array($id))
                $ids = [$id];
            else
                $ids = $id;
            foreach ($ids as $id) {
                $recruit = Recruit::find($id);
                checkAuthByCompany($recruit);
                if($recruit->status==6){
                    $this->getModel()->where('id', $id)->update(['status'=>1]);
                }elseif($recruit->status==7){
                    $this->getModel()->where('id', $id)->update(['status'=>3]);
                    foreach ($recruit->entrusts as $entrust) {
                        if($entrust->status != 6)
                            continue;
                        $entrust->status = 1;
                        $entrust->save();
                    }
                }
            }

        }
        return $this->apiReturnJson(0);
    }

    public function checkExist()
    {
        $job_id = $this->request->get('job_id');
        $has = Recruit::where('job_id',$job_id)->whereNotIn('status',[4,5])->first();
        $has_pause = Recruit::where('job_id',$job_id)->whereIn('status',[6,7])->pluck('id')->toArray();
        if(!$job_id)
            return $this->apiReturnJson(0,null,'缺少job_id');
        if($has){
            return $this->apiReturnJson(0,['has'=>1, 'has_pause'=>$has_pause]);
        }else{
            return $this->apiReturnJson(0,['has'=>0, 'has_pause'=>[]]);
        }
    }

    public function outsourceSort(&$model)
    {
        $model = $model->orderByRaw("FIELD(status, 2, 3) desc")->orderBy('updated_at','desc');
        return $model;
    }

    public function outsourceList(Request $request)
    {

        $model = $this->getModel();

        $company = $this->getCurrentCompany();
        if ($company) {
            //委托了的招聘
            $has_entrust_ids = Entrust::pluck('company_job_recruit_id')->toArray();
            $model = $model->where('company_id', $company->id)->whereIn('status', [2,3,4,5,7])->whereIn('id', $has_entrust_ids);
        }else{
            $model = $model->where('id', 0);
        }
        //筛选

        $job_id = $request->get('job_id');
        $department_id = $request->get('department_id');
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');
        $leading_id = $request->get('leading_id');
        $third_party_id = $request->get('third_party_id');
        $recruit_search_status = $request->get('recruit_search_status');
        if($job_id){
            $model = $model->where('job_id', $job_id);
        }
        if($department_id){
            $department = CompanyDepartment::find($department_id);
            if($department->level==1){
                $departmentIds = [$department->id];
            }else{
                $departmentIds = $department->children->pluck('id')->toArray();
            }
            $jobIds = Job::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
            $model = $model->whereIn('job_id', $jobIds);
        }
        if($leading_id){
            $model = $model->where('leading_id', $leading_id);
        }
        if($start_at && !$end_at){
            $model = $model->where('created_at', '>=' ,$start_at);
        }elseif (!$start_at && $end_at){
            $model = $model->where('created_at', '<=' ,$end_at);
        }elseif ($start_at && $end_at){
            $model = $model->where('created_at', '>=' ,$start_at)->where('created_at', '<=' ,$end_at);
        }
        if($third_party_id){
            $_ids = Entrust::where('third_party_id', $third_party_id)->pluck('company_job_recruit_id')->toArray();
            $model = $model->whereIn('id', $_ids);
        }

        if($recruit_search_status){
            switch ($recruit_search_status){
                case 1:
                    $model = $model->whereIn('status', [2,3,7,4,5]);
                    break;
                case 2:
                    $model = $model->whereIn('status', [1,6,4,5]);
                    break;
                case 3:
                    $model = $model->whereIn('status', [4,5]);
                    break;
                case 4:
                    $model = $model->whereIn('status', [7]);
                    break;
                case 5:
                    $model = $model->where('is_public', 1);
                    break;
                case 6:
                    $model = $model->where('is_public', 0);
                    break;
            }
        }
        $model = $this->modelPipeline([
            'modelGetSearch',
            'outsourceSort',
        ],$model);
        $model_data = clone $model;
        $count = $model->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
            'collectionGetLoads',
            'modelByAfterGet',
        ],$model_data);

        $companies = Company::all()->keyBy('id')->toArray();

        $entrustRes = app()->build(EntrustsRepository::class);
        foreach ($list as &$v) {
            foreach ($v['entrusts'] as &$entrust) {

                if(isset($companies[$entrust['third_party_id']])){
                    $entrust['third_party'] = $companies[$entrust['third_party_id']];
                    $entrust['third_party_company_alias'] = $entrust['third_party']['company_alias'];
                }else{
                    $entrust['third_party'] = null;
                }
                $entrust['true_status'] = $entrust['status'];//状态 -3 外包方未确定直接取消  -2 拒绝  -1 取消 0申请中 1正常 2完成
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
