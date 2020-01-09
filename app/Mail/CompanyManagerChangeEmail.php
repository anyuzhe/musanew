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

class CompanyManagerChangeEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $company;
    protected $need_affirm;

    public function __construct($user, $company, $need_affirm=null)
    {
        $this->user = $user;
        $this->company = $company;
        $this->need_affirm = $need_affirm;
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
        $need_affirm = $this->need_affirm;
        $this->subject = "加入{$company->company_name}企业的邮件提醒";
        /**
         * XXXXXX公司邀请您成为企业管理员，请尽快登录musa平台填写企业基础信息。
         * XXXXXX公司邀请您成为企业管理员，请点击下方链接登录musa平台注册账号。
        **/
        $content_text_array = ["尊敬的用户您好!"];
        if($user->confirmed){
            if($need_affirm){

            }else{
                $content_text_array[] = "{$company->company_name}公司邀请您成为企业管理员，请尽快登录musa平台填写企业基础信息";
            }
        }else{
            $token = ExternalToken::where('userid', $user->id)->first();
            $content_text_array[] = "{$company->company_name}公司邀请您成为企业管理员，请点击下方链接登录musa平台注册账号";
            $content_text_array[] = "<a href='".env('APP_FRONT_URL')."/managerRegister?token={$token->token}"."'>点击激活</a>";
        }
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
