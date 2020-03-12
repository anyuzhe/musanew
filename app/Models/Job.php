<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;
    protected $connection = 'musa';

    protected $table = 'jobs';

    public $fillable = [
        'code',
        'major_requirements',
        'company_id',
        'name',
        'salary',
        'salary_min',
        'salary_max',
        'occupation_id',
        'occupation_rank',
        'work_nature',
//        'province_id',
//        'city_id',
//        'district_id',
//        'address',
        'status',
        'description',
        'working_years',
        'educational_requirements',
        'address_id',
        'department_id',
        'is_formal',
        'source_job_id',
        'source_company_id',
        'source_recruit_id',
        'resume_grade_setting_id',
    ];

    public function tests()
    {
        return $this->belongsToMany('App\Models\Course', 'job_test','job_id','course_id');
    }

    public function skills()
    {
        return $this->belongsToMany('App\Models\Skill', 'job_skill','job_id','skill_id')->withPivot('used_time', 'skill_level');
    }

    public function necessarySkills()
    {
        return $this->belongsToMany('App\Models\Skill', 'job_skill','job_id','skill_id')->wherePivot('type', 1)->withPivot('used_time', 'skill_level');
    }

    public function optionalSkills()
    {
        return $this->belongsToMany('App\Models\Skill', 'job_skill','job_id','skill_id')->wherePivot('type', 2)->withPivot('used_time', 'skill_level');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    public function sourceCompany()
    {
        return $this->belongsTo('App\Models\Company', 'source_company_id');
    }

    public function recruits()
    {
        return $this->hasMany('App\Models\Recruit', 'job_id');
    }

    public function address()
    {
        return $this->belongsTo('App\Models\CompanyAddress', 'address_id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\CompanyDepartment', 'department_id');
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function resumeGradeSetting()
    {
        return $this->belongsTo('App\Models\CompanyResumeGradeSetting', 'resume_grade_setting_id');
    }
}
