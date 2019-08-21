<?php

namespace App\Console;

use App\Console\Commands\ClearData;
use App\Mail\RecruitResumeLogEmail;
use App\Mail\RecruitResumeUntreatedEmail;
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
                ->where('created_at','<=',date('Y-m-d H:i:s',time()-(3600*24)))->get();
            $recruitResumeIds = $recruitResumeLogs->pluck('company_job_recruit_resume_id')->toArray();

            $recruitResumeHasIds = RecruitResumeLog::where('status','!=',1)->whereIn('company_job_recruit_resume_id', $recruitResumeIds)
                ->pluck('company_job_recruit_resume_id')->toArray();
            
            $recruits = [];
            foreach ($recruitResumeLogs as $recruitResumeLog) {
                if(in_array($recruitResumeLog->company_job_recruit_resume_id, $recruitResumeHasIds)===false){
                    $recruitResume = $recruitResumeLog->recruitResume;
                    //给负责人发送邮件通知
                    $recruit = $recruitResume->recruit;
                    if($recruit->leading_id && $leading = User::find($recruit->leading_id)){
                        if($leading->email){
                            $recruitResume->leading = $leading;
                            if(!isset($recruitResume[$recruit->id]))
                                $recruitResume[$recruit->id] = [];
                            $recruitResume[$recruit->id][] = $recruitResume;
                        }
                    }
                }
            }
            foreach ($recruits as $recruit) {
                Mail::to($recruit[0]->leading)->send(new RecruitResumeUntreatedEmail($recruit));
            }
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
