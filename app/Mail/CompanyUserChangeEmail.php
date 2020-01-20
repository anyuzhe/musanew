<?php

namespace App\Mail;

use App\Models\ExternalToken;
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
        $user = $this->user;
        $company = $this->company;
        $this->subject = "加入{$company->company_name}企业的邮件提醒";
        /**
         XXXXXX公司邀请您加入，请尽快登录musa平台填写基础信息。
         欢迎您加入XXXXXXXXX(公司名称）
        **/

        $content_text_array = ["尊敬的用户您好!"];
        if($user->confirmed){
            $content_text_array[] = "欢迎您加入{$company->company_name}";
        }else{
            $token = ExternalToken::where('userid', $user->id)->first();
            $content_text_array[] = "{$company->company_name}公司邀请您加入，请点击下方链接登录musa平台注册账号";
            $content_text_array[] = "<a href='".config('app.front_url')."/managerRegister?token={$token->token}"."'>点击激活</a>";
        }
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
