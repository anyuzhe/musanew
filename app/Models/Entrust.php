<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrust extends Model
{
    //status 状态 -3 外包方未确定直接取消  -2 拒绝  -1 取消 0申请中 1正常 2完成 6暂停
    protected $table = 'company_job_recruit_entrust';
    protected $connection = 'musa';
    public $fillable = [
        'job_id',
        'company_id',
        'third_party_id',
        'company_job_recruit_id',
        'done_num',
        'resume_num',
        'new_resume_num',
        'status',
        'creator_id',
        'leading_id',
        'end_at',
        'is_public',
        'created_at',
        'end_at',
        'source_recruit_id',
        'source_entrust_id',
        'affirmed_at',
    ];

    public function job()
    {
        return $this->belongsTo('App\Models\Job', 'job_id');
    }

    public function recruit()
    {
        return $this->belongsTo('App\Models\Recruit', 'company_job_recruit_id');
    }

    public function thirdParty()
    {
        return $this->belongsTo('App\Models\Company', 'third_party_id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function leading()
    {
        return $this->hasOneThrough(
            'App\Models\UserBasicInfo',
            'App\Models\User',
            'id',
            'user_id',
            'leading_id'
        );
    }

    public function creator()
    {
        return $this->hasOneThrough(
            'App\Models\UserBasicInfo',
            'App\Models\User',
            'id',
            'user_id',
            'creator_id'
        );
    }
}
