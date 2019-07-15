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
        'department',
        'name',
        'salary',
        'salary_min',
        'salary_max',
        'occupation_id',
        'occupation_rank',
        'work_nature',
        'province_id',
        'city_id',
        'district_id',
        'address',
        'status',
        'description',
        'working_years',
        'educational_requirements',
    ];

    public function tests()
    {
        return $this->belongsToMany('App\Models\Course', 'job_test','job_id','course_id');
    }

    public function skills()
    {
            return $this->belongsToMany('App\Models\Skill', 'job_skill','job_id','skill_id')->withPivot('used_time', 'skill_level');;
    }

    public function recruits()
    {
        return $this->hasMany('App\Models\Recruit', 'job_id');
    }
}
