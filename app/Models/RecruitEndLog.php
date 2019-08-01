<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitEndLog extends Model
{
    protected $table = 'company_job_recruit_end_log';

    protected $connection = 'musa';

    public $fillable = [
        'company_id',
        'job_id',
        'company_job_recruit_id',
        'need_num',
        'done_num',
        'resume_num',
        'new_resume_num',
        'start_at',
        'end_at',
    ];

    public function recruit()
    {
        return $this->belongsTo('App\Models\Recruit', 'company_job_recruit_id');
    }
}
