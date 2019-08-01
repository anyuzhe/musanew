<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitEndLog;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;

class StatisticsRepository
{
    public function getCompanyCountStatistics(Company $company)
    {
        $companies = $company->thirdParty();

        $all_job_count = Recruit::where('company_id', $company->id)->count();
        $month_job_count = Recruit::where('company_id', $company->id)->whereIn('status',[1,2,3])->count();
        $month_people_count = (int)Recruit::where('company_id', $company->id)->whereIn('status',[1,2,3])->sum('need_num');

        $company_recruit_entrust_ids = Entrust::where('company_id', $company->id)->pluck('id')->toArray();
        $company_recruit_ids = RecruitResume::where('company_id', $company->id)->pluck('id')->toArray();

        $month_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $company_recruit_entrust_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=',date('Y-m-31 23:59:59'))->count();
        $all_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $company_recruit_entrust_ids)->count();

        $month_recommend_resume_succeed_count = RecruitResumeLog::where('status',6)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=',date('Y-m-31 23:59:59'))->count();
        $all_recommend_resume_succeed_count = RecruitResumeLog::where('status',6)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->count();

        $month_recommend_resume_entry_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=',date('Y-m-31 23:59:59'))->count();
        $all_recommend_resume_entry_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->count();
        return compact('all_job_count', 'month_job_count', 'month_people_count',
            'month_recommend_resume_count', 'all_recommend_resume_count','month_recommend_resume_succeed_count','all_recommend_resume_succeed_count',
            'month_recommend_resume_entry_count', 'all_recommend_resume_entry_count','companies');
    }

    public function getCompanyThirdPartyCountStatistics(Company $company)
    {
        $companies = $company->demandSides;

        $all_job_count = Entrust::where('third_party_id', $company->id)->count();
        $month_job_count = Entrust::where('third_party_id', $company->id)->whereIn('status',[0,1])->count();
        $month_people_count = (int)Recruit::whereIn('id',
            Entrust::where('third_party_id', $company->id)->whereIn('status',[0,1])->pluck('company_job_recruit_id')->toArray())->sum('need_num');

        $third_party_recruit_entrust_ids = Entrust::where('third_party_id', $company->id)->pluck('id')->toArray();
        $third_party_recruit_ids = RecruitResume::where('third_party_id', $company->id)->pluck('id')->toArray();
        $month_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $third_party_recruit_entrust_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=',date('Y-m-31 23:59:59'))->count();
        $all_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $third_party_recruit_entrust_ids)->count();

        $month_recommend_resume_succeed_count = RecruitResumeLog::where('status',6)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=',date('Y-m-31 23:59:59'))->count();
        $all_recommend_resume_succeed_count = RecruitResumeLog::where('status',6)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->count();

        $month_recommend_resume_entry_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=',date('Y-m-31 23:59:59'))->count();
        $all_recommend_resume_entry_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->count();
        return compact('all_job_count', 'month_job_count', 'month_people_count',
            'month_recommend_resume_count', 'all_recommend_resume_count','month_recommend_resume_succeed_count','all_recommend_resume_succeed_count',
            'month_recommend_resume_entry_count', 'all_recommend_resume_entry_count','companies');
    }

    public function getCompanyDataStatistics(Company $company,$start_date,$end_date)
    {
        $company_job_recruit_resume_ids = RecruitResume::where('company_id', $company->id)->pluck('id')->toArray();
        //“推荐简历”、“邀请面试”、“面试中”、“录用”、“入职”
        $companies = Company::all()->keyBy('id')->toArray();

        //推荐简历
        $recommend_resume = $this->getCountByStatus([1], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //邀请面试
        $invite_interview = $this->getCountByStatus([2], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //面试中
        $interviewing = $this->getCountByStatus([2,3,4,5], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //录用
        $hire = $this->getCountByStatus([6], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //入职
        $entry = $this->getCountByStatus([7], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);

        return compact('recommend_resume', 'invite_interview', 'interviewing', 'hire', 'entry');
    }

    public function getCompanyThirdPartyDataStatistics(Company $company,$start_date,$end_date)
    {
        $company_job_recruit_resume_ids = RecruitResume::where('third_party_id', $company->id)->pluck('id')->toArray();
        //“推荐简历”、“邀请面试”、“面试中”、“录用”、“入职”
        $companies = Company::all()->keyBy('id')->toArray();
        $thirdParties = $company->thirdParty();

        //推荐简历
        $recommend_resume = $this->getCountByStatus([1], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //邀请面试
        $invite_interview = $this->getCountByStatus([2], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //面试中
        $interviewing = $this->getCountByStatus([2,3,4,5], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //录用
        $hire = $this->getCountByStatus([6], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //入职
        $entry = $this->getCountByStatus([7], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);

        return compact('recommend_resume', 'invite_interview', 'interviewing', 'hire', 'entry', 'thirdParties');
    }

    public function getCompanyDataStatisticsDetail(Company $company, $third_party_id, $start_date, $end_date)
    {
        $entrustLogs = RecruitEndLog::where('third_party_id', $third_party_id)->where('company_id', $company->id)
            ->where(function ($quesy)use($start_date,$end_date){
                $quesy->where(function ($query1)use($start_date,$end_date){
                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
                })->orWhere(function ($query2)use($start_date,$end_date){
                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
                });
            })->get();
        $entrustLogsIds = $entrustLogs->pluck('company_job_recruit_entrust_id')->toArray();
//        dump($start_date);
//        dump($end_date);
//        dump($entrustLogs->toArray());
        $entrusts = Entrust::whereNotIn('id', $entrustLogsIds)->where('third_party_id', $third_party_id)->where('company_id', $company->id)
            ->where(function ($quesy)use($start_date,$end_date){
                $quesy->where(function ($query1)use($start_date,$end_date){
                    $query1->where('created_at','>=',$start_date)->where('created_at','<=',$end_date);
                })->orWhere(function ($query2)use($start_date,$end_date){
                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
                });
            })->whereIn('status', [1])->get();
//        dump($entrusts->toArray());
//        $departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        $departments = CompanyDepartment::where('company_id', $company->id)->get()->keyBy('id')->toArray();
        $entrusts->load('job');
        $entrusts->load('recruit');
        $has_entrust_ids = [];
        foreach ($entrusts as $entrust) {
            $job = $entrust->job;
            if($job->department_id && isset($departments[$job->department_id]) && !in_array($entrust->id, $has_entrust_ids)){
                $recruit = $entrust->recruit;
                $company_job_recruit_resume_ids = RecruitResume::where('company_job_recruit_entrust_id', $entrust->id)->pluck('id')->toArray();
                $_data = [
                    'job_name'=>$job->name,
                    'recruit_days'=>getDays(strtotime($entrust->created_at)),
                    'done_rate'=>(int)($entrust->done_num/$recruit->need_num*100),
                    'need_num'=>$recruit->need_num,
                    'entry_success_num'=>$this->getEntrustCountByStatus([7], $company_job_recruit_resume_ids),
                    'wait_entry_num'=>$this->getEntrustCountByStatus([6], $company_job_recruit_resume_ids),
                    'residue_num'=>$recruit->need_num - $recruit->done_num,
                    'recommend_resume_num'=>$this->getEntrustCountByStatus([1], $company_job_recruit_resume_ids),
                    'interview_resume_num'=>$this->getEntrustCountByStatus([2], $company_job_recruit_resume_ids),
                ];
                if(!isset($departments[$job->department_id]['data'])){
                    $departments[$job->department_id]['data'] = [];
                }
                $departments[$job->department_id]['data'][] = $_data;
                $has_entrust_ids[] = $entrust->id;
            }
        }
        $data = [];
        foreach ($departments as $department) {
            if($department['level']==1){
                $department['child'] = [];
                foreach ($departments as $v) {
                    if($v['pid']==$department['id']){
                        if(isset($v['data']))
                            $department['child'][] = $v;
                    }
                }
                if(count($department['child'])==0 && isset($department['data'])){
                    $department['child'] = $department;
                }
                if(count($department['child'])>0)
                    $data[] = $department;
            }
        }
        return ['departments'=>$data];
    }


    protected function getCountByStatus($status, $companies, $company_job_recruit_resume_ids, $start_date, $end_date)
    {
        $data = [
            'num'=>0,
            'data'=>[]
        ];

        $_recruitResumes = RecruitResumeLog::whereIn('status',$status)->where('created_at','>',$start_date)->where('created_at','<=',$end_date)
            ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
            ->groupBy('company_job_recruit_resume_id')->get();
        $_recruitResumes->load('recruitResume');
        $_data = [];
        foreach ($_recruitResumes as $recruitResume) {
            $data['num']++;
            $third_party_id = $recruitResume->recruitResume->third_party_id;
            if(isset($_data[$third_party_id])){
                $_data[$third_party_id]['num']++;
            }else{
                $_data[$third_party_id]=[
                    'num'=>1,
                    'name'=>$companies[$third_party_id]['company_alias']
                ];
            }
        }
        $data['data'] = array_values($_data);
        return $data;
    }

    protected function getEntrustCountByStatus($status, $company_job_recruit_resume_ids)
    {
        $count = RecruitResumeLog::whereIn('status',$status)
            ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
            ->groupBy('company_job_recruit_resume_id')->get()->count();
        return $count;
    }
}
