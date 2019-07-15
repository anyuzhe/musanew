<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitResumeLook extends Model
{
    protected $table = 'company_job_recruit_resume_look';

    protected $connection = 'musa';
    public $fillable = [
        'company_job_recruit_resume_id',
        'status',
        'resume_id',
        'company_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
