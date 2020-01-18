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
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-31 23:59:59');

        $companies = $company->thirdParty;

//        $endRecruitIds = RecruitEndLog::where('company_id', $company->id)->whereNotNull('third_party_id')
//            ->where(function ($quesy)use($start_date,$end_date){
//                $quesy->where(function ($query1)use($start_date,$end_date){
//                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
//                })->orWhere(function ($query2)use($start_date,$end_date){
//                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
//                });
//            })->pluck('company_job_recruit_id')->toArray();

        $company_recruit_entrust_ids = Entrust::where('company_id', $company->id)->whereNotIn('status', [-2,-3,0])->pluck('id')->toArray();
        $company_job_recruit_ids = Entrust::where('company_id', $company->id)->whereNotIn('status', [-2,-3,0])->pluck('company_job_recruit_id')->toArray();
        $company_recruit_ids = RecruitResume::where('company_id', $company->id)->whereNotNull('third_party_id')->pluck('id')->toArray();

        $all_job_count = Recruit::whereIn('id', $company_job_recruit_ids)->count();
        $month_job_count = Recruit::whereIn('id', $company_job_recruit_ids)->where(function ($query) use ($start_date, $end_date) {
            $query->whereIn('status',[1,2,3,6,7])->orWhere(function ($q) use ($end_date, $start_date) {
                $q->where('end_at','>', $start_date)->where('end_at','<=', $end_date);
            });
        })->count();
//        $month_job_count = Recruit::whereIn('id', $company_job_recruit_ids)->whereIn('status',[1,2,3])->orWhereIn('id',$endRecruitIds)->count();
        $month_people_count = (int)Recruit::whereIn('id', $company_job_recruit_ids)->where(function ($query) use ($start_date, $end_date) {
            $query->whereIn('status',[1,2,3,6,7])->orWhere(function ($q) use ($end_date, $start_date) {
                $q->where('end_at','>', $start_date)->where('end_at','<=', $end_date);
            });
        })->sum('need_num');
//        $month_people_count = (int)Recruit::whereIn('id', $company_job_recruit_ids)->whereIn('status',[1,2,3])->orWhereIn('id',$endRecruitIds)->sum('need_num');

        $month_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $company_recruit_entrust_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=', $end_date)->count();
        $all_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $company_recruit_entrust_ids)->count();

        $month_recommend_resume_succeed_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=', $end_date)->count();
        $all_recommend_resume_succeed_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->count();

        $month_recommend_resume_entry_count = RecruitResumeLog::where('status',8)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->where('created_at','>',date('Y-m-01'))->where('created_at','<=', $end_date)->count();
        $all_recommend_resume_entry_count = RecruitResumeLog::where('status',8)->whereIn('company_job_recruit_resume_id', $company_recruit_ids)
            ->count();
        return compact('all_job_count', 'month_job_count', 'month_people_count',
            'month_recommend_resume_count', 'all_recommend_resume_count','month_recommend_resume_succeed_count','all_recommend_resume_succeed_count',
            'month_recommend_resume_entry_count', 'all_recommend_resume_entry_count','companies');
    }

    public function getCompanyThirdPartyCountStatistics(Company $company)
    {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-31 23:59:59');

        $companies = $company->demandSides;

//        $endRecruitIds = RecruitEndLog::where('third_party_id', $company->id)
//            ->where(function ($quesy)use($start_date,$end_date){
//                $quesy->where(function ($query1)use($start_date,$end_date){
//                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
//                })->orWhere(function ($query2)use($start_date,$end_date){
//                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
//                });
//            })->pluck('company_job_recruit_id')->toArray();

        $allRecruitIds = Entrust::where('third_party_id', $company->id)->whereNotIn('status', [-2,-3,0])->pluck('company_job_recruit_id')->toArray();

        $all_job_count = Entrust::where('third_party_id', $company->id)->whereNotIn('status', [-2,-3,0])->count();

        $month_job_count = Recruit::whereIn('id', $allRecruitIds)->where(function ($query) use ($start_date, $end_date) {
            $query->whereIn('status',[1,2,3,6,7])->orWhere(function ($q) use ($end_date, $start_date) {
                $q->where('end_at','>', $start_date)->where('end_at','<=', $end_date);
            });
        })->count();
//        $month_job_count = Recruit::whereIn('id', $allRecruitIds)->whereIn('status',[1,2,3])->orWhereIn('id',$endRecruitIds)->count();
//        $month_job_count = Entrust::where('third_party_id', $company->id)->whereIn('status',[0,1])->count();
        $month_people_count = (int)Recruit::whereIn('id', $allRecruitIds)->where(function ($query) use ($start_date, $end_date) {
            $query->whereIn('status',[1,2,3,6,7])->orWhere(function ($q) use ($end_date, $start_date) {
                $q->where('end_at','>', $start_date)->where('end_at','<=', $end_date);
            });
        })->sum('need_num');
//        $month_people_count = (int)Recruit::whereIn('id', $allRecruitIds)->whereIn('status',[1,2,3])->orWhereIn('id',$endRecruitIds)->sum('need_num');

        $third_party_recruit_entrust_ids = Entrust::where('third_party_id', $company->id)->pluck('id')->toArray();
        $third_party_recruit_ids = RecruitResume::where('third_party_id', $company->id)->pluck('id')->toArray();
        $month_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $third_party_recruit_entrust_ids)
            ->where('created_at','>', $start_date)->where('created_at','<=', $end_date)->count();
        $all_recommend_resume_count = RecruitResume::whereIn('company_job_recruit_entrust_id', $third_party_recruit_entrust_ids)->count();

        $month_recommend_resume_succeed_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->where('created_at','>', $start_date)->where('created_at','<=', $end_date)->count();
        $all_recommend_resume_succeed_count = RecruitResumeLog::where('status',7)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->count();

        $month_recommend_resume_entry_count = RecruitResumeLog::where('status',8)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->where('created_at','>', $start_date)->where('created_at','<=', $end_date)->count();
        $all_recommend_resume_entry_count = RecruitResumeLog::where('status',8)->whereIn('company_job_recruit_resume_id', $third_party_recruit_ids)
            ->count();
        return compact('all_job_count', 'month_job_count', 'month_people_count',
            'month_recommend_resume_count', 'all_recommend_resume_count','month_recommend_resume_succeed_count','all_recommend_resume_succeed_count',
            'month_recommend_resume_entry_count', 'all_recommend_resume_entry_count','companies');
    }

    public function getCompanyDataStatistics(Company $company,$start_date,$end_date)
    {
        $company_job_recruit_resume_ids = RecruitResume::where('company_id', $company->id)->whereNotNull('third_party_id')->pluck('id')->toArray();
        //“推荐简历”、“邀请面试”、“面试中”、“录用”、“入职”
        $companies = Company::all()->keyBy('id')->toArray();

        //招聘职位数量
//        $entrustLogs = RecruitEndLog::where('company_id', $company->id)->whereNotNull('third_party_id')
//            ->where(function ($quesy)use($start_date,$end_date){
//                $quesy->where(function ($query1)use($start_date,$end_date){
//                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
//                })->orWhere(function ($query2)use($start_date,$end_date){
//                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
//                });
//            })->get();
//        $entrustLogsIds = $entrustLogs->pluck('company_job_recruit_entrust_id')->toArray();
        $recruitLogIds = Entrust::where(function ($query)use($company,$start_date,$end_date){
            $query->where('company_id', $company->id)->whereIn('status',[1])->where('created_at','>',$start_date)->where('created_at','<=',$end_date);
        })->pluck('company_job_recruit_id')->toArray();
        $recruit_num = Recruit::whereIn('id', $recruitLogIds)->count();
        //招聘职位人数
        $recruit_people_num = (int)(Recruit::whereIn('id', $recruitLogIds)->sum('need_num'));

        //推荐简历
        $recommend_resume = $this->getCountByStatus([1], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //邀请面试
        $invite_interview = $this->getCountByStatus([2], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //面试中
        $interviewing = $this->getCountByStatus([2,3,4,5,6], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //录用
        $hire = $this->getCountByStatus([7], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);
        //入职
        $entry = $this->getCountByStatus([8], $companies, $company_job_recruit_resume_ids, $start_date, $end_date);

        return compact('recommend_resume', 'invite_interview', 'interviewing', 'hire', 'entry','recruit_num','recruit_people_num');
    }

    public function getCompanyThirdPartyDataStatistics(Company $company,$start_date,$end_date)
    {
        $company_job_recruit_resume_ids = RecruitResume::where('third_party_id', $company->id)->pluck('id')->toArray();
        //“推荐简历”、“邀请面试”、“面试中”、“录用”、“入职”
        $companies = Company::all()->keyBy('id')->toArray();
        $thirdParties = $company->thirdParty;

        //招聘职位数量
//        $entrustLogs = RecruitEndLog::where('third_party_id', $company->id)
//            ->where(function ($quesy)use($start_date,$end_date){
//                $quesy->where(function ($query1)use($start_date,$end_date){
//                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
//                })->orWhere(function ($query2)use($start_date,$end_date){
//                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
//                });
//            })->get();
//        $entrustLogsIds = $entrustLogs->pluck('company_job_recruit_entrust_id')->toArray();
        $recruitLogIds = Entrust::where(function ($query)use($company,$start_date,$end_date){
            $query->where('third_party_id', $company->id)->whereIn('status',[1])->where('created_at','>',$start_date)->where('created_at','<=',$end_date);
        })->pluck('company_job_recruit_id')->toArray();
        $recruit_num = Recruit::whereIn('id', $recruitLogIds)->count();
        //招聘职位人数
        $recruit_people_num = (int)(Recruit::whereIn('id', $recruitLogIds)->sum('need_num'));

        //推荐简历
        $recommend_resume = $this->getCountByStatus([1], $companies, $company_job_recruit_resume_ids, $start_date, $end_date, 2);
        //邀请面试
        $invite_interview = $this->getCountByStatus([2], $companies, $company_job_recruit_resume_ids, $start_date, $end_date, 2);
        //面试中
        $interviewing = $this->getCountByStatus([2,3,4,5,6], $companies, $company_job_recruit_resume_ids, $start_date, $end_date, 2);
        //录用
        $hire = $this->getCountByStatus([7], $companies, $company_job_recruit_resume_ids, $start_date, $end_date, 2);
        //入职
        $entry = $this->getCountByStatus([8], $companies, $company_job_recruit_resume_ids, $start_date, $end_date, 2);

        return compact('recommend_resume', 'invite_interview', 'interviewing', 'hire', 'entry', 'thirdParties','recruit_num','recruit_people_num');
    }

    public function getCompanyDataStatisticsDetail(Company $company, $third_party_id, $start_date, $end_date)
    {
//        $entrustLogs = RecruitEndLog::where('third_party_id', $third_party_id)->where('company_id', $company->id)
//            ->where(function ($quesy)use($start_date,$end_date){
//                $quesy->where(function ($query1)use($start_date,$end_date){
//                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
//                })->orWhere(function ($query2)use($start_date,$end_date){
//                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
//                });
//            })->get();
//        $entrustLogsIds = $entrustLogs->pluck('company_job_recruit_entrust_id')->toArray();
//        dump($start_date);
//        dump($end_date);
//        dump($entrustLogs->toArray());
        $entrusts = Entrust::where(function ($q)use ($third_party_id, $company, $start_date, $end_date){
            $q->where('third_party_id', $third_party_id)->where('company_id', $company->id)
                ->where(function ($quesy)use($start_date,$end_date){
                    $quesy->where(function ($query1)use($start_date,$end_date){
                        $query1->where('created_at','>=',$start_date)->where('created_at','<=',$end_date);
                    })->orWhere(function ($query2)use($start_date,$end_date){
                        $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
                    });
                })->whereIn('status', [-1,1,2,6]);
        })->get();
//        dump($entrusts->toArray());
//        $departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        $departments = CompanyDepartment::where('company_id', $company->id)->get()->keyBy('id')->toArray();
        $entrusts->load('job');
        $entrusts->load('recruit');
        $has_entrust_ids = [];
        $all_resume_num = 0;
        foreach ($entrusts as $entrust) {
            $job = $entrust->job;
            if($job->department_id && isset($departments[$job->department_id]) && !in_array($entrust->id, $has_entrust_ids)){
                $recruit = $entrust->recruit;
                $company_job_recruit_resume_ids = RecruitResume::where('company_job_recruit_entrust_id', $entrust->id)->pluck('id')->toArray();
                $_data = [
                    'job_name'=>$job->name,//职位
                    'publish_at'=>$entrust->created_at->toDateTimeString(),//招聘发布时间
                    'recruit_days'=>getDays(strtotime($entrust->created_at)),//招聘天数
                    'done_rate'=>(int)($entrust->done_num/$recruit->need_num*100),//完成率
                    'need_num'=>$recruit->need_num,//需求量
                    'entry_success_num'=>$this->getEntrustCountByStatus([8], $company_job_recruit_resume_ids),//入职成功
                    'wait_entry_num'=>$this->getEntrustCountByStatus([7], $company_job_recruit_resume_ids,2),//等待入职
                    'residue_num'=>$recruit->need_num - $recruit->done_num,//剩余量
                    'recommend_resume_num'=>$this->getEntrustCountByStatus([1], $company_job_recruit_resume_ids),//推荐简历
                    'value'=>$this->getEntrustCountByStatus([1], $company_job_recruit_resume_ids),//推荐简历
                    'interview_resume_num'=>$this->getEntrustCountByStatus([2,3,5], $company_job_recruit_resume_ids),//邀请面试
                    'resume_mismatching_num'=>$this->getEntrustCountByStatus([-1], $company_job_recruit_resume_ids),//简历不匹配
                    'give_up_interview_num'=>$this->getEntrustCountByStatus([-3], $company_job_recruit_resume_ids),//放弃面试
                    'undetermined_num'=>$this->getEntrustCountByStatus([4], $company_job_recruit_resume_ids,2),//待定
                    'interview_pass_num'=>$this->getEntrustCountByStatus([6], $company_job_recruit_resume_ids),//面试通过
                    'interview_defeated_num'=>$this->getEntrustCountByStatus([-3], $company_job_recruit_resume_ids),//面试失败
                    'interview_pass_inappropriate_num'=>$this->getEntrustCountByStatus([-4], $company_job_recruit_resume_ids),//面试通过不合适
                    'hire_num'=>$this->getEntrustCountByStatus([7], $company_job_recruit_resume_ids),//录用
                ];

                $all_resume_num += $_data['recommend_resume_num'];

                if(!isset($departments[$job->department_id]['data'])){
                    $departments[$job->department_id]['data'] = [];
                }
                if($departments[$job->department_id]['level']==1){
                    $_data['department1_name'] = $departments[$job->department_id]['name'];
                    $_data['department2_name'] = $departments[$job->department_id]['name'];
                }elseif ($departments[$job->department_id]['level']==2){
                    $_data['department2_name'] = $departments[$job->department_id]['name'];
                }
                $departments[$job->department_id]['data'][] = $_data;
                $has_entrust_ids[] = $entrust->id;
            }
        }
        $data = [];
        foreach ($departments as $department) {
            if($department['level']==1){
                $department['child'] = [];
                $department['value'] = 0;
                if(isset($department['data']))
                    $department['value'] += count($department['data']);
                foreach ($departments as $v) {
                    if($v['pid']==$department['id']){
                        if(isset($v['data'])){
                            foreach ($v['data'] as &$vv) {
                                $vv['department1_name'] = $department['name'];
                            }
                            $v['value'] = count($v['data']);
                            $department['value']+= count($v['data']);
                            $department['child'][] = $v;
                        }
                    }
                }
//                if(count($department['child'])==0 && isset($department['data'])){
//                    $department['child'][] = $department;
//                    unset($department['data']);
//                }
//                if(count($department['child'])>0)
//                    $data[] = $department;

                if(count($department['child'])>0 || isset($department['data'])){
                    $data[] = $department;
                }
            }
        }
        return ['departments'=>$data,'all_resume_num'=>$all_resume_num];
    }

    public function getCompanyThirdPartyDataStatisticsDetail(Company $company, $demand_side_id, $start_date, $end_date)
    {
//        $entrustLogs = RecruitEndLog::where('third_party_id', $company->id)->where('company_id', $demand_side_id)
//            ->where(function ($quesy)use($start_date,$end_date){
//                $quesy->where(function ($query1)use($start_date,$end_date){
//                    $query1->where('start_at','>=',$start_date)->where('start_at','<=',$end_date);
//                })->orWhere(function ($query2)use($start_date,$end_date){
//                    $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
//                });
//            })->get();
//        $entrustLogsIds = $entrustLogs->pluck('company_job_recruit_entrust_id')->toArray();

        $entrusts = Entrust::where(function ($q)use ($demand_side_id, $company, $start_date, $end_date){
            $q->where('third_party_id', $company->id)->where('company_id', $demand_side_id)
                ->where(function ($quesy)use($start_date,$end_date){
                    $quesy->where(function ($query1)use($start_date,$end_date){
                        $query1->where('created_at','>=',$start_date)->where('created_at','<=',$end_date);
                    })->orWhere(function ($query2)use($start_date,$end_date){
                        $query2->where('end_at','>=',$start_date)->where('end_at','<=',$end_date);
                    });
                })->whereIn('status', [-1,1,2,6]);
        })->get();
//        dump($entrusts->toArray());
//        $departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        $departments = CompanyDepartment::where('company_id', $demand_side_id)->get()->keyBy('id')->toArray();
        $entrusts->load('job');
        $entrusts->load('recruit');
        $has_entrust_ids = [];
        $all_resume_num = 0;
        foreach ($entrusts as $entrust) {
            $job = $entrust->job;
            if($job->department_id && isset($departments[$job->department_id]) && !in_array($entrust->id, $has_entrust_ids)){
                $recruit = $entrust->recruit;
                $company_job_recruit_resume_ids = RecruitResume::where('company_job_recruit_entrust_id', $entrust->id)->pluck('id')->toArray();
                $_data = [
                    'job_name'=>$job->name,//职位
                    'publish_at'=>$entrust->created_at->toDateTimeString(),//招聘发布时间
                    'recruit_days'=>getDays(strtotime($entrust->created_at)),//招聘天数
                    'done_rate'=>(int)($entrust->done_num/$recruit->need_num*100),//完成率
                    'need_num'=>$recruit->need_num,//需求量
                    'entry_success_num'=>$this->getEntrustCountByStatus([8], $company_job_recruit_resume_ids),//入职成功
                    'wait_entry_num'=>$this->getEntrustCountByStatus([7], $company_job_recruit_resume_ids,2),//等待入职
                    'residue_num'=>$recruit->need_num - $recruit->done_num,//剩余量
                    'value'=>$this->getEntrustCountByStatus([1], $company_job_recruit_resume_ids),//推荐简历
                    'recommend_resume_num'=>$this->getEntrustCountByStatus([1], $company_job_recruit_resume_ids),//推荐简历
                    'interview_resume_num'=>$this->getEntrustCountByStatus([2,3,5], $company_job_recruit_resume_ids),//邀请面试
                    'resume_mismatching_num'=>$this->getEntrustCountByStatus([-1], $company_job_recruit_resume_ids),//简历不匹配
                    'give_up_interview_num'=>$this->getEntrustCountByStatus([-3], $company_job_recruit_resume_ids),//放弃面试
                    'undetermined_num'=>$this->getEntrustCountByStatus([4], $company_job_recruit_resume_ids,2),//待定
                    'interview_pass_num'=>$this->getEntrustCountByStatus([6], $company_job_recruit_resume_ids),//面试通过
                    'interview_defeated_num'=>$this->getEntrustCountByStatus([-3], $company_job_recruit_resume_ids),//面试失败
                    'interview_pass_inappropriate_num'=>$this->getEntrustCountByStatus([-4], $company_job_recruit_resume_ids),//面试通过不合适
                    'hire_num'=>$this->getEntrustCountByStatus([7], $company_job_recruit_resume_ids),//录用
                ];
                $all_resume_num += $_data['recommend_resume_num'];

                if($departments[$job->department_id]['level']==1){
                    $_data['department1_name'] = $departments[$job->department_id]['name'];
                    $_data['department2_name'] = $departments[$job->department_id]['name'];
                }elseif ($departments[$job->department_id]['level']==2){
                    $_data['department2_name'] = $departments[$job->department_id]['name'];
                }
                $departments[$job->department_id]['data'][] = $_data;
                $has_entrust_ids[] = $entrust->id;
            }
        }
        $data = [];
        foreach ($departments as $department) {
            if($department['level']==1){
                $department['child'] = [];
                $department['value'] = 0;
                if(isset($department['data']))
                    $department['value'] += count($department['data']);
                foreach ($departments as $v) {
                    if($v['pid']==$department['id']){
                        if(isset($v['data'])){
                            foreach ($v['data'] as &$vv) {
                                $vv['department1_name'] = $department['name'];
                            }
                            $v['value'] = count($v['data']);
                            $department['value'] += count($v['data']);
                            $department['child'][] = $v;
                        }
                    }
                }
//                if(count($department['child'])==0 && isset($department['data'])){
//                    $department['child'][] = $department;
//                    unset($department['data']);
//                }
//                if(count($department['child'])>0)
//                    $data[] = $department;

                if(count($department['child'])>0 || isset($department['data'])){
                    $data[] = $department;
                }
            }
        }
        return ['departments'=>$data,'all_resume_num'=>$all_resume_num];
    }

    public function getExcelData($data)
    {
        $companyArray = [];
        function getCompanyArray(&$companyArray,$data,$key){
            foreach ($data[$key]['data'] as $v) {
                if(!isset($companyArray[$v['id']])){
                    $companyArray[$v['id']] = [
                        'name'=>$v['name'],
                        'id'=>$v['id']
                    ];
                }
            }
        }
        function getRowData($data,$companyArray,&$row){
            $all = 0;
            foreach ($companyArray as $key=>$value) {
                foreach ($data as $v) {
                    if($value['id']==$v['id']){
                        $all += $v['value'];
                        $row[$key+1] = $v['value'];
                        continue 2;
                    }
                }
                $row[] = 0;
            }
            $row[] = $all;
        }
        getCompanyArray($companyArray,$data,'recommend_resume');
        getCompanyArray($companyArray,$data,'invite_interview');
        getCompanyArray($companyArray,$data,'interviewing');
        getCompanyArray($companyArray,$data,'hire');
        getCompanyArray($companyArray,$data,'entry');
        $title = [''];
        $companyArray = array_values($companyArray);
        foreach ($companyArray as $item) {
            $title[] = $item['name'];
        }
        $title[] = '总计';
        $recommend_resume = ['推荐简历'];
        getRowData($data['recommend_resume']['data'],$companyArray,$recommend_resume);
        $invite_interview = ['邀请面试'];
        getRowData($data['invite_interview']['data'],$companyArray,$invite_interview);
        $interviewing = ['面试中'];
        getRowData($data['interviewing']['data'],$companyArray,$interviewing);
        $hire = ['录用'];
        getRowData($data['hire']['data'],$companyArray,$hire);
        $entry = ['入职'];
        getRowData($data['entry']['data'],$companyArray,$entry);
        $excelData = [
            $recommend_resume,$invite_interview,$interviewing,$hire,$entry
        ];
        return ['title'=>$title,'data'=>$excelData];
    }

    public function getExcelDetailData($data)
    {
        $excelData = [];
        foreach ($data['departments'] as $department) {
            foreach ($department['child'] as $level2) {
                foreach ($level2['data'] as $v) {
                    $excelData[] = [
                        $v['department1_name'],
                        $v['department2_name'],
                        $v['job_name'],
                        $v['publish_at'],
                        $v['recruit_days'],
                        $v['need_num'],
                        $v['residue_num'],
                        $v['done_rate'],
                        $v['recommend_resume_num'],
                        $v['resume_mismatching_num'],
                        $v['interview_resume_num'],
                        $v['give_up_interview_num'],
                        $v['undetermined_num'],
                        $v['interview_pass_num'],
                        $v['interview_defeated_num'],
                        $v['interview_pass_inappropriate_num'],
                        $v['hire_num'],
                        $v['wait_entry_num'],
                        $v['entry_success_num'],
                    ];
                }
            }
        }
        $title = [
            '一级部门',
            '二级部门',
            '职位',
            '招聘发布时间',
            '招聘天数',
            '需求量',
            '剩余量',
            '完成率',
            '推荐简历',
            '简历不匹配',
            '邀请面试',
            '放弃面试',
            '待定',
            '面试通过',
            '面试失败',
            '面试通过不合适',
            '录用',
            '待入职',
            '成功入职',
        ];
        return ['title'=>$title,'data'=>$excelData];
    }

    protected function getCountByStatus($status, $companies, $company_job_recruit_resume_ids, $start_date, $end_date, $type=3)
    {
        $data = [
            'value'=>0,
            'data'=>[]
        ];

        $_recruitResumes = RecruitResumeLog::whereIn('status',$status)->where('created_at','>',$start_date)->where('created_at','<=',$end_date)
            ->whereIn('company_job_recruit_resume_id', $company_job_recruit_resume_ids)
            ->groupBy('company_job_recruit_resume_id')->get();
        $_recruitResumes->load('recruitResume');
        $_data = [];
        foreach ($_recruitResumes as $recruitResume) {
            $data['value']++;
            if($type==3)
                $third_party_id = $recruitResume->recruitResume->third_party_id;
            else
                $third_party_id = $recruitResume->recruitResume->company_id;
            if(isset($_data[$third_party_id])){
                $_data[$third_party_id]['value']++;
            }else{
                $_data[$third_party_id]=[
                    'value'=>1,
                    'name'=>$companies[$third_party_id]['company_alias'],
                    'id'=>$companies[$third_party_id]['id'],
                ];
            }
        }
        $data['data'] = array_values($_data);
        return $data;
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
