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
        'job_id',
        'recruit_id',
        'entrust_id',
        'user_id',
    ];

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
