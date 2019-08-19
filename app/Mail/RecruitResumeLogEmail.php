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
    public $log;

    /*
     * 1 流转
     * 2 24小时未处理
     */
    public $type;

    public function __construct(RecruitResumeLog $log, $type=1)
    {
        $this->log = $log;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $log = $this->log;
        $recruitResume = $log->recruitResume;
        $resume = $recruitResume->resume;
        $recruit = $recruitResume->recruit;
        $job = $recruitResume->job;
        $content_text_array = [];
        $url = '';
        if($this->type==1){
            $this->subject = "{$job->name}招聘有更新";
            $content_text_array = ["职位：$job->name", "简历：$resume->name", "更新内容：$log->text"];
            if($log->status==1){
                $content_text_array[] = '该姓名已有投递，可能为重复简历';
            }
            $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1";
        }elseif ($this->type==2){
            $this->subject = "{$job->name}招聘未即使处理";
            $content_text_array = ["职位：$job->name", "简历：$resume->name", " 请即使处理"];
            $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1";
        }
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array)
            ->with('url', $url);
    }
}
