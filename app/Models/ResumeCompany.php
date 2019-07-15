<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeCompany extends Model
{
    protected $table = 'resume_company';

    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'resume_id',
        'industry',//所属行业
        'job_title',//职位名称
        'job_category',//职位类型
        'job_start',//开始时间
        'job_end',//结束时间
        'salary',//薪水
        'job_desc',//工作描述
        'company_name',//公司名称
    ];
}
