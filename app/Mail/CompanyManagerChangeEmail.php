<?php

namespace App\Mail;

use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyManagerChangeEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $company;

    public function __construct($user, $company)
    {
        $this->user = $user;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /**
         * XXXXXX公司邀请您成为企业管理员，请尽快登录musa平台填写企业基础信息。
         * XXXXXX公司邀请您成为企业管理员，请点击下方链接登录musa平台注册账号。
        **/
        $content_text_array = ["尊敬的用户您好!"];
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
