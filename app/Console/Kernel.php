<?php

namespace App\Console;

use App\Console\Commands\ClearData;
use App\Mail\RecruitResumeLogEmail;
use App\Mail\RecruitResumeUntreatedEmail;
use App\Models\CompanyManagerLog;
use App\Models\RecruitResumeLog;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ClearData::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function () {
            //简历24小时没有操作提示
            $recruitResumeLogs = RecruitResumeLog::where('status',1)
            ->where('created_at','>=',date('Y-m-d H:i:s',time()-(3600*24+60)))
//                ->where('created_at','>=',date('Y-m-d H:i:s',time()-(3600*2400+60)))
                ->where('created_at','<=',date('Y-m-d H:i:s',time()-(3600*24)))->where('is_send_email',0)->get();
            $recruitResumeIds = $recruitResumeLogs->pluck('company_job_recruit_resume_id')->toArray();

            $recruitResumeHasIds = RecruitResumeLog::where('status','!=',1)->whereIn('company_job_recruit_resume_id', $recruitResumeIds)
                ->pluck('company_job_recruit_resume_id')->toArray();

//            $recruitResumeLogs=RecruitResumeLog::all();
//            $recruitResumeHasIds=[];
            $recruits = [];
            $entrusts = [];
            foreach ($recruitResumeLogs as $recruitResumeLog) {
                $recruitResumeLog->is_send_email = 1;
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
//                Mail::to("68067348@qq.com")->send(new RecruitResumeUntreatedEmail($recruit, $recruit->resumes));
                Mail::to($recruit->leading->email)->send(new RecruitResumeUntreatedEmail($recruit, $recruit->resumes));
            }
            foreach ($entrusts as $entrust) {
//                Mail::to("68067348@qq.com")->send(new RecruitResumeUntreatedEmail($recruit, $recruit->resumes));
                Mail::to($entrust->leading->email)->send(new RecruitResumeUntreatedEmail($entrust->recruit, $entrust->resumes));
            }

            //简历24小时没有操作提示---结束

            //4小时没有更换管理员处理自动取消
            CompanyManagerLog::where('created_at', '<' ,date('Y-m-d H:i:s', time()-3600*4))->where('status',0)->update(['status'=>-2]);
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
