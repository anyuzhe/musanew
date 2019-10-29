<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recruit extends Model
{
    //status 1招聘中 2等待外包公司审核 3外包中 4结束 5已完成 6暂停
    protected $table = 'company_job_recruit';
    protected $connection = 'musa';
    public $fillable = [
        'job_id',
        'need_num',
        'is',
        'leading_id',
        'is_public',
    ];

    public function job()
    {
        return $this->belongsTo('App\Models\Job', 'job_id');
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

    public function entrusts()
    {
        //status 状态 -2 拒绝  -1 取消 0申请中 1正常 2完成
        return $this->hasMany('App\Models\Entrust', 'company_job_recruit_id');
    }
}
