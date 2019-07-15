<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeEducation extends Model
{
    protected $table = 'resume_education';

    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'resume_id',
        'school_name',//名称
        'major',//专业
        'start_date',//开始时间
        'end_date',//结束时间
        'national',//是否统招
        'education',//学历
    ];
}
