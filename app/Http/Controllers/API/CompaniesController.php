<?php

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyDepartment;
use App\Models\CompanyManagerLog;
use App\Models\CompanyResume;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\CompanyUserPermissionScope;
use App\Models\CompanyUserRole;
use App\Models\Entrust;
use App\Models\ExternalToken;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\RecruitResumeLook;
use App\Models\Resume;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\AreaRepository;
use App\Repositories\CompaniesRepository;
use App\Repositories\CompanyLogRepository;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\Repositories\RoleRepository;
use App\Repositories\StatisticsRepository;
use App\Repositories\UserRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use App\ZL\ORG\Excel\ExcelHelper;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use mod_questionnaire\question\date;

class CompaniesController extends ApiBaseCommonController
{
    protected $model_name = Company::class;
    protected $userRepository;
    protected $recruitResumesRepository;

    public function __construct(Request $request, UserRepository $userRepository, RecruitResumesRepository $recruitResumesRepository)
    {
        Parent::__construct($request);
        $this->userRepository = $userRepository;
        $this->recruitResumesRepository = $recruitResumesRepository;
    }

    public function thirdPartyList()
    {
        $this->requireMoodleConfig();

        $company = $this->getCurrentCompany();

        $thirdParty = $company->thirdParty();
        $model_data = clone $thirdParty;
        $count = $thirdParty->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
        ],$model_data);

        ## 招聘完成率 recruitFinishingRate
        ## 招聘成功率 recruitSuccessRate
        ## 累计负责岗位数量 allJobCount
        ## 负责本企业岗位数量 ourJobCount
        ## 当前进行招聘岗位数量 currentRecruitCount

//        招聘完成率
//文本
//增加职位简历资料并转转交还需求方的职位数量/总职位数量
//招聘成功率
//文本
//需求方采纳第三方公司提交的简历/总职位数量
//累计负责职位数量
//文本
//该第三方企业同意接受的所有职位数量
//负责本企业职位数量
//文本
//该第三方企业同意接受的本企业所有职位数量
//当前进行招聘职位数量
//文本
//该第三方企业正在为本企业招聘的职位数量

        foreach ($list as &$v) {
            $_ids1 = Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereNotIn('status', [0,-2])->pluck('id')->toArray();
            $_finish_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $_ids1)->whereNotIn('status',[1,-1])->count();
            $_success_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $_ids1)->whereNotIn('status',[6])->count();
            $_all_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereNotIn('status', [0,-2])->pluck('company_job_recruit_id')->toArray())->sum('need_num');
            $v->recruitFinishingRate = (!$_all_count)?0:round($_finish_count/$_all_count*100, 2);
            $v->recruitSuccessRate = (!$_all_count)?0:round($_success_count/$_all_count*100, 2);
//            $_all_need_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->whereNotIn('status', [0,-2])->pluck('company_job_recruit_id')->toArray())->sum('need_num');
            $_all_need_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->whereNotIn('status', [0,-2])->pluck('company_job_recruit_id')->toArray())->count();
//            $_current_need_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereIn('status', [1])->pluck('company_job_recruit_id')->toArray())->sum('need_num');
            $_current_need_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereIn('status', [1])->pluck('company_job_recruit_id')->toArray())->count();
            $v->allJobCount = $_all_need_count?$_all_need_count:0;
            $v->ourJobCount = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereNotIn('status', [0,-2])->pluck('company_job_recruit_id')->toArray())->count();;
            $v->currentRecruitCount = $_current_need_count?$_current_need_count:0;

            $v->logo_url = getCompanyLogo($v->logo);
        }
        unset($v);

        $pageSize = app('request')->get('pageSize',3);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;

        return $this->apiReturnJson(0, $list,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }

    public function thirdPartyListIdName()
    {
        $company = $this->getCurrentCompany();
        $thirdParty = $company->thirdParty;
        $arr = [];
        foreach ($thirdParty as $key=>$item) {
            $_arr = [];
            $_arr['id'] = $item['id'];
            $_arr['name'] = $item['company_name'];
            $arr[] = $_arr;
        }
        return $this->apiReturnJson(0, $arr);
    }


    public function demandSideListIdName()
    {
        $company = $this->getCurrentCompany();
        $demandSides = $company->demandSides;
        $arr = [];
        foreach ($demandSides as $key=>$item) {
            $_arr = [];
            $_arr['id'] = $item['id'];
            $_arr['name'] = $item['company_name'];
            $arr[] = $_arr;
        }
        return $this->apiReturnJson(0, $arr);
    }

    public function entrustsList()
    {
        $this->requireMoodleConfig();
        $jobRes = app()->build(JobsRepository::class);

        $company = $this->getCurrentCompany();

        $type = 4;

        $model = app()->build(EntrustsRepository::class)->getModelByType($type, $company);
        $company_ids = $model->pluck('company_id')->toArray();
        $entrust_ids = $model->pluck('id')->toArray();
        $companyModel = Company::whereIn('id', $company_ids);


        $model_data = clone $companyModel;
        $count = $companyModel->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
        ],$model_data);
        if($type==2){
            $list->load(['requirements' => function ($query)use($entrust_ids) {
                $query->whereIn('id', $entrust_ids);
            }]);
        }elseif ($type==3){
            $list->load(['requirements' => function ($query)use($entrust_ids) {
                $query->whereIn('id', $entrust_ids);
            }]);
        }elseif ($type==4){
            $list->load(['entrusts' => function ($query)use($entrust_ids) {
                $query->whereIn('id', $entrust_ids);
            }]);
        }
        $newList = [];
        foreach ($list as &$v) {
            if($type==2){
                $entrusts = $v->requirements;
            }elseif ($type==3){
                $entrusts = $v->requirements;
            }elseif ($type==4){
                $entrusts = $v->entrusts;
            }else{
                continue;
            }

            $v->logo_url = getCompanyLogo($v->logo);

            $entrusts->load('job');
            $entrusts->load('recruit');
            $entrusts->load('leading');

            $item = $v->toArray();

            $job_ids = [];
            $entrusts = $entrusts->toArray();
            foreach ($entrusts as $entrust) {
                $job_ids[] = $entrust['job']['id'];
            }
            $jobs = $jobRes->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
            foreach ($entrusts as &$entrust) {
                $entrust['job'] = $jobs[$entrust['job']['id']];
                $entrust['need_num'] = $entrust['recruit']['need_num'];
                $entrust['recruit_id'] = $entrust['recruit']['id'];
            }
            unset($entrust);

            if($type==2){
                $item['entrusts'] = $entrusts;
            }elseif ($type==3){
                $item['entrusts'] = $entrusts;
                unset($item['requirements']);
                unset($item['recruit']);
            }elseif ($type==4){
                $item['entrusts'] = $entrusts;
                unset($item['requirements']);
                unset($item['recruit']);
            }else{
                continue;
            }
            $newList[] = $item;
        }
        unset($v);

        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;
        return $this->apiReturnJson(0, $newList,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }

    public function getCurrentInfo()
    {
        $company = $this->getCurrentCompany();

        CompanyLogRepository::addLog('basics_manage','show_basics', '查看企业信息');

        $company->addresses;
        foreach ($company->addresses as &$v) {
            $v->area = [$v->province_id,$v->city_id,$v->district_id];
            $v->area_text = Area::where('id', $v->province_id)->value('cname').
                Area::where('id', $v->city_id)->value('cname').
                Area::where('id', $v->district_id)->value('cname');
        }
        if($company->province_id){
            $company->province_text = Area::where('id', $company->province_id)->value('cname');
        }else{
            $company->province_text = '';
        }
        if($company->city_id){
            $company->city_text = Area::where('id', $company->city_id)->value('cname');
        }else{
            $company->city_text = '';
        }
        if($company->district_id){
            $company->district_text = Area::where('id', $company->district_id)->value('cname');
        }else{
            $company->district_text = '';
        }
        $company->full_logo = getCompanyLogo($company->logo);
        $company->industry;
        $company->conglomerate;
        $company->departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        $company->manager = app()->build(CompaniesRepository::class)->getManager($company->id);
        getOptionsText($company);
//        $company->is_demand_side = count($company->thirdParty)>0?1:0;
        $role = CompanyRole::find($company->pivot->company_role_id);
        if($role){
            $company->role_name = CompanyRole::find($company->pivot->company_role_id)->name;
        }else{
            $company->role_name = '未设置角色';
        }
//        $company->is_third_party = count($company->demandSides)>0?1:0;
        return $this->apiReturnJson(0,$company);
    }

    public function getDepartments()
    {
        $third_party_id = $this->request->get('third_party_id');
        $company = $this->getCurrentCompany();
        if($company){
            if($third_party_id){
                return $this->apiReturnJson(0,app()->build(CompaniesRepository::class)->getDepartmentTreeByThirdParty($company->id, $third_party_id));
            }else{
                return $this->apiReturnJson(0,app()->build(CompaniesRepository::class)->getDepartmentTree($company->id));
            }
        }else
            return $this->apiReturnJson(9999,null,'没有当前公司');
    }

    public function updateCurrentInfo()
    {
        $company = $this->getCurrentCompany();

        if(!$company)
            return $this->apiReturnJson(9999,null,'没有当前公司');

        if(Company::where('company_alias',$this->request->get('company_alias'))->where('status', 1)->where('id','!=', $company->id)->first()){
            return $this->apiReturnJson(9999,null,'该企业简称已经存在');
        }
        $model = new Company();

        $company->fill($this->request->only($model->fillable));
        $editText = CompanyLogRepository::getDiffText($company);

        CompanyLogRepository::addLog('basics_manage','edit_basics', $editText);
//        $editText = CompanyLogRepository::getDiffText($obj, RecruitLogHelper::class);

        $company->save();

        app()->build(CompaniesRepository::class)->saveAddressesAndDepartments($this->request->get('addresses'),
            $this->request->get('departments'),$company->id);

        $company = Company::find($company->id);
        $company->addresses;
        foreach ($company->addresses as &$v) {
            $v->area = [$v->province_id,$v->city_id,$v->district_id];
            $v->area_text = Area::where('id', $v->province_id)->value('cname').
                Area::where('id', $v->city_id)->value('cname').
                Area::where('id', $v->district_id)->value('cname');
        }
        $company->full_logo = getCompanyLogo($company->logo);
        $company->industry;
        $company->conglomerate;
        $company->departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        getOptionsText($company);
//        $company->is_demand_side = count($company->thirdParty)>0?1:0;
        return $this->apiReturnJson(0, $company);
    }

    public function getBacklog()
    {
        //委托申请
        $company = $this->getCurrentCompany();
        $user = $this->getUser();
        $entrustApplyData = Entrust::where('company_id',$company->id)->where('status',0)->get();
        $entrustApplyData->load('job');
        $entrustApplyData->load('recruit');
        $entrustApplyData->load('thirdParty');
        $entrustApply = [];
        foreach ($entrustApplyData as $v) {
            $entrustApply[] = [
              'recruit_id'=>$v->recruit->id,
              'entrust_id'=>$v->id,
              'job_name'=>$v->job->name,
              'need_num'=>$v->recruit->need_num,
              'third_party_name'=>$v->thirdParty->company_alias,
              'created_at'=>$v->created_at->toDateTimeString(),
            ];
        }
        //审核委托
        $checkEntrustData = Entrust::where('third_party_id',$company->id)->where('status',0)->get();
        $checkEntrustData->load('job');
        $checkEntrustData->load('recruit');
        $checkEntrustData->load('company');
        $checkEntrust = [];
        foreach ($checkEntrustData as $v) {
            $checkEntrust[] = [
                'recruit_id'=>$v->recruit->id,
                'entrust_id'=>$v->id,
                'job_name'=>$v->job->name,
                'need_num'=>$v->recruit->need_num,
                'company_name'=>$v->company->company_alias,
                'created_at'=>$v->created_at->toDateTimeString(),
            ];
        }

        $recruitResumesRepository = app()->build(RecruitResumesRepository::class);
        //正在招聘的相关 招聘id
        $recruitIds = Entrust::where('third_party_id', $company->id)->whereIn('status',[1])->pluck('company_job_recruit_id')->toArray();
        $recruitIds = array_merge($recruitIds, Recruit::where('company_id', $company->id)->whereIn('status', [1,3])->pluck('id')->toArray());

        //待处理
        $waitHandleData = RecruitResume::where(function ($query)use ($company){
            $query->where('third_party_id',$company->id)->orWhere('company_id',$company->id);
        })->whereIn('status',[1,4,6])->whereIn('company_job_recruit_id', $recruitIds)->get();
        $waitHandleData->load('job');
        $waitHandleData->load('resume');
        $waitHandleData->load('recruit');
        $waitHandleData->load('company');
        $waitHandleData->load('thirdParty');
        $waitHandle = [];
        foreach ($waitHandleData as $v) {

            $recruitResumesRepository->addFieldText($v);
            $waitHandle[] = [
                'recruit_id'=>$v->company_job_recruit_id,
                'entrust_id'=>$v->company_job_recruit_entrust_id,
                'resume_id'=>$v->resume_id,
                'company_name'=>$v->company->company_alias,
                'third_party_name'=>$v->thirdParty?$v->thirdParty->company_alias:'',
                'recruit_resume_id'=>$v->id,
                'job_name'=>$v->job->name,
                'resume_name'=>$v->resume->name,
                'status'=>$v->status,
                'status_str'=>$v->status_str,
                'created_at'=>$v->created_at->toDateTimeString(),
                'updated_at'=>$v->updated_at->toDateTimeString(),
            ];
        }

        //待面试
        $waitInterviewData = RecruitResume::whereIn('status', [2,3,5])->whereIn('company_job_recruit_id', $recruitIds)->get();
        $waitInterviewData->load('job');
        $waitInterviewData->load('resume');
        $waitInterviewData->load('recruit');
        $waitInterviewData->load('company');
        $waitInterviewData->load('thirdParty');
        $waitInterview = [];
        foreach ($waitInterviewData as $v) {

            $recruitResumesRepository->addFieldText($v);
            $waitInterview[] = [
                'recruit_id'=>$v->company_job_recruit_id,
                'entrust_id'=>$v->company_job_recruit_entrust_id,
                'resume_id'=>$v->resume_id,
                'company_name'=>$v->company->company_alias,
                'third_party_name'=>$v->thirdParty?$v->thirdParty->company_alias:'无',
                'recruit_resume_id'=>$v->id,
                'job_name'=>$v->job->name,
                'resume_name'=>$v->resume->name,
                'status'=>$v->status,
                'status_str'=>$v->status_str,
                'interview_at'=>$v->interview_at,
                'created_at'=>$v->created_at->toDateTimeString(),
                'updated_at'=>$v->updated_at->toDateTimeString(),
            ];
        }

        //待入职
        $waitEntryData = RecruitResume::where(function ($query)use ($company){
            $query->where('third_party_id',$company->id)->orWhere('company_id',$company->id);
        })->whereIn('status',[7])->whereIn('company_job_recruit_id', $recruitIds)->get();
        $waitEntryData->load('job');
        $waitEntryData->load('resume');
        $waitEntryData->load('recruit');
        $waitEntryData->load('company');
        $waitEntryData->load('thirdParty');
        $waitEntry = [];
        foreach ($waitEntryData as $v) {

            $recruitResumesRepository->addFieldText($v);
            $waitEntry[] = [
                'recruit_id'=>$v->company_job_recruit_id,
                'entrust_id'=>$v->company_job_recruit_entrust_id,
                'resume_id'=>$v->resume_id,
                'company_name'=>$v->company->company_alias,
                'third_party_name'=>$v->thirdParty?$v->thirdParty->company_alias:'无',
                'recruit_resume_id'=>$v->id,
                'job_name'=>$v->job->name,
                'resume_name'=>$v->resume->name,
                'status'=>$v->status,
                'status_str'=>$v->status_str,
                'entry_at'=>$v->entry_at,
                'created_at'=>$v->created_at->toDateTimeString(),
                'updated_at'=>$v->updated_at->toDateTimeString(),
            ];
        }

        return $this->apiReturnJson(0,compact('entrustApply','checkEntrust',
            'waitHandle','waitInterview','waitEntry'));
    }

    public function getCalendarData()
    {
        $start_date = $this->request->get('start_date',date('Y-m-01'));
        $center_date = $this->request->get('center_date', null);
        $end_date = $this->request->get('end_date',date('Y-m-d 23:59:59'));
        $end_date = date('Y-m-d 23:59:59', strtotime($end_date));
        if($center_date){
            $start_date = date('Y-m-d', strtotime($center_date)-(3600*24*30));
            $end_date = date('Y-m-d', strtotime($center_date)+(3600*24*30));
        }
        //面试
        $interviews = RecruitResumeLog::select(DB::raw('left (other_data,10) as date'))
            ->where('user_id', $this->getUser()->id)
            ->whereIn('status',[2,3,5])
        ->where('other_data','>=',$start_date)->where('other_data','<=',$end_date)->get()->keyBy('date')->toArray();
        $dates = [];
        while (strtotime($start_date)<strtotime($end_date)){
            $start_date = date('Y-m-d', strtotime($start_date));
            $dates[] = [
                'date'=>$start_date,
                'have_interview'=>isset($interviews[$start_date])?1:0,
            ];
            $start_date = date('Y-m-d', strtotime($start_date)+3600*24);
        }
        return $this->apiReturnJson(0,$dates);
    }

    public function getUsers()
    {
        $company_id = $this->request->get('company_id',$this->getCurrentCompany()->id);
        $companyUsers = CompanyUser::where('company_id', $company_id)->get();
        $userIds = $companyUsers->pluck('user_id')->toArray();
        $roleIds = $companyUsers->pluck('company_role_id')->toArray();
        $users = User::whereIn('id', $userIds)->get();
        $users->load('info');
        $users = $users->keyBy('id')->toArray();
        $roles = CompanyRole::whereIn('id', $roleIds)->get()->keyBy('id')->toArray();
        $data = [];
        foreach ($companyUsers as $companyUser) {
            $user = $users[$companyUser->user_id];
            if(isset($roles[$companyUser->company_role_id]))
                $role = $roles[$companyUser->company_role_id];
            else
                $role = null;
            $info = $user['info'];
            $data[] = [
              'id'=>$user['id'],
              'name'=>$info?$info['realname']:'无姓名',
              'role_name'=>$role?$role['name']:'无角色',
            ];
        }
        return $this->apiReturnJson(0,$data);
    }

    public function getUserList()
    {
        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $department_id = app('request')->get('department_id');
        $name = app('request')->get('name');

        if($name){
            $userIds = UserBasicInfo::where('realname','like',"%$name%")->pluck('user_id')->toArray();
        }else{
            $userIds = null;
        }

        $company_id = $this->request->get('company_id',$this->getCurrentCompany()->id);
        $model = new CompanyUser();
        if($userIds){
            $model = $model->whereIn('user_id', $userIds);
        }
        if($department_id){
            $model = $model->where('department_id', $department_id);
        }
        $model1 = clone $model;
        $companyUsers = $model->where('company_id', $company_id)->skip($pageSize*($pagination-1))->take($pageSize)->orderByRaw("FIELD(company_role_id, 1) desc")->get();
        $count = $model1->where('company_id', $company_id)->count();
        $companyUsers->load('department');
        $userIds = $companyUsers->pluck('user_id')->toArray();
//        $roleIds = $companyUsers->pluck('company_role_id')->toArray();
        $users = User::whereIn('id', $userIds)->get();
        $users->load('info');
        $users = $users->keyBy('id')->toArray();
        $data = [];
        foreach ($companyUsers as $companyUser) {
            $user = $users[$companyUser->user_id];
            if(!$user)
                continue;
            $is_manager = 0;
            $_roles = getCompanyRoles($this->getCurrentCompany(), $user);
            $role_names = [];
            $role_ids = [];
            foreach ($_roles as $role) {
                if($role['id']==1)
                    $is_manager = 1;
                $role_names[] = $role['name'];
                $role_ids[] = $role['id'];
            }
            $info = $user['info'];

            if($companyUser->department && $companyUser->department->level==1){
                $department_ids = [$companyUser->department_id];
                $department_name = $companyUser->department->name;
            }elseif($companyUser->department && $companyUser->department->level==2){
                $department_ids = [$companyUser->department->parent->id,$companyUser->department_id];
                $department_name = $companyUser->department->parent->name.'-'.$companyUser->department->name;
            }else{
                $department_ids = [];
                $department_name = null;
            }
            $data[] = [
              'id'=>$user['id'],
              'name'=>$info?$info['realname']:'无姓名',
              'role_names'=>$role_names,
              'role_ids'=>$role_ids,
              'email'=>$user['email'],
              'confirmed'=>$user['confirmed'],
              'department'=>$department_name,
              'department_id'=>$companyUser->department_id,
              'department_ids'=>$department_ids,
              'is_manager'=>$is_manager,
              'avatar_url'=>getPicFullUrl($info['avatar']),
            ];
        }
        CompanyLogRepository::addLog('company_user_manage','show_user',"查看企业人员列表 第".request('pagination', 1)."页");
        return $this->apiReturnJson(0,$data,null,['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }

    public function userShow($id)
    {
        $user = User::find($id);
        $company = $this->getCurrentCompany();
        $info = UserBasicInfo::where('user_id', $id)->first();
        $companyUser = CompanyUser::where('company_id', $company->id)->where('user_id', $id)->first();

        if($companyUser->department && $companyUser->department->level==1){
            $department_ids = [$companyUser->department_id];
            $department_name = $companyUser->department->name;
        }elseif($companyUser->department && $companyUser->department->level==2){
            $department_ids = [$companyUser->department->parent->id,$companyUser->department_id];
            $department_name = $companyUser->department->parent->name.'-'.$companyUser->department->name;
        }else{
            $department_ids = [];
            $department_name = null;
        }
        $_info = $this->userRepository->getInfo($user);
        $info->department_name = $department_name;
        $info->department_ids = $department_ids;
        $info->start_work_at = $_info['start_work_at'];
        $info->entry_at = $companyUser->entry_at;

        $_roles = getCompanyRoles($company, $user);

        $info->address_id = $companyUser->address_id;
        if($info->address_id)
            $info->address = CompanyAddress::find($info->address_id);

        $role_ids = [];
        $role_names = [];
        $is_manager = 0;
        foreach ($_roles as $role) {
            if($role['id']==1)
                $is_manager = 1;
            $role_names[] = $role['name'];
            $role_ids[] = $role['id'];
        }
        $info->role_ids = $role_ids;
        $info->role_names = $role_names;
        $info->is_manager = $is_manager;
        $info->avatar_url = getPicFullUrl($info->avatar);
        $info->work_years = getYearsText($info->start_work_at, date('Y-m-d'));
        $info->entry_years = getYearsText($info->entry_at, date('Y-m-d'));

        CompanyLogRepository::addLog('company_user_manage','show_user',"查看详情 $info->realname");

        return $this->apiReturnJson(0,$info);
    }

    public function getUserPermissionScope($id)
    {
        $user = User::find($id);
        $company = $this->getCurrentCompany();
        $info = UserBasicInfo::where('user_id', $id)->first();
        $companyUser = CompanyUser::where('company_id', $company->id)->where('user_id', $id)->first();

        if($companyUser->department && $companyUser->department->level==1){
            $department_ids = [$companyUser->department_id];
            $department_name = $companyUser->department->name;
        }elseif($companyUser->department && $companyUser->department->level==2){
            $department_ids = [$companyUser->department->parent->id,$companyUser->department_id];
            $department_name = $companyUser->department->parent->name.'-'.$companyUser->department->name;
        }else{
            $department_ids = [];
            $department_name = null;
        }
        $_info = $this->userRepository->getInfo($user);
        $info->department_name = $department_name;
        $info->department_ids = $department_ids;
        $info->start_work_at = $_info['start_work_at'];
        $info->entry_at = $companyUser->entry_at;

        $_roles = getCompanyRoles($company, $user);

        $info->address_id = $companyUser->address_id;
        if($info->address_id)
            $info->address = CompanyAddress::find($info->address_id);

        $role_ids = [];
        $role_names = [];
        $is_manager = 0;
        foreach ($_roles as $role) {
            if($role['id']==1)
                $is_manager = 1;
            $role_names[] = $role['name'];
            $role_ids[] = $role['id'];
        }

        $permissions_tree = RoleRepository::getTreeByRoles($_roles);
        $permissions_tree = RoleRepository::getScopeByTree($permissions_tree, $company->id, $user->id);

        return $this->apiReturnJson(0, $permissions_tree);
    }

    public function setUserPermissionScope($id)
    {
        $user = User::find($id);
        $company = $this->getCurrentCompany();

        $permissions = $this->request->get('scopes');
        foreach ($permissions as $permission) {
            $has = CompanyUserPermissionScope::where('user_id', $user->id)->where('company_id', $company->id)->where('company_permission_id', $permission['permission_id'])->first();
            if($has){
                CompanyUserPermissionScope::where('id', $has->id)->update([
                    'type'=>$permission['type'],
                    'department_ids'=>is_array($permission['department_ids'])?implode(',', $permission['department_ids']):$permission['department_ids'],
                ]);
            }else{
                CompanyUserPermissionScope::create([
                    'company_id'=>$company->id,
                    'company_permission_id'=>$permission['permission_id'],
                    'user_id'=>$user->id,
                    'key'=>$permission['permission_id'].'_'.$company->id.'_'.$user->id,
                    'type'=>$permission['type'],
                    'department_ids'=>is_array($permission['department_ids'])?implode(',', $permission['department_ids']):$permission['department_ids'],
                ]);
            }
        }
        return $this->apiReturnJson(0);
    }

    public function storeUser(Request $request)
    {
        $department_id = $request->get('department_id');
        $email = $request->get('email');
        $roles =  $request->get('roles');
        $company = $this->getCurrentCompany();

        $user = User::where('email', $email)->where('confirmed', 1)->where('deleted', 0)->first();
        if(!$user)
            $user = User::where('email', $email)->where('deleted', 0)->first();
        if($user && CompanyUser::where('company_id', $company->id)->where('user_id',$user->id)->first()){
            return $this->apiReturnJson(9999, null, '该用户已在企业中, 请直接修改');
        }
        $user = app()->build(CompaniesRepository::class)->handleUser($company, $email, $roles, $department_id);

        CompanyLogRepository::addLog('company_user_manage','add_user',"新增企业人员 $email");

        return $this->apiReturnJson(0);
    }

    public function updateUser($user_id, Request $request)
    {
        $department_id = $request->get('department_id');
        if(is_array($department_id) && count($department_id)>0)
            $department_id = $department_id[count($department_id)-1];
        $email = $request->get('email');
        $roles =  $request->get('roles');
        $entry_at =  $request->get('entry_at');
        $address_id =  $request->get('address_id');

        $company = $this->getCurrentCompany();
        $user = User::find($user_id);

        $this->userRepository->setInfo($user, $request->all());

        if($user && $email &&$email!=$user->email){
            if(User::where('id','!=',$user->id)->where('confirmed',1)->where('email', $user->email)->where('deleted',0)->first()){
                return $this->apiReturnJson(9999, null, '该邮箱已经存在');
            }
            $user->email = $email;
            $user->save();
        }
        if(!$email){
            $email = $user->email;
        }
        $requestData = $request->all();
        unset($requestData['department_id']);
        $companyUser = CompanyUser::where('company_id', $company->id)->where('user_id', $user_id)->first();
        $companyUser->fill($requestData);
        $companyUser->save();
        $user = app()->build(CompaniesRepository::class)->handleUser($company, $email, $roles, $department_id);

        CompanyLogRepository::addLog('company_user_manage','edit_user',"编辑企业人员 $email");
        return $this->apiReturnJson(0);
    }

    public function deleteUser($user_id, Request $request)
    {
        $user = User::find($user_id);
        CompanyLogRepository::addLog('company_user_manage','delete_user',"删除企业人员 $user->email");

        $company = $this->getCurrentCompany();
        CompanyUser::where('user_id', $user_id)->where('company_id', $company->id)->delete();
        CompanyUserRole::where('user_id', $user_id)->where('company_id', $company->id)->delete();

        return $this->apiReturnJson(0);
    }

    public function countStatistics()
    {
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyCountStatistics($this->getCurrentCompany());
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyCountStatistics($this->getCurrentCompany());
        }
        return $this->apiReturnJson(0,$data);
    }

    public function dataStatistics()
    {
        $start_date = $this->request->get('start_date',date('Y-m-01'));
        $end_date = $this->request->get('end_date',date('Y-m-d 23:59:59'));
        if(!$start_date)
            $start_date = date('Y-m-01');
        if(!$end_date)
            $end_date = date('Y-m-d 23:59:59');
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }
        return $this->apiReturnJson(0,$data);
    }

    public function dataStatisticsExcel()
    {
        $start_date = $this->request->get('start_date',date('Y-m-01'));
        $end_date = $this->request->get('end_date',date('Y-m-d 23:59:59'));
        if(!$start_date)
            $start_date = date('Y-m-01');
        if(!$end_date)
            $end_date = date('Y-m-d 23:59:59');
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }
        $res = app()->build(StatisticsRepository::class)->getExcelData($data);
        $excelHelper = new ExcelHelper();
        $excelHelper->dumpExcel(array_values($res['title']),$res['data'],'数据', "{$start_date}-{$end_date}招聘数据");
    }

    public function dataStatisticsDetail()
    {
        $company_id = $this->request->get('company_id');
        $start_date = $this->request->get('start_date',date('Y-m-01'));
        $end_date = $this->request->get('end_date',date('Y-m-d 23:59:59'));
        if(!$start_date)
            $start_date = date('Y-m-01');
        if(!$end_date)
            $end_date = date('Y-m-d 23:59:59');
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatisticsDetail($this->getCurrentCompany(), $company_id,$start_date,$end_date);
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyDataStatisticsDetail($this->getCurrentCompany(), $company_id,$start_date,$end_date);
        }
        return $this->apiReturnJson(0,$data);
    }

    public function dataStatisticsDetailExcel()
    {
        $company_id = $this->request->get('company_id');
        $start_date = $this->request->get('start_date',date('Y-m-01'));
        $end_date = $this->request->get('end_date',date('Y-m-d 23:59:59'));
        if(!$start_date)
            $start_date = date('Y-m-01');
        if(!$end_date)
            $end_date = date('Y-m-d 23:59:59');
        $company = Company::find($company_id);
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatisticsDetail($this->getCurrentCompany(), $company_id,$start_date,$end_date);
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyDataStatisticsDetail($this->getCurrentCompany(), $company_id,$start_date,$end_date);
        }
        $res = app()->build(StatisticsRepository::class)->getExcelDetailData($data);
        $excelHelper = new ExcelHelper();
        $excelHelper->dumpExcel(array_values($res['title']),$res['data'],'数据',"{$start_date}-{$end_date} {$company->company_alias}职位招聘数据");
    }

    public function thirdPartyStatistics()
    {
        $company_id = $this->request->get('company_id', $this->getCurrentCompany()->id);
        $third_party_id = $this->request->get('third_party_id');
        $demand_side_id = $this->request->get('demand_side_id');
        $job_id = $this->request->get('job_id');
        $department_id = $this->request->get('department_id');
        $leading = $this->request->get('leading');
        $delivery_id = $this->request->get('delivery_id');
        $sex = $this->request->get('sex',$this->request->get('gender'));
        $working_years = $this->request->get('working_years');
        $recruit_search_status = $this->request->get('recruit_search_status');
        $education = $this->request->get('education');
        $data_items = $this->request->get('data_items',[]);

        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);

        $model = new RecruitResume();

        if($third_party_id){
            $model = $model->where('third_party_id', $third_party_id);
        }
        if($demand_side_id){
            $model = $model->where('company_id', $demand_side_id);
        }
        if(!$third_party_id && !$demand_side_id){
            $model = $model->where(function ($query) use ($company_id) {
                $query->where('company_id', $company_id)->orWhere('third_party_id', $company_id);
            });
        }
        if($job_id){
            $model = $model->where('job_id', $job_id);
        }
        if($department_id && !is_array($department_id)){
            $department = CompanyDepartment::find($department_id);
            if($department->level==2){
                $departmentIds = [$department->id];
            }else{
                $departmentIds = $department->children->pluck('id')->toArray();
            }
            $jobIds = Job::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
            $model = $model->whereIn('job_id', $jobIds);
        }elseif($department_id && is_array($department_id)){
            $jobIds = Job::whereIn('department_id', $department_id)->pluck('id')->toArray();
            $model = $model->whereIn('job_id', $jobIds);
        }
        if($leading){
            $recruitIds = Recruit::where('leading_id', $leading)->pluck('id');
            $entrustIds = Entrust::where('leading_id', $leading)->pluck('id');
            $model = $model->where(function ($query)use($recruitIds, $entrustIds){
                $query->whereIn('company_job_recruit_id', $recruitIds)->orWhereIn('company_job_recruit_entrust_id', $entrustIds);
            });
        }

        $resumeModel = new Resume();
        $hasResumeSearch = false;

        if($sex){
            $resumeModel = $resumeModel->where('gender',$sex);
            $hasResumeSearch = true;
        }
        if($working_years){
            $resumeModel = $resumeModel->where('working_years',$working_years);
            $hasResumeSearch = true;
        }
        if($education){
            $resumeModel = $resumeModel->where('education',$education);
            $hasResumeSearch = true;
        }
        if($hasResumeSearch){
            $model = $model->whereIn('resume_id', $resumeModel->pluck('id'));
        }

        if($delivery_id){
            $recruit_resume_ids = RecruitResumeLog::where('user_id', $delivery_id)->where('status', 1)->pluck('company_job_recruit_resume_id');
            $model = $model->whereIn('id', $recruit_resume_ids);
        }

        $model = $this->generateDateSearch($model, 'resume_deliver_date', 'created_at');
        $model = $this->generateDateSearch($model, 'resume_end_date', 'updated_at');
        $model = $this->generateDateSearch($model, 'resume_entry_date', 'formal_entry_at');
        //最后面试时间
        $has_last_interview_date = $this->checkHasDateSearch('last_interview_date');
        $model = $this->generateDateSearch($model, 'last_interview_date', 'interview_at');
        //首次面试时间
        $has_first_interview_date = $this->checkHasDateSearch('first_interview_date');
        if($has_first_interview_date){
            $recruitResumeLogModel1 = RecruitResumeLog::where('interview_count',1);
            $recruitResumeLogModel1 = $this->generateDateSearch($recruitResumeLogModel1, 'first_interview_date', 'interview_at');
            $model = $model->whereIn('id', $recruitResumeLogModel1->pluck('company_job_recruit_resume_id'));
        }
        //第二次面试时间
        $has_second_interview_date = $this->checkHasDateSearch('second_interview_date');
        if($has_second_interview_date){
            $recruitResumeLogModel2 = RecruitResumeLog::where('interview_count',2);
            $recruitResumeLogModel2 = $this->generateDateSearch($recruitResumeLogModel2, 'second_interview_date', 'interview_at');
            $model = $model->whereIn('id', $recruitResumeLogModel2->pluck('company_job_recruit_resume_id'));
        }
        //第三次面试时间
        $has_third_interview_date = $this->checkHasDateSearch('third_interview_date');
        if($has_third_interview_date){
            $recruitResumeLogModel3 = RecruitResumeLog::where('interview_count',2);
            $recruitResumeLogModel3 = $this->generateDateSearch($recruitResumeLogModel3, 'third_interview_date', 'interview_at');
            $model = $model->whereIn('id', $recruitResumeLogModel3->pluck('company_job_recruit_resume_id'));
        }

        $recruitModel = new Recruit();
        $recruitModel = $recruitModel->where('id','>',0);
        $recruitModel = $this->generateDateSearch($recruitModel, 'recruit_start_date', 'created_at');
        $recruitModel = $this->generateDateSearch($recruitModel, 'recruit_end_date', 'end_at');
        if($recruit_search_status){
            switch ($recruit_search_status){
                case 1:
                    $recruitModel = $recruitModel->whereIn('status', [2,3,7,4,5]);
                    break;
                case 2:
                    $recruitModel = $recruitModel->whereIn('status', [1,4,5]);
                    break;
                case 3:
                    $recruitModel = $recruitModel->whereIn('status', [4,5]);
                    break;
                case 4:
                    $recruitModel = $recruitModel->whereIn('status', [6]);
                    break;
                case 5:
                    $recruitModel = $recruitModel->where('is_public', 1)->whereNotIn('status', [6,7]);
                    break;
                case 6:
                    $recruitModel = $recruitModel->where('is_public', 0)->whereNotIn('status', [6,7]);
                    break;
            }
        }
        if(count($recruitModel->getQuery()->wheres)>1)
            $model = $model->whereIn('company_job_recruit_id', $recruitModel->pluck('id'));

        $_model = clone $model;
        $count =$_model->count();
        $list = $model->skip($pageSize*($pagination-1))->take($pageSize)->get();

        $list->load('resume');
        $list->load('thirdParty');
        $list->load('company');
        $list->load('job');
        $list->load('delivery');
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $list->pluck('job_id'))->get())->keyBy('id')->toArray();
        foreach ($list as &$v) {
            $v->resume =  app()->build(ResumesRepository::class)->getData($v->resume);
            $this->recruitResumesRepository->addFieldText($v);

            //最后面试时间
            $has_last_interview_date = $this->checkHasDateSearch('last_interview_date');
            if($has_last_interview_date){
                $v->last_interview_feedback = RecruitResumeLog::where('company_job_recruit_resume_id', $v->id)->where('status', 4)->orderBy('id','desc')->value('other_data');
            }
            //首次面试时间
            $has_first_interview_date = $this->checkHasDateSearch('first_interview_date');
            if($has_first_interview_date){
                $v->first_interview_feedback = RecruitResumeLog::where('company_job_recruit_resume_id', $v->id)->where('status', 4)->where('interview_count',1)->orderBy('id','desc')->value('other_data');
            }
            //第二次面试时间
            $has_second_interview_date = $this->checkHasDateSearch('second_interview_date');
            if($has_second_interview_date){
                $v->second_interview_feedback = RecruitResumeLog::where('company_job_recruit_resume_id', $v->id)->where('status', 4)->where('interview_count',2)->orderBy('id','desc')->value('other_data');
            }
            //第三次面试时间
            $has_third_interview_date = $this->checkHasDateSearch('third_interview_date');
            if($has_third_interview_date){
                $v->third_interview_feedback = RecruitResumeLog::where('company_job_recruit_resume_id', $v->id)->where('status', 4)->where('interview_count',3)->orderBy('id','desc')->value('other_data');
            }

            if(in_array('resume_look_count', $data_items)){
                $v->resume_look_count = RecruitResumeLook::where('company_job_recruit_resume_id', $v->id)->count();
            }
            $gradeData = null;
            if(in_array('necessary_skills_grade', $data_items)){
                if(!$gradeData)
                    $gradeData = $this->recruitResumesRepository->matching($v);
                $v->necessary_skills_grade = $gradeData['necessary_skills_score'];
            }
            if(in_array('optional_skills_grade', $data_items)){
                if(!$gradeData)
                    $gradeData = $this->recruitResumesRepository->matching($v);
                $v->optional_skills_grade = $gradeData['optional_skills_score'];
            }
            if(in_array('job_test_grade', $data_items)){
                if(!$gradeData)
                    $gradeData = $this->recruitResumesRepository->matching($v);
                $v->job_test_grade = $gradeData['score'];
            }
            if(in_array('other_deliver_count', $data_items)){
                $v->other_deliver_count = RecruitResume::where('resume_id', $v->resume_id)->where('id','!=',$v->id)->count();
            }
            if(in_array('hire_count', $data_items)){
                $v->hire_count = RecruitResume::where('resume_id', $v->resume_id)->where('status','>=',7)->count();
            }
            $v->job = $jobs[$v->job_id];
        }

        CompanyLogRepository::addLog('data_analysis','third_party_analysis',"查看第三方选择数据统计");

        return $this->apiReturnJson(0,$list,null,['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }

    public function thirdPartyStatisticsExcel()
    {
        request()->offsetSet('pageSize',999999);
        $res = $this->thirdPartyStatistics();
        if($res['code']!=0){
            return $res;
        }
        $list = $res['data'];
        $title = [
            '第三方企业名称',
            '职位',
            '部门',
            '名称',
            '状态',
            '投递时间',
        ];

        $excelHelper = new ExcelHelper();
        $data = [];
        $last_interview_feedback = false;
        $first_interview_feedback = false;
        $second_interview_feedback = false;
        $third_interview_feedback = false;
        $resume_look_count = false;
        $necessary_skills_grade = false;
        $optional_skills_grade = false;
        $job_test_grade = false;
        $other_deliver_count = false;
        $hire_count = false;

        foreach ($list as $v) {
            $_data = [];
            $_data[] = $v->thirdParty?$v->thirdParty->company_alias:'';
            $_data[] = $v['job']['name'];
            $_data[] = $v['job']['department']['full_name'];
            $_data[] = $v->resume->name;
            $_data[] = $v->status_str;
            $_data[] = $v->created_at;
            if(isset($v->last_interview_feedback)){
                $_data[] = $v->last_interview_feedback;
                $last_interview_feedback = true;
            }elseif($last_interview_feedback){
                $_data[] = '';
            }
            if(isset($v->first_interview_feedback)){
                $_data[] = $v->first_interview_feedback;
                $first_interview_feedback = true;
            }elseif($first_interview_feedback){
                $_data[] = '';
            }
            if(isset($v->second_interview_feedback)){
                $_data[] = $v->second_interview_feedback;
                $second_interview_feedback = true;
            }elseif($second_interview_feedback){
                $_data[] = '';
            }
            if(isset($v->third_interview_feedback)){
                $_data[] = $v->third_interview_feedback;
                $third_interview_feedback = true;
            }elseif($third_interview_feedback){
                $_data[] = '';
            }
            if(isset($v->resume_look_count)){
                $_data[] = $v->resume_look_count;
                $resume_look_count = true;
            }elseif($resume_look_count){
                $_data[] = '';
            }
            if(isset($v->necessary_skills_grade)){
                $_data[] = $v->necessary_skills_grade;
                $necessary_skills_grade = true;
            }elseif($necessary_skills_grade){
                $_data[] = '';
            }
            if(isset($v->optional_skills_grade)){
                $_data[] = $v->optional_skills_grade;
                $optional_skills_grade = true;
            }elseif($optional_skills_grade){
                $_data[] = '';
            }
            if(isset($v->job_test_grade)){
                $_data[] = $v->job_test_grade;
                $job_test_grade = true;
            }elseif($job_test_grade){
                $_data[] = '';
            }
            if(isset($v->other_deliver_count)){
                $_data[] = $v->other_deliver_count;
                $other_deliver_count = true;
            }elseif($other_deliver_count){
                $_data[] = '';
            }
            if(isset($v->hire_count)){
                $_data[] = $v->hire_count;
                $hire_count = true;
            }elseif($hire_count){
                $_data[] = '';
            }
            $data[] = $_data;
        }

        if($last_interview_feedback)
            $title[] = '最后一次面试反馈';
        if($first_interview_feedback)
            $title[] = '第一次面试反馈';
        if($second_interview_feedback)
            $title[] = '第二次面试反馈';
        if($third_interview_feedback)
            $title[] = '第三次面试反馈';
        if($resume_look_count)
            $title[] = '简历阅览次数';
        if($necessary_skills_grade)
            $title[] = '必要技能分数';
        if($optional_skills_grade)
            $title[] = '选择技能分数';
        if($job_test_grade)
            $title[] = '职位测试分数';
        if($other_deliver_count)
            $title[] = '投递其他简历次数';
        if($hire_count)
            $title[] = '被录用次数';
        $excelHelper->dumpExcel($title,$data,'数据分析简历数据', "数据分析简历数据");
    }

    public function checkHasDateSearch($timeStr)
    {
        $start_at = $this->request->get($timeStr.'_start');
        $end_at = $this->request->get($timeStr.'_end');
        return $start_at || $end_at;
    }
    public function generateDateSearch($model, $timeStr, $modelField=null)
    {
        $start_at = $this->request->get($timeStr.'_start');
        $end_at = $this->request->get($timeStr.'_end');
        if(!$modelField)
            $modelField = $timeStr;
        if($start_at && !$end_at){
            $model = $model->where($modelField, '>=' ,$start_at);
        }elseif (!$start_at && $end_at){
            $model = $model->where($modelField, '<=' ,$end_at);
        }elseif ($start_at && $end_at){
            $model = $model->where($modelField, '>=' ,$start_at)->where($modelField, '<=' ,$end_at);
        }
        return $model;
    }

    public function resumeRelationSet()
    {
        $company_id = $this->request->get('company_id', $this->getCurrentCompany()->id);
        $resume_id = $this->request->get('resume_id');
        $type = $this->request->get('type',2);
        $action = $this->request->get('action','add');
        if(!$company_id || in_array($type,[2,3])!==true || in_array($action, ['add','cancel'])!==true){
            return $this->apiReturnJson(9999, null, '参数出错');
        }
        $has = CompanyResume::where('company_id', $company_id)
            ->where('resume_id', $resume_id)
            ->where('type',$type)
            ->first();
        if($has && $action=='cancel'){
            $has->delete();
        }
        if(!$has && $action=='add'){
            CompanyResume::create([
                'company_id'=>$company_id,
                'resume_id'=>$resume_id,
                'type'=>$type,
                'creator_id'=>$this->getUser()->id,
            ]);
        }
        return $this->apiReturnJson(0);
    }

    public function changeManager(Request $request)
    {
        $email = $request->get('email');
        $company = $this->getCurrentCompany();

        $has = CompanyManagerLog::where('company_id', $company->id)->where('status', '=', 0)->first();
        if($has){
            return $this->apiReturnJson(9999,null,'正在申请变更中');
        }

        $user = User::where('email', $email)->where('confirmed', 1)->where('deleted', 0)->first();
        if(!$user)
            $user = User::where('email', $email)->where('deleted', 0)->first();
        if($user){
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanyManagerChangeEmail($user, $company, true));
        }else{
            $userRe = app()->build(UserRepository::class);
            $user = $userRe->generateInviteUser($email);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanyManagerChangeEmail($user, $company, true));
        }
        $token = ExternalToken::where('userid', $user->id)->first();

        $editText = "管理员更换为:$email-".UserBasicInfo::where('user_id', $user->id)->value('realname');
        CompanyLogRepository::addLog('basics_manage','edit_manager', $editText);

        CompanyManagerLog::create([
            'company_id'=>$company->id,
            'new_id'=>$user->id,
            'old_id'=>CompanyUser::where('company_id', $company->id)->where('company_role_id', 1)->value('user_id'),
            'status'=>0,
            'token'=>$token->token,
        ]);
        return $this->apiReturnJson(0);
    }

    public function changeManagerAffirm(Request $request)
    {
        $user = $this->getUser();
        if(!$user){
            $log = CompanyManagerLog::where('token', $request->header('token','123'))->orderBy('id', 'desc')->first();
            if($log){
                $user = $log->user;
            }else{
                return $this->apiReturnJson(9999,null,'找不到用户');
            }
        }

        $company_id = $request->get('company_id');
        $status = $request->get('status');
        $company = Company::find($company_id);
        if(!$user || !$company || !isset($status)){
            return $this->apiReturnJson(9999,null,'参数错误');
        }

        $log = CompanyManagerLog::where('new_id', $user->id)->where('company_id', $company->id)->orderBy('id', 'desc')->first();
        if($log->status==1){
            return $this->apiReturnJson(9999,null,'已确认');
        }
        if($log->status==-1){
            return $this->apiReturnJson(9999,null,'已取消');
        }
        if($log->status==-2){
            return $this->apiReturnJson(9999,null,'超过4小时已经自动取消');
        }
        if(time()-strtotime($log->created_at)>3600*4){
            return $this->apiReturnJson(9999,null,'已超过4小时无法确认');
        }
        $log->status = 1;
        $log->save();

        \Illuminate\Support\Facades\DB::connection('musa')->table('company_user')->where('company_role_id', 1)->where('company_id', $company->id)->update(['company_role_id' => null]);
        CompanyUserRole::where('user_id', $user->id)->where('company_id', $company->id)->where('role_id', 1)->delete();

        $has = CompanyUser::where('user_id', $user->id)->where('company_id', $company->id)->first();
        if($has){
            \Illuminate\Support\Facades\DB::connection('musa')->table('company_user')->where('user_id', $user->id)->where('company_id', $company->id)->update(['company_role_id' => 1]);
        }else{
            CompanyUser::create([
                'user_id'=>$user->id,
                'company_id'=>$company->id,
                'company_role_id'=>1,
            ]);
        }

        return $this->apiReturnJson(0);
    }
}

