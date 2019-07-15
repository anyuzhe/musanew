<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitResumeLog extends Model
{
    protected $table = 'company_job_recruit_resume_log';

    protected $connection = 'musa';
    public $fillable = [
        'company_job_recruit_resume_id',
        'text',
        'other_data',
        'status',
        'resume_id',
        'company_id',
        'user_id',
    ];

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
