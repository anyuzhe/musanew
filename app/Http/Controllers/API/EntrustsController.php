<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Repositories\CompanyLogRepository;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;

class EntrustsController extends ApiBaseCommonController
{
    protected $model_name = Entrust::class;
//}elseif($type==2){
//    //外包出去的
//$company_job_recruit_ids = app('db')->table('company_job_recruit_entrust')->whereIn('status', [2,3]);
//->where('company_id', $company->id)->pluck('company_job_recruit_id');
//$model = $model->whereIn('id', $company_job_recruit_ids);
//}elseif($type==3){
//    //作为外包公司
//$company_job_recruit_ids = app('db')->table('company_job_recruit_entrust')
//    ->where('third_party_id', $company->id)->pluck('company_job_recruit_id');
//$model = $model->whereIn('id', $company_job_recruit_ids)->where('status', 3);

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
        $demand_side_id = $request->get('demand_side_id');
        $recruit_search_status = $request->get('recruit_search_status');
        if($job_id){
            $model = $model->where('job_id', $job_id);
        }
        if($department_id){
            $department = CompanyDepartment::find($department_id);
            if($department->level==1){
                $departmentIds = $department->children->pluck('id')->toArray();
            }else{
                $departmentIds = [$department->id];
            }
            $jobIds = Job::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
            $model = $model->whereIn('job_id', $jobIds);
        }
        if($leading_id){
            $_ids1 = Recruit::where('leading_id', $leading_id)->pluck('id')->toArray();
            $_ids2 = Entrust::where('leading_id', $leading_id)->pluck('id')->toArray();
            $model = $model->where(function ($query) use ($_ids2, $_ids1) {
                $query->whereIn('company_job_recruit_id', $_ids1)->orWhereIn('id', $_ids2);
            });
        }
        if($start_at && !$end_at){
            $model = $model->where('created_at', '>=' ,$start_at);
        }elseif (!$start_at && $end_at){
            $model = $model->where('created_at', '<=' ,$end_at);
        }elseif ($start_at && $end_at){
            $model = $model->where('created_at', '>=' ,$start_at)->where('created_at', '<=' ,$end_at);
        }
        if($third_party_id){
            $model = $model->where('third_party_id', $third_party_id);
        }
        if($demand_side_id){
            $model = $model->where('company_id', $demand_side_id);
        }

        if($recruit_search_status){
            switch ($recruit_search_status){
                case 1:
                    $model = $model->whereIn('status', [0,1,2]);
                    break;
                case 2:
                    $model = $model->whereIn('status', [-3,-2,-1]);
                    break;
                case 3:
                    $model = $model->whereIn('status', [-3,-2,-1,2]);
                    break;
                case 4:
                    $model = $model->whereIn('status', [6,7]);
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
        $type = $this->request->type;

        if ($user && $type) {
            $company = $this->getCurrentCompany();
            $model = app()->build(EntrustsRepository::class)->getModelByType($type, $company, $in_recruit, $resume_id, $model);
            if($type==2){
                $depIds = getPermissionScope($company->id, $user->id, 20);
                if($depIds && is_array($depIds)){
                    $jobIds = Job::whereIn('department_id', $depIds)->pluck('id')->toArray();
                    $model = $model->whereIn('job_id', $jobIds);
                }
            }
        }
        return null;
    }

    //列表排序
    protected function modelGetSort(&$model)
    {
        $model = $model->orderByRaw("FIELD(status, 6, 0,1) desc")->orderBy('updated_at', 'desc');
//        $model = $model->orderByRaw("FIELD(status, 1, 0, 2, -1, -2, -3)")->orderBy('updated_at', 'desc');
//        $model = $model->orderByRaw("FIELD(status, ?)", [1,0,2,-1,-2])->orderBy('created_at', 'desc');
        return $model;
    }

    public function _after_get(&$entrusts)
    {
        $entrusts->load('job');
        $entrusts->load('recruit');
        $entrusts->load('leading');
        $entrusts->load('company');
        $entrusts->load('thirdParty');

        $entrustRes = app()->build(EntrustsRepository::class);
        $allAcount = $entrustRes->getEntrustsAmount($entrusts);
        $job_ids = [];
        foreach ($entrusts as &$entrust) {
            $job_ids[] = $entrust->job->id;
            $entrust->recruit->leading;
        }
        unset($entrust);
        $entrusts = $entrusts->toArray();
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();

        foreach ($entrusts as &$entrust) {
            $entrust['job'] = $jobs[$entrust['job']['id']];
            $entrust['need_num'] = $entrust['recruit']['need_num'];
            $entrust['recruit_id'] = $entrust['recruit']['id'];
            $entrust['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($entrust['recruit'],$entrust);
            $entrust['status'] = $entrustRes->getStatusByEntrustAndRecruit($entrust['status'],$entrust['recruit']['status']);

            $acounts = $allAcount[$entrust['company_job_recruit_id']];
            $entrust['resume_num'] = $entrust['recruit']['resume_num'] - $acounts['total_resume_num'] + $entrust['resume_num'];
            $entrust['done_num'] = $entrust['recruit']['done_num'] - $acounts['total_done_num'] + $entrust['done_num'];
            $entrust['new_resume_num'] = $entrust['recruit']['new_resume_num'] - $acounts['total_new_resume_num'] + $entrust['new_resume_num'];
            $entrust['recruit']['residue_num'] = $entrust['recruit']['need_num'] - $entrust['recruit']['done_num'] - $entrust['recruit']['wait_entry_num'];
            $entrust['recruit']['residue_num'] = $entrust['recruit']['residue_num']>0?$entrust['recruit']['residue_num']:0;
        }

        $type = $this->request->type;
        if($type==2) {
            CompanyLogRepository::addLog('recruit_user_manage','show_outsourcing_employee',"查看/筛查外包员工职位 第".request('pagination', 1)."页");
        }elseif($type==3){
            CompanyLogRepository::addLog('recruit_user_manage','show_demand',"查看/筛查需求管理 第".request('pagination', 1)."页");
        }elseif($type==4){
            CompanyLogRepository::addLog('entrust_manage','show_entrust',"查看委托申请 第".request('pagination', 1)."页");
        }
        return $entrusts;
    }

    public function _after_find(&$data)
    {
        $entrustRes = app()->build(EntrustsRepository::class);
        $data->need_num = $data->recruit->need_num;
        $data['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($data['recruit'],$data);
        $data->status = app()->build(EntrustsRepository::class)->getStatusByEntrustAndRecruit($data->status, $data->recruit->status);
        $data->company;
        $data->thirdParty;
        $data->leading;
        $data->job = app()->build(JobsRepository::class)->getData($data->job);
        $data->recruit->residue_num = $data->recruit->need_num - $data->recruit->done_num - $data->recruit->wait_entry_num;
        $data->recruit->residue_num = $data->recruit->residue_num>0?$data->recruit->residue_num:0;
    }

    public function applyEntrust()
    {
        $company_job_recruit_id = $this->request->get('company_job_recruit_id');
        $is_new_create = $this->request->get('is_new_create',0);

        $recruit = Recruit::find($company_job_recruit_id);

        if($is_new_create){
            $new = Recruit::create($recruit->toArray());

            $new->company_id = $recruit->company_id;
            $new->creator_id = $this->getUser()->id;
            $new->true_created_at = date('Y-m-d H:i:s');
            $new->wait_entry_num = 0;
            $new->done_num = 0;
            $new->resume_num = 0;
            $new->new_resume_num = 0;
            $new->save();
            $recruit = $new;
        }

        $recruit->status = 2;
        $recruit->save();
        $third_party_ids = $this->request->get('third_party_ids');
        $thirdPartyIds = $this->getCurrentCompany()->thirdParty->pluck('id')->toArray();
        $text = $recruit->job->name." 发起委托给:";
        if(is_array($third_party_ids)){
            foreach ($third_party_ids as $third_party_id) {
                if(in_array($third_party_id, $thirdPartyIds)){
                    Entrust::create([
                        'is_public'=>$recruit->is_public,
                        'job_id'=>$recruit->job_id,
                        'leading_id'=>$recruit->leading_id,
                        'company_id'=>$recruit->company_id,
                        'third_party_id'=>$third_party_id,
                        'company_job_recruit_id'=>$recruit->id,
                        'done_num'=>0,
                        'resume_num'=>0,
                        'new_resume_num'=>0,
                        'status'=>0,
                        'creator_id'=>$this->getUser()->id,
                    ]);
                    $text.= ' '. Company::find($third_party_id)->company_alias;
                }
            }
        }
        CompanyLogRepository::addLog('recruit_user_manage','add_entrust', $text);

        return $this->apiReturnJson(0);
    }

    public function subcontract(Request $request)
    {
        $data = $request->all();
        $job = new Job();
        $job->fill($data);
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
        $id = $job->id;

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

        $entrust_id = $this->request->get('entrust_id');

        $entrust = Entrust::find($entrust_id);
        $recruit = $entrust->recruit;

        $new = Recruit::create($recruit->toArray());

        if(isset($data['leading_id']) && $data['leading_id']){
            $leading_id = $data['leading_id'];
        }else{
            $leading_id = $entrust->leading_id;
        }
        $new->company_id = $this->getCurrentCompany()->id;
        $new->job_id = $job->id;
        $new->is_public = $data['is_public'];
        $new->leading_id = $leading_id;
        $new->creator_id = $this->getUser()->id;
        $new->true_created_at = date('Y-m-d H:i:s');
        $new->wait_entry_num = 0;
        $new->done_num = 0;
        $new->resume_num = 0;
        $new->new_resume_num = 0;
        $new->save();
        $recruit = $new;

        $recruit->status = 2;
        $recruit->save();
        $third_party_ids = $this->request->get('third_party_ids');
        $thirdPartyIds = $this->getCurrentCompany()->thirdParty->pluck('id')->toArray();
        if(is_array($third_party_ids)){
            foreach ($third_party_ids as $third_party_id) {
                if(in_array($third_party_id, $thirdPartyIds)){
                    Entrust::create([
                        'is_public'=>$recruit->is_public,
                        'job_id'=>$recruit->job_id,
                        'leading_id'=>$recruit->leading_id,
                        'company_id'=>$recruit->company_id,
                        'third_party_id'=>$third_party_id,
                        'company_job_recruit_id'=>$recruit->id,
                        'done_num'=>0,
                        'resume_num'=>0,
                        'new_resume_num'=>0,
                        'status'=>0,
                        'creator_id'=>$this->getUser()->id,
                    ]);
                }
            }
        }
        return $this->apiReturnJson(0);
    }

    public function cancelEntrust()
    {
        $id = $this->request->get('id');
        if(!is_array($id)){
            $id = [$id];
        }
        foreach ($id as $v) {
            $entrust = Entrust::find($v);
            $recruit = $entrust->recruit;
//            if($recruit->status==2) {
                if ($this->getCurrentCompany()->id == $entrust->company_id && $entrust->status == 0) {
                    $entrust->status = -3;
                } else {
                    $entrust->status = -1;
                }
                $entrust->save();

                //假如委托没有进行中的了 才会变成1
                if (!Entrust::where('company_job_recruit_id', $recruit->id)->whereIn('status', [0, 1])->first()) {
                    $recruit->status = 1;
                    $recruit->save();
                }
//            }
        }
        return $this->apiReturnJson(0);
    }

    public function acceptEntrust()
    {
        $ids = $this->request->get('ids');
        $leading_id = $this->request->get('leading_id');

        $is_public = $this->request->get('is_public',null);
        if(!$ids || count($ids)==0){
            $id = $this->request->get('id');
            if($id){
                $ids = [$id];
            }
        }
        foreach ($ids as $id) {
            $entrust = Entrust::find($id);
            $recruit = $entrust->recruit;
            if($entrust->status==0){
                $recruit->status = 3;
                if($leading_id){
                    $entrust->leading_id = $leading_id;
                }
                $recruit->save();

                if($is_public!==null){
                    $entrust->is_public  = $is_public;
                }
                $entrust->status = 1;
                $entrust->save();
            }
            CompanyLogRepository::addLog('entrust_manage','agree_entrust', '同意 '.$recruit->company->company_alias.' '.$entrust->job->name);

        }
        return $this->apiReturnJson(0);
    }

    public function rejectEntrust()
    {
        $ids = $this->request->get('ids');
        if(!$ids || count($ids)==0){
            $id = $this->request->get('id');
            if($id){
                $ids = [$id];
            }
        }
        foreach ($ids as $id) {
            $entrust = Entrust::find($id);
            $recruit = $entrust->recruit;
            if($entrust->status==0){
                //假如委托没有进行中的了 直接变成结束
                if(!Entrust::where('company_job_recruit_id', $recruit->id)->whereIn('status',[0,1])->first()){
                    $recruit->status = 4;
                    $recruit->save();
                }

                $entrust->status = -2;
                $entrust->save();
            }
            CompanyLogRepository::addLog('entrust_manage','agree_entrust', '拒绝 '.$recruit->company->company_alias.' '.$entrust->job->name);
        }
        return $this->apiReturnJson(0);
    }

    public function returnEntrust()
    {
        $company_job_recruit_id = $this->request->get('company_job_recruit_id');
        $third_party_id = $this->request->get('third_party_id');
        $recruit = Recruit::find($company_job_recruit_id);
        if($recruit->status==3){
            $recruit->status = 1;
            $recruit->save();

            Entrust::where('company_job_recruit_id',$company_job_recruit_id)
                ->where('third_party_id', $third_party_id)->update([
                'status'=>-1
            ]);
            return $this->apiReturnJson(0);
        }else{
            return $this->apiReturnJson(9999);
        }
    }

    public function thirdPartyJobListIdName()
    {
        $company = $this->getCurrentCompany();
        $jobIds = Entrust::where('third_party_id', $company->id)->pluck('job_id')->toArray();
        $data = Job::whereIn('id',$jobIds)->get();
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
}
