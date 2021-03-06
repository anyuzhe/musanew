<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyDepartment;
use App\Models\Course;
use App\Models\DataMapOption;
use App\Models\Recruit;
use App\Models\Resume;
use App\Models\Skill;
use App\Repositories\CompanyLogRepository;
use App\Repositories\JobsRepository;
use App\Repositories\SkillsRepository;
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
        $data = Course::whereIn('category', SkillsRepository::getTestCateId())->get();
        return $this->apiReturnJson(0, $data);
    }

    public function checkUpdate($id,$data)
    {
        $obj = Job::find($id);
        checkAuthByCompany($obj);
        if(Job::where('code',$data->get('code'))->where('company_id',$this->getCurrentCompany()->id)->where('id','!=', $id)->first()){
            return '已获取该职位';
            //            return '职位代码必须唯一';
        }
    }
    public function checkStore($data)
    {
        if(Job::where('code',$data->get('code'))->where('company_id',$this->getCurrentCompany()->id)->first())
            return '已获取该职位';
//            return '职位代码必须唯一';
        else
            return null;
    }

    public function allListIdName()
    {
        $model = $this->getModel();
        $this->authLimit($model);
        $data = $model->orderBy('code','asc')->get();
        $arr = [];
        foreach ($data as $key=>$item) {
            $_arr = [];
            $_arr['id'] = $item->id;
            $_arr['name'] = $item->name;
            $_arr['code'] = $item->code;
            $arr[] = $_arr;
        }
        return $this->apiReturnJson(0, $arr);
    }

    public function recruitJobListIdName()
    {
        $company = $this->getCurrentCompany();
        $jobIds = Recruit::where('company_id', $company->id)->pluck('job_id')->toArray();
        $data = Job::whereIn('id',$jobIds)->orderBy('code','asc')->get();
        $arr = [];
        foreach ($data as $key=>$item) {
            $_arr = [];
            $_arr['id'] = $item->id;
            $_arr['name'] = $item->name;
            $_arr['code'] = $item->code;
            $arr[] = $_arr;
        }
        return $this->apiReturnJson(0, $arr);
    }

    public function authLimit(&$model)
    {
        $department_id = $this->request->get('department_id');
        $model = $model->where('status','!=',-1);
        $user = $this->getUser();
        if ($user) {
            $company = $this->getCurrentCompany();
            if ($company) {
                $depIds = getPermissionScope($company->id, $user->id, 6);
                if($depIds && is_array($depIds)){
                    $model = $model->whereIn('department_id', $depIds);
                }
                $model = $model->where('company_id', $company->id);
            }else{
                $model = $model->where('id', 0);
            }

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
        }
        return null;
    }

    public function _after_get(&$data)
    {
        CompanyLogRepository::addLog('job_manage','show_official_job',"查看职位列表 第".request('pagination', 1)."页");
        return app()->build(JobsRepository::class)->getListData($data);
    }

    public function _after_find(&$data)
    {
        $data = app()->build(JobsRepository::class)->getData($data);
        CompanyLogRepository::addLog('job_manage','show_official_job',"查看职位 $data->name 详情");
    }

    public function afterStore($job, $data)
    {
        $id = $job->id;
        $job->creator_id = $this->getUser()->id;
        CompanyLogRepository::addLog('job_manage','add_official_job',"添加职位 $job->name");

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
        $necessarySkills = isset($data['necessary_skills'])?$data['necessary_skills']:null;
        $optionalSkills = isset($data['optional_skills'])?$data['optional_skills']:null;
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
        if(is_array($necessarySkills)){
            $skill_ids = [];
            foreach ($necessarySkills as $skill) {
                $skill['job_id'] = $id;
                $skill['type'] = 1;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->connection('musa')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
            }
            app('db')->connection('musa')->table('job_skill')->where('job_id', $id)->where('type', 1)->whereNotIn('id', $skill_ids)->delete();
        }
        if(is_array($optionalSkills)){
            $skill_ids = [];
            foreach ($optionalSkills as $skill) {
                $skill['job_id'] = $id;
                $skill['type'] = 2;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->connection('musa')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
            }
            app('db')->connection('musa')->table('job_skill')->where('job_id', $id)->where('type', 2)->whereNotIn('id', $skill_ids)->delete();
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

    public function afterUpdate($id, $data, $job)
    {
        $editText = CompanyLogRepository::getDiffText($job);
        $skills = isset($data['skills'])?$data['skills']:null;
        $necessarySkills = isset($data['necessary_skills'])?$data['necessary_skills']:null;
        $optionalSkills = isset($data['optional_skills'])?$data['optional_skills']:null;
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
        $skillLevelArr = DataMapOption::where('data_map_id',10)->get()->keyBy('value');
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

        if(is_array($necessarySkills)){
            $editText.= ', 必要技能改为:';
            $skill_ids = [];
            $hasF = false;
            foreach ($necessarySkills as $skill) {
                $hasF = true;
                $skill['job_id'] = $id;
                $skill['type'] = 1;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->connection('musa')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
                $_s = Skill::find($skill['skill_id']);
                $editText.= $_s->name." {$skillLevelArr->get($skill['skill_level'])->text}, ";
            }
            if($hasF)
                $editText = substr($editText,0,strlen($editText)-2);
            else
                $editText.= '空';
            app('db')->connection('musa')->table('job_skill')->where('job_id', $id)->where('type', 1)->whereNotIn('id', $skill_ids)->delete();
        }

        if(is_array($optionalSkills)){
            $skill_ids = [];
            $editText.= ', 选择技能改为:';
            $hasF = false;
            foreach ($optionalSkills as $skill) {
                $hasF = true;
                $skill['job_id'] = $id;
                $skill['type'] = 2;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->connection('musa')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
                $_s = Skill::find($skill['skill_id']);
                $editText.= $_s->name." {$skillLevelArr->get($skill['skill_level'])->text}, ";
            }
            if($hasF)
                $editText = substr($editText,0,strlen($editText)-2);
            else
                $editText.= '空';
            app('db')->connection('musa')->table('job_skill')->where('job_id', $id)->where('type', 2)->whereNotIn('id', $skill_ids)->delete();
        }

        if(is_array($tests)){
            $test_ids = [];
            $hasF = false;
            foreach ($tests as $test) {
                $hasF = true;
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
            if($hasF)
                $editText.= ', 测试修改为: '.implode(',', Course::whereIn('id',app('db')->connection('moodle')->table('job_test')->where('job_id', $id)->pluck('course_id'))->pluck('shortname')->toArray());
        }
        CompanyLogRepository::addLog('job_manage','edit_official_job', $editText);

        return $this->apiReturnJson(0);
    }

    public function checkCode()
    {
        $id = $this->request->get('id');
        $code = $this->request->get('code');
        $company_id = $this->request->get('company_id', null);
        if(!$company_id)
            $company_id = $this->getCurrentCompany()->id;
        if(!$code)
            return $this->apiReturnJson(9999, ['check'=>0]);
        if($id){
            $has = Job::where('code', $code)->where('company_id',$company_id)->where('id', '!=', $id)->first();
        }else{
            $has = Job::where('code', $code)->where('company_id',$company_id)->first();
        }
        return $this->apiReturnJson(0, ['check'=>$has?0:1]);
    }

    public function destroy($id)
    {
        $model = $this->getModel()->find($id);
        if($model->company_id!=$this->getCurrentCompany()->id){
            return responseZK(9999);
        }
        $has = $this->checkDestroy($model);
        if(!$has){
            CompanyLogRepository::addLog('job_manage','delete_official_job',"删除职位 $model->name ");
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
        checkAuthByCompany($model);
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
