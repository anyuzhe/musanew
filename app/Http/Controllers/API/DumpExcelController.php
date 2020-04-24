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
use App\Models\Skill;
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

class DumpExcelController extends ApiBaseCommonController
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
            CompanyLogRepository::addLog('data_analysis','third_party_analysis',"查看第三方数据统计");

            $data = app()->build(StatisticsRepository::class)->getCompanyDataStatistics($this->getCurrentCompany(),$start_date,$end_date);
        }else{
            CompanyLogRepository::addLog('data_analysis','third_party_analysis',"查看需求方数据统计");

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
        $excelHelper->dumpExcel(array_values($res['title']),$res['data'],"{$start_date}-{$end_date}招聘", "{$start_date}-{$end_date}招聘");
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
        $excelHelper->dumpExcel(array_values($res['title']),$res['data'],"{$start_date}-{$end_date} {$company->company_alias}职位招聘数据","{$start_date}-{$end_date} {$company->company_alias}职位招聘数据");
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

    public function DumpRecruitData()
    {

        set_time_limit(0);
        $recruits = Recruit::all();

        $recruits->load('job');
        $recruits->load('leading');
        $recruits->load('entrusts');
        $recruits->load('company');

        $leadIds = [];
        foreach ($recruits as &$recruit) {
            $recruit->entrusts->load('thirdParty');
            foreach ($recruit->entrusts as &$entrust) {
                $entrust->thirdParty;
                $leadIds[] = $entrust->leading_id;
            }
        }
        unset($recruit);
        unset($entrust);
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

        $data =[];
//        foreach ($recruits as $recruit) {
//            $_data = [];
//            $_data[] = $recruit['job']['name'];
//            $_data[] = $recruit['company']['company_alias'];
//            $_third_party = '';
//            foreach ($recruit['entrusts'] as $entrust) {
//                $_third_party.=$entrust['third_party']['company_alias'].',';
//            }
//            $_data[] = $_third_party;
//            $_data[] = $recruit['leading']['realname'];
//            $_data[] = $recruit['need_num'];
//            $_data[] = $recruit['resume_num'];
//            $_data[] = $recruit['done_num'];
//            $_data[] = $recruit['wait_entry_num'];
//            $_data[] = $recruit['residue_num'];
//            $_data[] = $recruit['status_text'];
//            $_data[] = $recruit['created_at'];
//            $_data[] = '';
//            $_data[] = '';
//            $_resumes = RecruitResume::where('company_job_recruit_id', $recruit['id'])->get();
//
//            $_resumes->load('resume');
//            $_resumes->load('thirdParty');
//            $_resumes->load('company');
//            $is_next = false;
//            foreach ($_resumes as $_resume) {
//                if($is_next){
//                    $_data = ['','','','','','','','','','','','',''];
//                }
//
//                $this->recruitResumesRepository->addFieldText($_resume);
//                $_data[] = $_resume['resume']['name'];
//                $_data[] = $_resume['status_str'];
//                $_data[] = $_resume['resume_source_str'];
//                $_data[] = $_resume['created_at'];
//                $is_next = true;
//                $data[] = $_data;
//            }
//            if(!$is_next)
//                $data[] = $_data;
//        }

        $data =[];
        foreach ($recruits as $recruit) {
            $_third_party = '';
            foreach ($recruit['entrusts'] as $entrust) {
                $_third_party.=$entrust['third_party']['company_alias'].',';
            }
            $_resumes = RecruitResume::where('company_job_recruit_id', $recruit['id'])->get();

            $_resumes->load('resume');
            $_resumes->load('thirdParty');
            $_resumes->load('company');
            foreach ($_resumes as $_resume) {
                $this->recruitResumesRepository->addFieldText($_resume);
                $_data = [];
                $_data[] = $_resume['resume']['name'];
                $_data[] = $_resume['resume']['created_at'];
                $_data[] = $recruit['job']['name'];
                $_data[] = $recruit['job']['department']['full_name'];
//                if(isset($_resume['third_party']) || isset($_resume['thirdParty']))
//                    dd($_resume['thirdParty']);
                $_data[] = $recruit['company']['company_alias']."/".(isset($_resume['thirdParty']['company_alias'])?$_resume['thirdParty']['company_alias']:'');
                $_data[] = $_resume['status_str'];
                $_data[] = $_resume['created_at'];
                $data[] = $_data;
            }
        }

        $title = [
            '姓名',
            '简历上传时间',
            '推荐岗位',
            '推荐部门',
            '来源',
            '简历状态',
            '简历推送时间',
        ];
//        $title = [
//            '职位名称',
//            '企业',
//            '第三方企业',
//            '负责人',
//            '职位需求量',
//            '简历数量',
//            '已入职数量',
//            '待入职人数',
//            '剩余空缺',
//            '状态',
//            '发布时间',
//            '',
//            '简历：',
//            '名称',
//            '来源企业',
//            '状态',
//            '推送时间',
//        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'简历数据备份', "简历数据备份");
//        $excelHelper->dumpExcel($title,$data,'招聘数据', "招聘数据");
    }

    public function DumpData1(Request $request)
    {
        set_time_limit(0);
        $company_id = $request->get('company_id');
        //委托了的招聘
        $has_entrust_ids = Entrust::pluck('company_job_recruit_id')->toArray();
        $model = new Recruit();
        if($company_id){
            $model = $model->where('company_id',$company_id);
        }
        $recruits = $model->whereIn('status', [2,3,4,5,7])->whereIn('id', $has_entrust_ids)->get();

        $recruits->load('job');
        $recruits->load('leading');
        $recruits->load('entrusts');
        $recruits->load('company');

        $leadIds = [];
        foreach ($recruits as &$recruit) {
            $recruit->entrusts->load('thirdParty');
            foreach ($recruit->entrusts as &$entrust) {
                $entrust->thirdParty;
                $leadIds[] = $entrust->leading_id;
            }
        }
        unset($recruit);
        unset($entrust);
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

        unset($recruit);
        unset($entrust);

        $data =[];
        foreach ($recruits as $recruit) {
            $_third_party = '';
            foreach ($recruit['entrusts'] as $entrust) {
                $_third_party.=$entrust['third_party']['company_alias'].',';
            }

            $_data = [];
            $_data[] = $recruit['job']['department']['full_name'];
            $_data[] = $recruit['company']['company_alias'];
            $_data[] = $recruit['leading']['realname'];
            $_data[] = $recruit['job']['code'];
            $_data[] = $recruit['job']['name'];
            $_data[] = $recruit['job']['address']['name'];
            $_data[] = '外包';
            $_necessary_skills = '';
            $has_skill = false;
            foreach ($recruit['job']['necessary_skills'] as $necessary_skill) {
                $has_skill = true;
                $_necessary_skills .= $necessary_skill['name'].'+'.$necessary_skill['skill_level_text'].';';
            }
            if($has_skill)
                $_necessary_skills = substr($_necessary_skills,0,strlen($_necessary_skills)-1);
            $_data[] = $recruit['need_num'];
            $_data[] = $_necessary_skills;
            $_data[] = $recruit['status_text'];
            $_data[] = $recruit['created_at'];
            $_data[] = $recruit['end_at'];
            $_data[] = getDays($recruit['created_at'],$recruit['end_at']);
            $data[] = $_data;
        }

        $title = [
            '部门',
            '需求方',
            '需求方负责人',
            '职位编号',
            '职位名称',
            '工作地点',
            '人力类型',
            '岗位招聘数量',
            '职位必要技能',
            '当前状态',
            '招聘开始时间',
            '招聘关闭时间',
            '招聘时长（天）',
        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'需求表', "需求表");
    }

    public function DumpData2(Request $request)
    {
        set_time_limit(0);
        $company_id = $request->get('company_id');
        //委托了的招聘
        $has_entrust_ids = Entrust::pluck('company_job_recruit_id')->toArray();
        $model = new Recruit();
        if($company_id){
            $model = $model->where('company_id',$company_id);
        }

        $recruits = $model->whereIn('status', [2,3,4,5,7])->whereIn('id', $has_entrust_ids)->get();

        $recruits->load('job');
        $recruits->load('leading');
        $recruits->load('entrusts');
        $recruits->load('company');

        $leadIds = [];
        foreach ($recruits as &$recruit) {
            $recruit->entrusts->load('thirdParty');
            foreach ($recruit->entrusts as &$entrust) {
                $entrust->thirdParty;
                $leadIds[] = $entrust->leading_id;
            }
        }
        unset($recruit);
        unset($entrust);
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
        unset($recruit);
        unset($entrust);

        $data =[];
        foreach ($recruits as $recruit) {
            $_third_party = '';
            foreach ($recruit['entrusts'] as $entrust) {
                $_third_party.=$entrust['third_party']['company_alias'].',';
            }

            $_data = [];
            $_data[] = $recruit['job']['department']['full_name'];
            $_data[] = $recruit['company']['company_alias'];
            $_data[] = $recruit['leading']['realname'];
            $_data[] = $recruit['job']['code'];
            $_data[] = $recruit['job']['name'];
            $_data[] = $recruit['job']['address']['name'];
            $_data[] = '外包';
            $_necessary_skills = '';
            $has_skill = false;
            foreach ($recruit['job']['necessary_skills'] as $necessary_skill) {
                $has_skill = true;
                $_necessary_skills .= $necessary_skill['name'].'+'.$necessary_skill['skill_level_text'].';';
            }
            if($has_skill)
                $_necessary_skills = substr($_necessary_skills,0,strlen($_necessary_skills)-1);
            $_data[] = $recruit['need_num'];
            $_data[] = $_necessary_skills;

            $company_job_recruit_resume_ids = RecruitResume::where('company_job_recruit_id', $recruit['id'])->pluck('id')->toArray();
            $_data[] = $this->getRecruitCountByStatus([1], $company_job_recruit_resume_ids);//推荐简历8
            $_data[] = $this->getRecruitCountByStatus([2,3,5], $company_job_recruit_resume_ids);//邀请面试9
            $_data[] = !$_data[9]?0:round($_data[10]/$_data[9]*100, 1);
            $_data[] = $this->getRecruitCountByStatus([4,-3], $company_job_recruit_resume_ids,2);//参加面试11
            $_data[] = $this->getRecruitCountByStatus([6], $company_job_recruit_resume_ids);//面试通过12

            $_data[] = !$_data[12]?0:round($_data[13]/$_data[12]*100, 1);//13
            $_data[] = $this->getRecruitCountByStatus([7], $company_job_recruit_resume_ids);//录用数14
            $_data[] = !$_data[9]?0:round($_data[15]/$_data[9]*100, 1);//15
            $data[] = $_data;
        }

        $title = [
            '部门',
            '需求方',
            '需求方负责人',
            '职位编号',
            '职位名称',
            '工作地点',
            '人力类型',
            '岗位招聘数量',
            '职位必要技能',
            '推荐简历数',
            '邀约面试数',
            '简历通过率',
            '参加面试人数',
            '面试通过人数',
            '面试通过率',
            '录用数',
            '录用成功率',
        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'数据分析', "数据分析");
    }

    public function DumpData3(Request $request)
    {
        set_time_limit(0);
        $company_id = $request->get('company_id');
        $model = new RecruitResume();
        if($company_id){
            $model = $model->where('company_id',$company_id);
        }

        $recruitResumes = $model->whereNotNull('company_job_recruit_entrust_id')->get();

        $recruitResumes->load('job');
        $recruitResumes->load('recruit');
        $recruitResumes->load('entrust');
        $recruitResumes->load('company');
        $recruitResumes->load('thirdParty');
        $recruitResumes->load('resume');

        $entrustRes = app()->build(EntrustsRepository::class);

        $job_ids = $recruitResumes->pluck('job_id')->toArray();
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($recruitResumes as &$recruitResume) {
            $recruitResume->full_job = $jobs[$recruitResume['job']['id']];
            $recruitResume->status_text = $entrustRes->getStatusTextByRecruitAndEntrust($recruitResume->recruit);

        }
        unset($recruitResume);
        $data =[];
        $i = 0;
        $skills = Skill::all()->keyBy('id')->toArray();

        foreach ($recruitResumes as $recruitResume) {
            $this->recruitResumesRepository->addFieldText($recruitResume);
            $_data = [];
            $_data[] = ++$i;
            $_data[] = $recruitResume['resume']['name'];
            $_data[] = '';
            $_data[] = $recruitResume->thirdParty->company_alias;
            $_data[] = $recruitResume->created_at;
            $_data[] = in_array($recruitResume->status,[-9,-5,-4,-3,-2,-1,8])?$recruitResume->updated_at:'';
            $_data[] = $recruitResume['full_job']['department']['full_name'];
            $_data[] = $recruitResume['full_job']['name'];
            $_data[] = '外包';
            $_data[] = $recruitResume['full_job']['code'];

            $_necessary_skills = '';
            $has_skill = false;
            foreach ($recruitResume['full_job']['necessary_skills'] as $necessary_skill) {
                $has_skill = true;
                $_necessary_skills .= $necessary_skill['name'].'+'.$necessary_skill['skill_level_text'].';';
            }
            if($has_skill)
                $_necessary_skills = substr($_necessary_skills,0,strlen($_necessary_skills)-1);
            $_data[] = $_necessary_skills;

            $_data[] = '';

            $_skills = '';
            $has_skill = false;
            foreach ($recruitResume->resume->skills as $skill) {
                getOptionsText($skill);
                $has_skill = true;
                $_skills .= $skills[$skill->skill_id]['name'].'+'.$skill['skill_level_text'].';';
            }
            if($has_skill)
                $_skills = substr($_skills,0,strlen($_skills)-1);
            $_data[] = $_skills;

            $_data[] = $recruitResume['resume']['start_work_at'];

            if(in_array($recruitResume['status'], [1,2,3,5])){
                $_data[] = '待定';
            }elseif(in_array($recruitResume['status'], [6,7,8])){
                $_data[] = '通过';
            }else{
                $_data[] = '结束';
            }
            if(in_array($recruitResume['status'], [1,2,3,5])){
                $_data[] = '待定';
            }elseif(in_array($recruitResume['status'], [-3,-4,4,6,7,8])){
                $_data[] = '是';
            }else{
                $_data[] = '否';
            }
            if(in_array($recruitResume['status'], [1,2,3,5])){
                $_data[] = '待定';
            }elseif(in_array($recruitResume['status'], [6,7,8])){
                $_data[] = '通过';
            }else{
                $_data[] = '不通过';
            }
            if(in_array($recruitResume['status'], [1,2,3,5,4,6])){
                $_data[] = '待定';
            }elseif(in_array($recruitResume['status'], [7,8])){
                $_data[] = '通过';
            }else{
                $_data[] = '不通过';
            }
            $_data[] = $recruitResume['status_str'];
            if(in_array($recruitResume['status'], [1,2,3,5,4,6,7])){
                $_data[] = 'open';
            }else{
                $_data[] = 'close';
            }
            $_data[] = $recruitResume['formal_entry_at'];
//            $company_job_recruit_resume_ids = RecruitResume::where('company_job_recruit_id', $recruit['id'])->pluck('id')->toArray();
//            $_data[] = $this->getRecruitCountByStatus([1], $company_job_recruit_resume_ids);//推荐简历8
//            $_data[] = $this->getRecruitCountByStatus([2,3,5], $company_job_recruit_resume_ids);//邀请面试9
//            $_data[] = !$_data[8]?0:round($_data[9]/$_data[8]*100, 1);
//            $_data[] = $this->getRecruitCountByStatus([4,-3], $company_job_recruit_resume_ids,2);//参加面试11
//            $_data[] = $this->getRecruitCountByStatus([6], $company_job_recruit_resume_ids);//面试通过12
//
//            $_data[] = !$_data[11]?0:round($_data[12]/$_data[11]*100, 1);//13
//            $_data[] = $this->getRecruitCountByStatus([7], $company_job_recruit_resume_ids);//录用数14
//            $_data[] = !$_data[8]?0:round($_data[14]/$_data[8]*100, 1);//15
            $data[] = $_data;
        }

        $title = [
            '序号',
            '姓名',
            '英文姓名',
            '外包公司',
            '推荐日期',
            '结束日期',
            '部门',
            '职位名称',
            '人力类型',
            '职位编号',
            '主要技能',
            '现在职位',
            '主要技能背景',
            '工作年限',
            '简历通过',
            '是否参加面试',
            '面试情况',
            'offer',
            '结果',
            '状态',
            '入职时间',
        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'应聘列表', "应聘列表");
    }

    public function DumpData4(Request $request)
    {
        set_time_limit(0);
        $company_id = $request->get('company_id');
        $model = new Job();
        if($company_id){
            $model = $model->where('company_id',$company_id);
        }

        Recruit::where('wait_entry_num', '<' ,0)->update(['wait_entry_num'=>0]);

        $depIds = $model->pluck('department_id')->unique();

        $departments = CompanyDepartment::all()->keyBy('id')->toArray();
        $company_job_recruit_ids = Entrust::whereNotIn('status', [-2,-3,0])->pluck('company_job_recruit_id')->toArray();

        $data =[];
        foreach ($depIds as $depId) {
            $_data = [];
            $department = CompanyDepartment::find($depId);
            if($department){
                if($department->pid && isset($departments[$department->pid])){
                    $department->full_name = $departments[$department->pid]['name'].'-'.$department->name;
                }else{
                    $department->full_name = $department->name;
                }
            }else{
                continue;
            }
            $company_job_ids = Job::where('department_id', $depId)->pluck('id');
            $need_count = (int)Recruit::whereIn('id', $company_job_recruit_ids)->whereIn('job_id', $company_job_ids)->sum('need_num');
            $_recruit_ids = Recruit::whereIn('id', $company_job_recruit_ids)->whereIn('job_id', $company_job_ids)->pluck('id');
            if($need_count<1)
                continue;

            $_drz_count = (int)Recruit::whereIn('id', $company_job_recruit_ids)->whereIn('job_id', $company_job_ids)->sum('wait_entry_num');

            $_dg_num = RecruitResume::whereIn('status',[8])->whereIn('company_job_recruit_id', $_recruit_ids)->count();
            $_data[] = $department->full_name;
            $_data[] = 0;
            $_data[] = $need_count;
            $_data[] = $need_count;

            $_data[] = 0;
            $_data[] = $_dg_num;
            $_data[] = $_dg_num;

            $_j = $need_count - $_dg_num;
            $_data[] = 0;
            $_data[] = $_j<0?0:$_j;
            $_data[] = $_j<0?0:$_j;

            $_data[] = 0;
            $_data[] = $_drz_count;
            $_data[] = $_drz_count;

            $data[] = $_data;
        }

        $title = [
            '部门',
            '猎头',
            '外包',
            '总计',
            '猎头',
            '外包',
            '总计',
            '猎头',
            '外包',
            '总计',
            '猎头',
            '外包',
            '总计',
        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'需求汇总', "需求汇总");
    }

    public function DumpData5(Request $request)
    {
        set_time_limit(0);
        $company_id = $request->get('company_id');
        $model = new Recruit();
        if($company_id){
            $model = $model->where('company_id',$company_id);
        }

        Recruit::where('wait_entry_num', '<' ,0)->update(['wait_entry_num'=>0]);

        $departments = CompanyDepartment::all()->keyBy('id')->toArray();
        $company_job_recruit_ids = Entrust::whereNotIn('status', [-2,-3,0])->pluck('company_job_recruit_id')->toArray();

        $recruits = $model->whereIn('id', $company_job_recruit_ids)->get();
        $data =[];
        $recruits->load('job');
        $recruits->load('leading');
        foreach ($recruits as $recruit) {
            $_data = [];
            $job = $recruit->job;
            $department = CompanyDepartment::find($job->department_id);
            if($department){
                if($department->pid && isset($departments[$department->pid])){
                    $department->full_name = $departments[$department->pid]['name'].'-'.$department->name;
                }else{
                    $department->full_name = $department->name;
                }
            }
            $_data[] = $department?$department->full_name:'';
            $_data[] = $recruit->leading->realname;
            $_data[] = $recruit->job->name;

            $_data[] = 0;
            $_data[] = $recruit->need_num;
            $_data[] = $recruit->need_num;

            $_data[] = 0;
            $_data[] = $recruit->done_num;
            $_data[] = $recruit->done_num;

            $_j = $recruit->need_num - $recruit->done_num;
            $_data[] = 0;
            $_data[] = $_j<0?0:$_j;
            $_data[] = $_j<0?0:$_j;

            $_data[] = 0;
            $_data[] = $recruit->wait_entry_num;
            $_data[] = $recruit->wait_entry_num;

            $data[] = $_data;
        }

        $title = [
            '部门',
            '负责人',
            '职位名称',
            '猎头',
            '外包',
            '总计',
            '猎头',
            '外包',
            '总计',
            '猎头',
            '外包',
            '总计',
            '猎头',
            '外包',
            '总计',
        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'数据仪表', "数据仪表");
    }

    public function DumpData6(Request $request)
    {
        set_time_limit(0);
        $company_id = $request->get('company_id');
        $model = new Company();
        if($company_id){
            $model = $model->where('id',$company_id);
        }

        $companies = $model->get();

        $data =[];
        foreach ($companies as $company) {
            $thirdParty = $company->thirdParty;
            foreach ($thirdParty as $v) {
                $company_job_recruit_resume_ids = RecruitResume::where('third_party_id', $v->id)->where('company_id', $company->id)->pluck('id')->toArray();
                $_data = [];
                $_data[] = $v->company_alias;
                $_data[] = $company->company_alias;
                $_data[] = $this->getEntrustCountByStatus([1], $company_job_recruit_resume_ids);//推荐简历;
                $_data[] = $this->getEntrustCountByStatus([2,3,5], $company_job_recruit_resume_ids);//邀请面试
                $_data[] = $this->getEntrustCountByStatus([-2], $company_job_recruit_resume_ids);//放弃面试
                $_data[] = $this->getEntrustCountByStatus([6], $company_job_recruit_resume_ids);//面试通过
                $_data[] = $this->getEntrustCountByStatus([8], $company_job_recruit_resume_ids);//
                $data[] = $_data;
            }
        }

        $title = [
            '供应商',
            '一级需求方',
            '推荐简历数',
            '邀约面试数',
            '面试未来',
            '面试通过人数',
            '入职到岗',
        ];

        $excelHelper = new ExcelHelper();

        $excelHelper->dumpExcel($title,$data,'下包商数据统计表', "下包商数据统计表");
    }

    /*
     * type
     *
     * 1: 正常状态统计
     * 2: 判断最后状态是不是已经变了
     */
    protected function getRecruitCountByStatus($status, $company_job_recruit_resume_ids, $type=1)
    {
        if($type==1){
            $data = RecruitResumeLog::whereIn('status',$status)
                ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
                ->groupBy('company_job_recruit_resume_id')->orderBy('id','desc')->get();
            $count = $data->count();
        }else{
            $count = 0;
            $all = RecruitResumeLog::whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)->orderBy('id','asc')->get();
            $lastData = [];
            foreach ($all as $log) {
                $recruitResumeId = $log->company_job_recruit_resume_id;
                if(!isset($lastData[$recruitResumeId])){
                    $lastData[$recruitResumeId] = [
                        'lastStatus'=> $log->statsu,
                        'lastAt'=> $log->created_at,
                    ];
                }else{
                    if(strtotime($log->created_at)>strtotime($lastData[$recruitResumeId]['lastAt'])){
                        $lastData[$recruitResumeId] = [
                            'lastStatus'=> $log->statsu,
                            'lastAt'=> $log->created_at,
                        ];
                    }
                }
            }

            $data = RecruitResumeLog::whereIn('status',$status)
                ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
                ->groupBy('company_job_recruit_resume_id')->get();
            foreach ($data as $v) {
                $last = $lastData[$v->company_job_recruit_resume_id];
                if($last['lastStatus']==$v->status)
                    $count++;
            }
        }
        return $count;
    }

    /*
     * type
     *
     * 1: 正常状态统计
     * 2: 判断最后状态是不是已经变了
     */
    protected function getEntrustCountByStatus($status, $company_job_recruit_resume_ids, $type=1)
    {
        if($type==1){
            $data = RecruitResumeLog::whereIn('status',$status)
                ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
                ->groupBy('company_job_recruit_resume_id')->orderBy('id','desc')->get();
            $count = $data->count();
        }else{
            $count = 0;
            $all = RecruitResumeLog::whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)->orderBy('id','asc')->get();
            $lastData = [];
            foreach ($all as $log) {
                $recruitResumeId = $log->company_job_recruit_resume_id;
                if(!isset($lastData[$recruitResumeId])){
                    $lastData[$recruitResumeId] = [
                        'lastStatus'=> $log->statsu,
                        'lastAt'=> $log->created_at,
                    ];
                }else{
                    if(strtotime($log->created_at)>strtotime($lastData[$recruitResumeId]['lastAt'])){
                        $lastData[$recruitResumeId] = [
                            'lastStatus'=> $log->statsu,
                            'lastAt'=> $log->created_at,
                        ];
                    }
                }
            }

            $data = RecruitResumeLog::whereIn('status',$status)
                ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
                ->groupBy('company_job_recruit_resume_id')->get();
            foreach ($data as $v) {
                $last = $lastData[$v->company_job_recruit_resume_id];
                if($last['lastStatus']==$v->status)
                    $count++;
            }
        }
        return $count;
    }
}

