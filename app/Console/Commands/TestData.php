<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyPermission;
use App\Models\CompanyUser;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\ResumeSkill;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\CompaniesRepository;
use App\Repositories\CompanyLogRepository;
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
        }
    }
}
