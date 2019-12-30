<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitResumeLog extends Model
{
    protected $table = 'company_job_recruit_resume_log';

    protected $connection = 'musa';
    public $fillable = [
        'company_job_recruit_id',
        'company_job_recruit_entrust_id',
        'company_job_recruit_resume_id',
        'text',
        'other_data',
        'status',
        'resume_id',
        'company_id',
        'interviewer',
        'job_id',
        'recruit_id',
        'entrust_id',
        'user_id',
        'created_at',
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

    public function recruitResume()
    {
        return $this->belongsTo('App\Models\RecruitResume','company_job_recruit_resume_id');
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
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function creatorInfo()
    {
        return $this->hasOneThrough(
            'App\Models\UserBasicInfo',
            'App\Models\User',
            'id',
            'user_id',
            'user_id'
        );
    }
}
