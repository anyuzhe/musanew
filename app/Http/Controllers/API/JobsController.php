<?php

namespace App\Http\Controllers\API;

use App\Models\Course;
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

    public function getTest()
    {
        $data = Course::where('category', 6)->get();
        return $this->apiReturnJson(0, $data);
    }

    public function storeValidate()
    {
        return [
            [
                'code' => 'required|unique:jobs',
            ],
            [
                'code.required'=>'编号编号必须填写',
                'code.unique'=>'编号必须唯一',
            ]
        ];
    }

    public function updateValidate($id)
    {
        return [
            [
                'code' => [
                    'required',
                    Rule::unique('jobs')->ignore($id)->where(function ($query) {
                        return $query->where('status', '!=',-1);
                    }),
                ],
            ],
            [
                'code.required'=>'编号编号必须填写',
                'code.unique'=>'编号必须唯一',
            ]
        ];
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

        if(isset($data['area']) && is_array($data['area'])){
            if(isset($data['area'][0]))
                $job->province_id = $data['area'][0];
            if(isset($data['area'][1]))
                $job->city_id = $data['area'][1];
            if(isset($data['area'][2]))
                $job->district_id = $data['area'][2];
        }
        $job->save();

        $skills = isset($data['skills'])?$data['skills']:[];
        $tests = isset($data['tests'])?$data['tests']:[];

        if($skills && is_array($skills)){
            $skill_ids = [];
            foreach ($skills as $skill) {
                $skill['job_id'] = $id;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
            }
            app('db')->table('job_skill')->where('job_id', $id)->whereNotIn('id', $skill_ids)->delete();
        }
        if($tests && is_array($tests)){
            $test_ids = [];
            foreach ($tests as $test) {
                $test['job_id'] = $id;
                if(isset($test['id']) && $test['id']){
                    $test_ids[] = $test['id'];
                    app('db')->table('job_test')->where('id', $test['id'])->update($test);
                }else{
                    $test['job_id'] = $id;
                    $_id = app('db')->table('job_test')->insertGetId($test);
                    $test_ids[] = $_id;
                }
            }
            app('db')->table('job_test')->where('job_id', $id)->whereNotIn('id', $test_ids)->delete();
        }
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data)
    {
        $skills = isset($data['skills'])?$data['skills']:[];
        $tests = isset($data['tests'])?$data['tests']:[];

        $job = Job::find($id);
        if(isset($data['area']) && is_array($data['area'])){
            if(isset($data['area'][0]))
                $job->province_id = $data['area'][0];
            if(isset($data['area'][1]))
                $job->city_id = $data['area'][1];
            if(isset($data['area'][2]))
                $job->district_id = $data['area'][2];


        }
        $job->modifier_id = $this->getUser()->id;
        $job->save();

        if($skills && is_array($skills)){
            $skill_ids = [];
            foreach ($skills as $skill) {
                $skill['job_id'] = $id;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    app('db')->table('job_skill')->where('id', $skill['id'])->update($skill);
                }else{
                    $_id = app('db')->table('job_skill')->insertGetId($skill);
                    $skill_ids[] = $_id;
                }
            }
            app('db')->table('job_skill')->where('job_id', $id)->whereNotIn('id', $skill_ids)->delete();
        }
        if($tests && is_array($tests)){
            $test_ids = [];
            foreach ($tests as $test) {
                $test['job_id'] = $id;
                if(isset($test['id']) && $test['id']){
                    $test_ids[] = $test['id'];
                    app('db')->table('job_test')->where('id', $test['id'])->update($test);
                }else{
                    $_id = app('db')->table('job_test')->insertGetId($test);
                    $test_ids[] = $_id;
                }
            }
            app('db')->table('job_test')->where('job_id', $id)->whereNotIn('id', $test_ids)->delete();
        }
        return $this->apiReturnJson(0);
    }

    public function checkCode()
    {
        $id = $this->request->get('id');
        $code = $this->request->get('code');
        if(!$code)
            return $this->apiReturnJson(0, ['check'=>0]);
        if($id){
            $has = Job::where('code', $code)->where('id', '!=', $id)->first();
        }else{
            $has = Job::where('code', $code)->first();
        }
        return $this->apiReturnJson(0, ['check'=>$has?0:1]);
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
