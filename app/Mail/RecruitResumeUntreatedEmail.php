<?php

namespace App\Mail;

use App\Models\RecruitResume;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitResumeUntreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $recruitResumes;

    public function __construct($recruitResumes)
    {
        $this->recruitResumes = $recruitResumes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $recruitResumes = $this->recruitResumes;
        $content_text_array = [];
        foreach ($recruitResumes as $recruitResume) {
            $resume = $recruitResume->resume;
            $recruit = $recruitResume->recruit;
            $job = $recruitResume->job;
            $this->subject = "{$job->name}招聘未即使处理";
            $content_text_array = ["职位：$job->name", "简历：$resume->name", " 请即使处理"];
            $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1";
            $content_text_array[] = "<a href=\"$url\">点击查看详情</a>";
        }
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
