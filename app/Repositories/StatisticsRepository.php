<?php

namespace App\Repositories;

use App\Models\Company;
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
        dump($entrustLogs->toArray());
        $entrusts = Entrust::whereNotIn('id', $entrustLogsIds)->where('third_party_id', $third_party_id)->where('company_id', $company->id)
            ->where('created_at','>=',$start_date)->where('created_at','<=',$end_date)->get();
        dd($entrusts->toArray());
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
}
