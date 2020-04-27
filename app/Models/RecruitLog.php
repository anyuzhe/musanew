<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitLog extends Model
{
    protected $table = 'company_job_recruit_update_logs';

    protected $connection = 'musa';
    public $fillable = [
        'company_id',
        'company_job_recruit_id',
//        'company_job_recruit_entrust_id',
        'user_id',
        'content',
    ];

    public function recruit()
    {
        return $this->belongsTo(Recruit::class, 'company_job_recruit_id');
    }

    public function user()
    {
        return $this->hasOneThrough(
            'App\Models\UserBasicInfo',
            'App\Models\User',
            'id',
            'user_id',
            'leading_id'
        );
    }
}
