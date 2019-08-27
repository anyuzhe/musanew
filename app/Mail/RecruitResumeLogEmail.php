<?php

namespace App\Mail;

use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecruitResumeLogEmail extends Mailable
{
    use Queueable, SerializesModels;

//    public $subject = '招聘简历更新';
    public $logs;
    public $status;

    /*
     * 1 流转
     * 2 24小时未处理
     */

    public function __construct($logs, $status=null)
    {
        $this->logs = $logs;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $logs = $this->logs;
        $status = $this->status;
        $content_text_array = [];
        $_one = $logs[0];
        if(!$status)
            $status = $_one->status;
        $job = $_one->job;
        if($status==1){
            $this->subject = "{$job->name}招聘收到简历";
            $str = '';
            $str .= "您招聘的{$job->name}收到";
            foreach ($logs as $log) {
                $resume = $log->resume;
                //查找重复
                $resumeIds = Resume::where('name', $resume->name)->where('id','!=',$resume->id)->pluck('id')->toArray();
                $has = RecruitResume::whereIn('resume_id', $resumeIds)->where('company_job_recruit_id', $log->company_job_recruit_id)->first();

                $has_text = '';
                if($has){
                    $has_text = '<span style="color: red">(可能是重复简历)</span>';
                }
                $url = env('APP_FRONT_URL')."/company/recruitment/resumeEdit/?id={$resume->id}&type=3&recruit_resume_id={$log->recruit_resume_id}&showChart=1";

                $str .= "<a href=\"$url\">{$resume->name}</a>{$has_text}，";
            }
            $str = substr($str,0,strlen($str)-1);

            $count = count($logs);
            $str .= " 共计{$count}份新简历，请及时查看";
            $content_text_array[] = $str;
            $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$_one->company_job_recruit_id}&activeType=1";
            $content_text_array[] = "<a href=\"$url\">点击查看详情</a>";
        }else{
            $this->subject = "{$job->name}招聘有更新";
            foreach ($logs as $log) {
                $resume = $log->resume;
                if($status==-5){
                    $content_text_array[] = "<span style='color: red'>{$resume->name}未到{$job->name}报道</span>，已关闭";
                }elseif ($status==-4){
                    $content_text_array[] = "{$resume->name} 面试通过但不合适 {$job->name} 面试，已关闭";
                }elseif ($status==-3){
                    $content_text_array[] = "{$resume->name} 面试 {$job->name} 失败，已关闭";
                }elseif ($status==-2){
                    $content_text_array[] = "{$resume->name} 放弃 {$job->name} 面试，已关闭";
                }elseif ($status==-1){
                    $content_text_array[] = "{$resume->name} 不匹配 {$job->name}，已关闭";
                }elseif ($status==2 ||$status==5 ){
                    $content_text_array[] = "{$resume->name} 应聘 {$job->name}，已协商约定 <span style='color: red'>{$log->other_data}</span> 进行面试";
                }elseif ($status==3){
                    $old = RecruitResumeLog::where('company_job_recruit_resume_id', $log->company_job_recruit_resume_id)->where('id','!=',$log->id)->orderBy('id','desc')->first();
                    $content_text_array[] = "{$resume->name} 应聘 {$job->name}，邀约面试时间时间从 {$old->other_data} 改成<span style='color: red'>{$log->other_data}</span> ,请知晓";
                }elseif ($status==4){
                    $content_text_array[] = "{$resume->name} 应聘 {$job->name}，完成面试，目前处于待定状态，请及时处理";
                    $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$log->recruit->id}&activeType=1";
                    $content_text_array[] = "<a href=\"$url\">点击查看详情</a>";
                }elseif ($status==6){
                    $content_text_array[] = "<span style='color: red'>{$resume->name} 录用 {$job->name}，将于 {$log->other_data} 正式入职。</span>";
                }elseif ($status==7){
                    $recruit = $log->recruit;
                    $residue_num = $recruit->need_num - $recruit->done_num - $recruit->wait_entry_num;
                    $content_text_array[] = "{$resume->name} 正式入职 {$job->name}，目前还剩<span style='color: red'>{$residue_num}</span>人需要招聘";
                }
//                $recruitResume = $log->recruitResume;
//                $resume = $recruitResume->resume;
//                $recruit = $recruitResume->recruit;
//                $job = $recruitResume->job;
//                $this->subject = "{$job->name}招聘有更新";
//                $content_text_array = ["职位：$job->name", "简历：$resume->name", "更新内容：$log->text"];
////                if($log)
//                    $content_text_array[] = '该姓名已有投递，可能为重复简历';
//                $url = env('APP_FRONT_URL')."/company/recruitment/recruitmentDetail?id={$recruit->id}&activeType=1";
//        <a href="{!! $url !!}">点击查看详情</a>
            }
        }
        return $this->view('emails.recruitResumeLogEmail')
            ->with('content_text_array', $content_text_array);
    }
}
