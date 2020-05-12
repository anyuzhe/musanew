<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyPermission;
use App\Models\CompanyResume;
use App\Models\CompanyResumeGradeSetting;
use App\Models\CompanyUser;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitEndLog;
use App\Models\RecruitLog;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\RecruitResumeLook;
use App\Models\ResumeSkill;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\CompaniesRepository;
use App\Repositories\CompanyLogRepository;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:data {type} {--check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        if($type==1){
            ## 把公司的人员 添加到别的公司
            $peopleIds =  CompanyUser::where('company_id', 15)->pluck('user_id')->toArray();
            $companies = Company::whereIn('id',[14,20200001,20200002])->get();
            foreach ($peopleIds as $peopleId) {
                foreach ($companies as $company) {
                    $department_id = null;
                    $roles =  [];
                    $user = User::find($peopleId);
                    if($user && CompanyUser::where('company_id', $company->id)->where('user_id',$user->id)->first()){
                    }else{
                        $user = app()->build(CompaniesRepository::class)->handleUser($company, $user->email, $roles, $department_id);
                    }
                }
            }
        }elseif ($type=2){
            ## 复制职位 到别的公司
            $jobs = Job::where('company_id', 14)->get();
            $companyResumeGradeSettings = CompanyResumeGradeSetting::where('company_id', 14)->get()->toArray();
            $companies = Company::whereIn('id',[20200001,20200002])->get();

            $gradeIdsChange = [];
            foreach ($companyResumeGradeSettings as $companyResumeGradeSetting) {
                foreach ($companies as $company) {
                    $companyResumeGradeSetting['company_id'] = $company->id;
                    $_id = CompanyResumeGradeSetting::create($companyResumeGradeSetting);
                    $gradeIdsChange[$companyResumeGradeSetting['id']][$company->id] = $_id;
                }
            }
            foreach ($jobs as $job) {
                foreach ($companies as $company) {
                    $newData = $job->toArray();
                    unset($newData['id']);
                    $newJob = new Job();
                    $newJob->fill($newData);
                    $newJob->company_id = $company->id;
                    if(isset($gradeIdsChange[$job->resume_grade_setting_id][$company->id])){
                        $newJob->resume_grade_setting_id = $gradeIdsChange[$job->resume_grade_setting_id][$company->id];
                    }
                    $newJob->save();
                    $id = $newJob->id;

                    $skills = app('db')->connection('musa')->table('job_skill')->where('job_id', $job->id)->get()->toArray();

                    if($skills){
                        foreach ($skills as $skill) {
                            $skill = json_decode(json_encode($skill,256),true);
                            $skill['job_id'] = $id;
                            unset($skill['id']);
                            app('db')->connection('musa')->table('job_skill')->insertGetId($skill);
                        }
                    }

                    if($company->id==20200001){
                        if($newJob->department_id==98){
                            $newJob->department_id = 131;
                            $this->changeRecruit($job, $newJob);
                        }
                        elseif($newJob->department_id==121){
                            $newJob->department_id = 133;
                            $this->changeRecruit($job, $newJob);
                        }
                        elseif($newJob->department_id==122){
                            $newJob->department_id = 134;
                            $this->changeRecruit($job, $newJob);
                        }
                        elseif($newJob->department_id==123){
                            $newJob->department_id = 135;
                            $this->changeRecruit($job, $newJob);
                        }
                        elseif($newJob->department_id==128){
                            $newJob->department_id = 136;
                            $this->changeRecruit($job, $newJob);
                        }
                    }elseif ($company->id==20200002){
                        if($newJob->department_id==105){
                            $newJob->department_id = 138;
                            $this->changeRecruit($job, $newJob);
                        }
                        elseif($newJob->department_id==106){
                            $newJob->department_id = 139;
                            $this->changeRecruit($job, $newJob);
                        }
                    }
                    $newJob->save();
                }
            }
            ## 迁移公司的招聘
        }
    }

    protected function changeRecruit($job, $newJob)
    {
        $oldRecruitIds = Recruit::where('job_id', $job->id)->pluck('id');
        $recruitResumes = RecruitResume::where('job_id', $job->id)->get();

        Recruit::where('job_id', $job->id)->update([
            'company_id'=>$newJob->company_id,
            'job_id'=>$newJob->id,
        ]);
        RecruitEndLog::where('job_id', $job->id)->update([
            'company_id'=>$newJob->company_id,
            'job_id'=>$newJob->id,
        ]);
        Entrust::where('job_id', $job->id)->update([
            'company_id'=>$newJob->company_id,
            'job_id'=>$newJob->id,
        ]);
        foreach ($recruitResumes as $recruitResume) {
            //往需求方添加人才库关联
            $_has = CompanyResume::where('company_id', $job->company_id)->where('resume_id', $recruitResume->resume_id)->where('type', 1)->first();
            if(!$_has){
                CompanyResume::create([
                    'company_id'=>$job->company_id,
                    'resume_id'=>$recruitResume->resume_id,
                    'type'=>1,
                    'source_type'=>1,
                    'source_recruit_id'=>$recruitResume->company_job_recruit_id,
                    'source_entrust_id'=>$recruitResume->company_job_recruit_entrust_id?$recruitResume->company_job_recruit_entrust_id:null,
                    'source_job_id'=>$job->id,
                    'source_company_id'=>$recruitResume->third_party_id?$recruitResume->third_party_id:null,
                    'creator_id'=>$recruitResume->creator_id,
                ]);
            }
        }
        RecruitResume::where('job_id', $job->id)->update([
            'company_id'=>$newJob->company_id,
            'job_id'=>$newJob->id,
        ]);
        RecruitResumeLog::where('job_id', $job->id)->update([
            'company_id'=>$newJob->company_id,
            'job_id'=>$newJob->id,
        ]);
        RecruitLog::whereIn('company_job_recruit_id', $oldRecruitIds)->update([
            'company_id'=>$newJob->id,
        ]);
    }
}
