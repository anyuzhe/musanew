<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitResume extends Model
{
    protected $table = 'company_job_recruit_resume';

    protected $connection = 'musa';
    public $fillable = [
        'third_party_id',
        'company_id',
        'job_id',
        'resume_id',
        'company_job_recruit_id',
        'company_job_recruit_entrust_id',
        'creator_id',
        'status',
        'resume_source',//来源 1:来源于 委托方添加
        'resume_source_company_id',
        'entry_at',
        'interview_at',
    ];

    /**
     *
//    -5 录用之后未到岗
//    -4 面试通过但不合适
//    -3 面试不通过
//    -2 面试没来
//    -1 简历不匹配
    //1 简历投递
    //2 邀请面试 可以修改面试时间再次邀约
    //3 修改时间
    //4 面试完成(填写反馈后-待定状态)
    //5 再次邀请面试
    //6 面试通过
    //7 录用
    //8 成功入职
     */

    public function logs()
    {
        return $this->hasMany('App\Models\RecruitResumeLog', 'company_job_recruit_resume_id');
    }

    public function looks()
    {
        return $this->hasMany('App\Models\RecruitResumeLook', 'company_job_recruit_resume_id');
    }

    public function resume()
    {
        return $this->belongsTo('App\Models\Resume', 'resume_id');
    }
    public function recruit()
    {
        return $this->belongsTo('App\Models\Recruit', 'company_job_recruit_id');
    }
    public function job()
    {
        return $this->belongsTo('App\Models\Job', 'job_id');
    }

    public function entrust()
    {
        return $this->belongsTo('App\Models\Entrust', 'company_job_recruit_entrust_id');
    }

    public function thirdParty()
    {
        return $this->belongsTo('App\Models\Company', 'third_party_id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

}
