<?php

namespace App\Mail;

use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyUserChangeEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /**
         XXXXXX公司邀请您加入，请尽快登录musa平台填写基础信息。
         欢迎您加入XXXXXXXXX(公司名称）
        **/
        $content_text_array = ["尊敬的用户您好!"];
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
