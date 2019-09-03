<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyDepartment;
use App\Models\Course;
use App\Models\Recruit;
use App\Repositories\JobsRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Models\Job;
use Illuminate\Validation\Rule;

class JobsController extends ApiBaseCommonController
{
    use SoftDeletes;

    public $model_name = Job::class;

    public $search_field_array = [
        ['code','like',0],
        ['name','like',0],
//        ['department_id','='],
        ['is_formal','='],
        ['source_company_id','='],
    ];

    public function getTest()
    {
        $data = Course::where('category', 6)->get();
        return $this->apiReturnJson(0, $data);
    }

    public function checkUpdate($id,$data)
    {
        if(Job::where('code',$data->get('code'))->where('company_id',$this->getCurrentCompany()->id)->where('id','!=', $id)->first())
            return '职位代码必须唯一';
        else
            return null;
    }
    public function checkStore($data)
    {
        if(Job::where('code',$data->get('code'))->where('company_id',$this->getCurrentCompany()->id)->first())
            return '职位代码必须唯一';
        else
            return null;
    }

    public function allListIdName()
    {
        $model = $this->getModel();
        $this->authLimit($model);
        $data = $model->pluck('name','id');
        $arr = [];
        foreach ($data as $key=>$item) {
            $_arr = [];
            $_arr['id'] = $key;
            $_arr['name'] = $item;
            $arr[] = $_arr;
        }
        return $this->apiReturnJson(0, $arr);
    }

    public function authLimit(&$model)
    {
        $department_id = $this->request->get('department_id');
        if($department_id) {
            $department = CompanyDepartment::find($department_id);
            if($department){
                if($department->level==1) {
                    $departmentIds = CompanyDepartment::where('pid', $department_id)->pluck('id')->toArray();
                    $model = $model->whereIn('department_id',array_merge($departmentIds,[$department_id]));
                }else{
                    $model = $model->where('department_id',$department_id);
                }
            }
        }
        $model = $model->where('status','!=',-1);
        $user = $this->getUser();
        if ($user) {
            $company = $this->getCurrentCompany();
            if ($company) {
                $model = $model->where('company_id', $company->id);
            }else{
                $model = $model->where('id', 0);
            }
        }
        return null;
    }

    public function _after_get(&$data)
    {
        return app()->build(JobsRepository::class)->getListData($data);
    }

    public function _after_find(&$data)
    {
        $data = app()->build(JobsRepository::class)->getData($data);
    }

    public function afterStore($job, $data)
    {
        $id = $job->id;
        $job->creator_id = $this->getUser()->id;

        if(!$job->company_id){
            $job->company_id = $this->getCurrentCompany()->id;
        }
        if($job->source_recruit_id){
            $_recruit = Recruit::find($job->source_recruit_id);
            if($_recruit) {
                $job->source_job_id = $_recruit->job->id;
                $job->source_company_id = $_recruit->company->id;
            }
        }
        if($job->source_job_id){
            $_job = Job::find($job->source_job_id);
            if($_job) {
                $job->source_company_id = $_job->company->id;
            }
        }
//
//        if(isset($data['area']) && is_array($data['area'])){
//            if(isset($data['area'][0]))
//                $job->province_id = $data['area'][0];
//            if(isset($data['area'][1]))
//                $job->city_id = $data['area'][1];
//            if(isset($data['area'][2]))
//                $job->district_id = $data['area'][2];
//        }
        $job->save();

        $skills = isset($data['skills'])?$data['skills']:null;
        $tests = isset($data['tests'])?$data['tests']:null;

        if(is_array($skills)){
            $skill_ids = [];
            foreach ($skills as $skill) {
                $skill['job_id'] = $id;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->connection('musa')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
            }
            app('db')->connection('musa')->table('job_skill')->where('job_id', $id)->whereNotIn('id', $skill_ids)->delete();
        }
        if(is_array($tests)){
            $test_ids = [];
            foreach ($tests as $test) {
                $test['job_id'] = $id;
                if(isset($test['id']) && $test['id']){
                    $test_ids[] = $test['id'];
                    app('db')->connection('moodle')->table('job_test')->where('id', $test['id'])->update($test);
                }else{
                    $test['job_id'] = $id;
                    $_id = app('db')->connection('moodle')->table('job_test')->insertGetId($test);
                    $test_ids[] = $_id;
                }
            }
            app('db')->connection('moodle')->table('job_test')->where('job_id', $id)->whereNotIn('id', $test_ids)->delete();
        }
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data)
    {
        $skills = isset($data['skills'])?$data['skills']:null;
        $tests = isset($data['tests'])?$data['tests']:null;

        $job = Job::find($id);
//        if(isset($data['area']) && is_array($data['area'])){
//            if(isset($data['area'][0]))
//                $job->province_id = $data['area'][0];
//            if(isset($data['area'][1]))
//                $job->city_id = $data['area'][1];
//            if(isset($data['area'][2]))
//                $job->district_id = $data['area'][2];
//        }
        $job->modifier_id = $this->getUser()->id;
        $job->save();

        if(is_array($skills)){
            $skill_ids = [];
            foreach ($skills as $skill) {
                $skill['job_id'] = $id;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->connection('musa')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
            }
            app('db')->connection('musa')->table('job_skill')->where('job_id', $id)->whereNotIn('id', $skill_ids)->delete();
        }
        if(is_array($tests)){
            $test_ids = [];
            foreach ($tests as $test) {
                $test['job_id'] = $id;
                if(isset($test['id']) && $test['id']){
                    $test_ids[] = $test['id'];
                    app('db')->connection('moodle')->table('job_test')->where('id', $test['id'])->update($test);
                }else{
                    $_id = app('db')->connection('moodle')->table('job_test')->insertGetId($test);
                    $test_ids[] = $_id;
                }
            }
            app('db')->connection('moodle')->table('job_test')->where('job_id', $id)->whereNotIn('id', $test_ids)->delete();
        }
        return $this->apiReturnJson(0);
    }

    public function checkCode()
    {
        $id = $this->request->get('id');
        $code = $this->request->get('code');
        if(!$code)
            return $this->apiReturnJson(9999, ['check'=>0]);
        if($id){
            $has = Job::where('code', $code)->where('company_id',$this->getCurrentCompany()->id)->where('id', '!=', $id)->first();
        }else{
            $has = Job::where('code', $code)->where('company_id',$this->getCurrentCompany()->id)->first();
        }
        return $this->apiReturnJson($has?9999:1, ['check'=>$has?0:1]);
    }

    public function destroy($id)
    {
        $model = $this->getModel()->find($id);
        $has = $this->checkDestroy($model);
        if(!$has){
            $model->status = -1;
            $model->save();
            return responseZK(0);
        }else{
            return responseZK(9999, null,$has);
        }
    }

    public function checkDelete($id)
    {
        $model = $this->getModel()->find($id);
        $has = $this->checkDestroy($model);
        if(!$has){
            return responseZK(0);
        }else{
            return responseZK(9999, null,$has);
        }
    }

    public function checkDestroy($model)
    {
        $has = $model->recruits()->whereIn('status', [1])->count();
        if($has){
            return '该职位处于招聘状态无法删除，请结束招聘状态！';
        }
        $has = $model->recruits()->whereIn('status', [2,3])->count();
        if($has){
            return '该职位处于招聘状态无法删除，请结束招聘状态！';
        }
        return false;
    }
}
