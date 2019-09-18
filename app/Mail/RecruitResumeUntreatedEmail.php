<?php

namespace App\Mail;

use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitResumeUntreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $recruit;
    protected $resumes;

    public function __construct(Recruit $recruit, $resumes)
    {
        $this->recruit = $recruit;
        $this->resumes = $resumes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $recruit = $this->recruit;
        $job = $recruit->job;
        $this->subject = "{$job->name}招聘未即使处理";
        $content_text_array = [];
        $str = '';
        $str .= "您招聘的{$job->name}有";
        foreach ($this->resumes as $resume) {
            //查找重复
            $resumeIds = Resume::where('name', $resume->name)->where('id','!=',$resume->id)->pluck()->toArray();
            $has = RecruitResume::whereIn('resume_id', $resumeIds)->where('company_job_recruit_id', $recruit->id)->get();
            if($has){
                $has_text = '<span style="color: red">(可能是重复简历)</span>';
            }else{
                $has_text = '';
            }
            $url = env('APP_FRONT_URL')."/company/recruitment/resumeEdit/?id={$resume->id}&type=3&recruit_resume_id={$resume->recruit_resume_id}&showChart=1";
            $str .= "<a href=\"$url\">{$resume->name}</a>$has_text, ";
        }
        $str = substr($str,0,strlen($str)-2);
        $count = count($this->resumes);
        $str .= " 共计{$count}份简历未即使处理，请及时查看";
        $content_text_array[] = $str;
        $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1";
        $content_text_array[] = "<a href=\"$url\">点击查看详情</a>";
//        $content_text_array[] = '<br/>';
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
