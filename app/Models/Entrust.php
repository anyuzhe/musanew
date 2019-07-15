<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrust extends Model
{
    //status 状态 -2 拒绝  -1 取消 0申请中 1正常 2完成
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
}
