<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;

class StatisticsRepository
{
    public function getCompanyDataStatistics(Company $company)
    {
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
            'month_recommend_resume_entry_count', 'all_recommend_resume_entry_count');
    }

    public function getCompanyThirdPartyDataStatistics(Company $company)
    {
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
            'month_recommend_resume_entry_count', 'all_recommend_resume_entry_count');
    }
}
