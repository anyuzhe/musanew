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

        $this->subject = "{$job->name}招聘有更新";
        $content_text_array = ["职位：$job->name", "简历：$resume->name", "更新内容：$log->text"];
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array)
            ->with('url', "http://musa.anyuzhe.com/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1");
    }
}
