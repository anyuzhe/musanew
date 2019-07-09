<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitResume extends Model
{
    protected $table = 'company_job_recruit_resume';

    public $fillable = [
        'third_party_id',
        'company_id',
        'job_id',
        'resume_id',
        'company_job_recruit_id',
        'company_job_recruit_entrust_id',
        'creator_id',
        'status',//-1放弃 1投递 2邀请面试 3已面试反馈 4录用
        'resume_source',//来源 1:来源于 委托方添加
    ];

    /**
     *
    -5 录用之后未到岗
    -4 面试通过但不合适
    -3 面试不通过
    -3 面试没来
    -1 简历不匹配
    1 简历投递
    2 邀请面试 可以修改面试时间再次邀约
    3 面试完成(填写反馈后-待定状态)
    4 再次邀请面试
    5 录用
    6 成功入职
     */

    public function logs()
    {
        return $this->hasMany('App\Models\RecruitResumeLog', 'company_job_recruit_resume_id');
    }

    public function resume()
    {
        return $this->belongsTo('App\Models\Resume', 'resume_id');
    }
    public function recruit()
    {
        return $this->belongsTo('App\Models\Recruit', 'company_job_recruit_id');
    }

    public function entrust()
    {
        return $this->belongsTo('App\Models\Recruit', 'company_job_recruit_entrust_id');
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
