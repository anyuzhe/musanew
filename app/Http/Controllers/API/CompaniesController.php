<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Repositories\AreaRepository;
use App\Repositories\CompaniesRepository;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\StatisticsRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use App\ZL\ORG\Excel\ExcelHelper;
use DB;
use Illuminate\Support\Facades\Log;

class CompaniesController extends ApiBaseCommonController
{
    protected $model_name = Company::class;

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
            $_all_need_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->whereNotIn('status', [0,-2])->pluck('company_job_recruit_id')->toArray())->sum('need_num');
            $_current_need_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereIn('status', [1])->pluck('company_job_recruit_id')->toArray())->sum('need_num');
            $v->allJobCount = $_all_need_count?$_all_need_count:0;
            $v->ourJobCount = $_all_count;
            $v->currentRecruitCount = $_current_need_count?$_current_need_count:0;

            $v->logo_url = getPicFullUrl($v->logo);
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

            $v->logo_url = getPicFullUrl($v->logo);

            $entrusts->load('job');
            $entrusts->load('recruit');

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
        $company->addresses;
        foreach ($company->addresses as &$v) {
            $v->area = [$v->province_id,$v->city_id,$v->district_id];
        }
        $company->logo = getPicFullUrl($company->logo);
        $company->departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        return $this->apiReturnJson(0,$company);
    }
    public function updateCurrentInfo()
    {
        $company = $this->getCurrentCompany();
        $model = new Company();
        $model->where('id', '=', $company->id)->update($this->request->only($model->fillable));
        app()->build(CompaniesRepository::class)->saveAddressesAndDepartments($this->request->get('addresses'),
            $this->request->get('departments'),$company->id);
        return $this->apiReturnJson(0);
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
                'entrust_id'=>$v->id,
                'job_name'=>$v->job->name,
                'need_num'=>$v->recruit->need_num,
                'company_name'=>$v->company->company_alias,
                'created_at'=>$v->created_at->toDateTimeString(),
            ];
        }

        $recruitResumesRepository = app()->build(RecruitResumesRepository::class);

        //待处理
        $waitHandleData = RecruitResume::where(function ($query)use ($company){
            $query->where('third_party_id',$company->id)->orWhere('company_id',$company->id);
        })->whereIn('status',[1,2,3,4,5,6])->get();
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
                'third_party_name'=>$v->thirdParty->company_alias,
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
        $waitInterviewData = RecruitResume::where(function ($query)use($user){
            $query->where('status',2)
                ->whereIn('id',RecruitResumeLog::where('user_id',$user->id)->where('status',2)->pluck('company_job_recruit_resume_id')->toArray());
        })->orWhere(function ($query)use($user){
            $query->where('status',3)
                ->whereIn('id',RecruitResumeLog::where('user_id',$user->id)->where('status',3)->pluck('company_job_recruit_resume_id')->toArray());
        })->orWhere(function ($query)use($user){
            $query->where('status',5)
                ->whereIn('id',RecruitResumeLog::where('user_id',$user->id)->where('status',5)->pluck('company_job_recruit_resume_id')->toArray());
        })->where('status',2)->get();
        $waitInterviewData->load('job');
        $waitInterviewData->load('resume');
        $waitInterviewData->load('recruit');
        $waitInterviewData->load('company');
        $waitInterviewData->load('thirdParty');
        $waitInterview = [];
        foreach ($waitInterviewData as $v) {

            $recruitResumesRepository->addFieldText($v);
            $waitEntry[] = [
                'recruit_id'=>$v->company_job_recruit_id,
                'entrust_id'=>$v->company_job_recruit_entrust_id,
                'resume_id'=>$v->resume_id,
                'company_name'=>$v->company->company_alias,
                'third_party_name'=>$v->thirdParty->company_alias,
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
        })->whereIn('status',[6])->get();
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
                'third_party_name'=>$v->thirdParty->company_alias,
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
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }
        $res = app()->build(StatisticsRepository::class)->getExcelData($data);
        $excelHelper = new ExcelHelper();
        $excelHelper->dumpExcel(array_values($res['title']),$res['data'],'数据');
    }

    public function dataStatisticsDetail()
    {
        $company_id = $this->request->get('company_id');
        $start_date = $this->request->get('start_date',date('Y-m-01'));
        $end_date = $this->request->get('end_date',date('Y-m-d 23:59:59'));
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
        if($this->request->type ==1){
            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatisticsDetail($this->getCurrentCompany(), $company_id,$start_date,$end_date);
        }else{
            $data = app()->build(StatisticsRepository::class)->getCompanyThirdPartyDataStatisticsDetail($this->getCurrentCompany(), $company_id,$start_date,$end_date);
        }
        dd($data);
        return $this->apiReturnJson(0,$data);
    }
}
