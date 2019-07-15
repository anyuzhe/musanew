<?php

namespace App\Http\Controllers\API;

use App\Models\Course;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
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
        $user = $this->getUser();
        if ($user) {
            $company = $user->company->first();
            if ($company) {
//                $model = $model->where('company_id', $company->id)->whereIn('status', [1,4]);
                //委托了的招聘
                $has_entrust_ids = Entrust::pluck('company_job_recruit_id')->toArray();
                $model = $model->where('company_id', $company->id)->whereIn('status', [1,4])->whereNotIn('id', $has_entrust_ids);
            }else{
                $model = $model->where('id', 0);
            }
        }
        return null;
    }

    public function _after_get(&$recruits)
    {
        $recruits->load('job');

        $entrustRes = app()->build(EntrustsRepository::class);

        $job_ids = [];
        $recruits = $recruits->toArray();
        foreach ($recruits as $recruit) {
            $job_ids[] = $recruit['job']['id'];
        }
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($recruits as &$recruit) {
            $recruit['job'] = $jobs[$recruit['job']['id']];

            $recruit['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($recruit);
        }
        return $recruits;
    }

    public function _after_find(&$data)
    {
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
        $obj->save();
        return $this->apiReturnJson(0);
    }

    //排序
    protected function modelGetSort(&$model)
    {
        $model = $model->orderBy('status','asc')->orderBy('id','desc');
        return $model;
    }

//    public function afterUpdate($id, $data)
//    {
//    }

    public function finish()
    {
        $id = $this->request->get('id');
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            Entrust::where('id', $entrust_id)->update(['status'=>2]);
        }else{
            $this->getModel()->where('id', $id)->update(['status'=>4]);
        }
        
        return $this->apiReturnJson(0);
    }

    public function restart()
    {
        $id = $this->request->get('id');
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            Entrust::where('id', $id)->where('id', $entrust_id)->update(['status'=>1,'created_at'=>date('Y-m-d H:i:s')]);;
        }else{
            $this->getModel()->where('id', $id)->update(['status'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        }
        return $this->apiReturnJson(0);
    }
}
