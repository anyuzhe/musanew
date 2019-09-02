<?php

namespace App\Console\Commands;

use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitEndLog;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\RecruitResumeLook;
use App\Models\Resume;
use App\Models\ResumeCompany;
use App\Models\ResumeEducation;
use App\Models\ResumeProject;
use App\Models\ResumeSkill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clr';

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
        Entrust::truncate();
        Recruit::truncate();
        RecruitEndLog::truncate();
        RecruitResume::truncate();
        RecruitResumeLog::truncate();
        RecruitResumeLook::truncate();

        Resume::truncate();
        ResumeCompany::truncate();
        ResumeEducation::truncate();
        ResumeProject::truncate();
        ResumeSkill::truncate();
        CompanyResume::truncate();
    }
}
