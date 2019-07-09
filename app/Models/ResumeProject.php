<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeProject extends Model
{
    protected $table = 'resume_project';
    public $timestamps = false;
    public $fillable = [
        'resume_id',
        'project_name',//项目名称
        'project_start',//开始时间
        'project_end',//结束时间
        'project_desc',//项目描述
        'responsibility',//职责
        'salary',//薪水
        'relate_company',//所属公司
    ];
}
