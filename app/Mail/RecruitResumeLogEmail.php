<?php

namespace App\Mail;

use App\Models\RecruitResumeLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitResumeLogEmail extends Mailable
{
    use Queueable, SerializesModels;

//    public $subject = '招聘简历更新';
    public $logs;

    /*
     * 1 流转
     * 2 24小时未处理
     */

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $logs = $this->logs;
        $content_text_array = [];
        foreach ($logs as $log) {
            $recruitResume = $log->recruitResume;
            $resume = $recruitResume->resume;
            $recruit = $recruitResume->recruit;
            $job = $recruitResume->job;
            $this->subject = "{$job->name}招聘有更新";
            $content_text_array = ["职位：$job->name", "简历：$resume->name", "更新内容：$log->text"];
            if($log)
                $content_text_array[] = '该姓名已有投递，可能为重复简历';
            $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1";
//        <a href="{!! $url !!}">点击查看详情</a>
        }
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
