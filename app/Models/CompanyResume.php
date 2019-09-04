<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyResume extends Model
{
    protected $table = 'company_resume';

    protected $connection = 'musa';
    public $fillable = [
        'resume_id',
        'company_id',
        'type',// 2 标识 3名单
        'source_type',// 1:外包招聘进来的简历
        'source_recruit_id',//
        'source_entrust_id',//
        'source_job_id',//
    ];
}
