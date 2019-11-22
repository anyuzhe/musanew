<?php

namespace App\Console\Commands;

use App\Mail\RecruitResumeUntreatedEmail;
use App\Models\RecruitResumeLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email';

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

        //简历24小时没有操作提示
        $recruitResumeLogs = RecruitResumeLog::where('status',1)->get();
        $recruitResumeIds = $recruitResumeLogs->pluck('company_job_recruit_resume_id')->toArray();

        $recruitResumeHasIds = RecruitResumeLog::where('status','!=',1)->whereIn('company_job_recruit_resume_id', $recruitResumeIds)
            ->pluck('company_job_recruit_resume_id')->toArray();

//            $recruitResumeLogs=RecruitResumeLog::all();
//            $recruitResumeHasIds=[];
        $recruits = [];
        $entrusts = [];
        foreach ($recruitResumeLogs as $recruitResumeLog) {
//            $recruitResumeLog->is_send_email = 1;
            $recruitResumeLog->save();
            if(in_array($recruitResumeLog->company_job_recruit_resume_id, $recruitResumeHasIds)!==false){
                continue;
            }
            $recruitResume = $recruitResumeLog->recruitResume;
            //给负责人发送邮件通知
            $recruit = $recruitResume->recruit;
            $entrust = $recruitResume->entrust;
            if(!$entrust){
                if($recruit->leading_id){
                    $leading = User::find($recruit->leading_id);
                }else{
                    $leading = null;
                }
                if($leading && $leading->email){
                    if(!isset($recruits[$recruit->id])){
                        $recruit->count = 0;
                        $recruit->resumes = [];
                        $recruits[$recruit->id] = $recruit;
                    }
                    $resume = $recruitResume->resume;
                    $resume->recruit_resume_id = $recruitResume->id;
                    $resumes = $recruits[$recruit->id]->resumes;
                    $resumes[] = $resume;
                    $recruits[$recruit->id]->resumes = $resumes;
                    $recruit->count++;
                }
            }else{
                if($entrust->leading_id){
                    $leading = User::find($entrust->leading_id);
                }else{
                    $leading = null;
                }
                if($leading && $leading->email){
                    if(!isset($entrust[$entrust->id])){
                        $entrust->count = 0;
                        $entrust->resumes = [];
                        $entrusts[$entrust->id] = $entrust;
                    }
                    $resume = $recruitResume->resume;
                    $resume->recruit_resume_id = $recruitResume->id;
                    $resumes = $entrusts[$entrust->id]->resumes;
                    $resumes[] = $resume;
                    $entrusts[$entrust->id]->resumes = $resumes;
                    $entrust->count++;
                }

            }
        }
        foreach ($recruits as $recruit) {
                Mail::to("68067348@qq.com")->send(new RecruitResumeUntreatedEmail($recruit, $recruit->resumes));
        }
        foreach ($entrusts as $entrust) {
                Mail::to("68067348@qq.com")->send(new RecruitResumeUntreatedEmail($entrust->recruit, $entrust->resumes));
        }
    }
}
