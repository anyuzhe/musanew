<?php

namespace App\Mail;

use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitLeadingUpdateEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $recruit;
    protected $entrust;

    public function __construct(Recruit $recruit, Entrust $entrust)
    {
        $this->recruit = $recruit;
        $this->entrust = $entrust;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->recruit){
            $recruit = $this->recruit;
        }else{
            $recruit = $this->entrust->recruit;
        }
        $entrust_id = $this->entrust?$this->entrust->id:0;
        $activeType = $entrust_id?1:2;
        $job = $recruit->job;
        $this->subject = "您成为了{$job->name}职位招聘的负责人";
        $content_text_array = [];
        $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail/?id={$recruit->id}&recruit_id={$entrust_id}&activeType={$activeType}";
        $content_text_array[] = "您成为了<a href=\"$url\">{$job->name}职位招聘的负责人 请注意查看招聘信息";
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
