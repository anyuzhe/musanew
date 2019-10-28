<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $table = 'resume';

    protected $connection = 'musa';
    public $fillable = [
        'user_id',//
        'workplace',//期望工作地点
        'job_status',//求职状态
        'hope_job_text',//期望职位
        'hope_salary_min',
        'hope_salary_max',
        'start_work_at',//开始工作
        'education',//最高学历
        'work_nature',//期望工作性质（全职 兼职）
        'intro',//个人自评
        'name',//应聘人姓名
        'phone',//手机
        'gender',//性别 1男0女
        'is_married',//婚姻状态 0未婚 1已婚
        'is_upload',//是否上传
        'birthdate',//出生年月日
        'avator',//头像
        'visable',//可见1，不可见2
        'company_id',//
        'permanent_province_id',//户籍省
        'permanent_city_id',//户籍市
        'permanent_district_id',//户籍区
        'residence_province_id',//现居地省
        'residence_city_id',//现居地市
        'residence_district_id',//现居地区
        'residence_address',//现居地详细地址
        'third_party_evaluation',//第三方评价
        'type',//1:外包方录入的简历。 2个人简历
        'self_evaluation',//自我评价
        'on_the_job',//是否在职
        'on_the_job_company_name',//在职公司
    ];

    public function skills()
    {
        return $this->hasMany('App\Models\ResumeSkill');
    }

    public function attachments()
    {
        return $this->hasMany('App\Models\ResumeAttachment');
    }

    public function educations()
    {
        return $this->hasMany('App\Models\ResumeEducation');
    }

    public function companies()
    {
        return $this->hasMany('App\Models\ResumeCompany');
    }

    public function jobCompany()
    {
        return $this->belongsTo('App\Models\Company','company_id');
    }

    public function assignmentCompany()
    {
        return $this->belongsTo('App\Models\Company','assignment_id');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\ResumeProject');
    }

    public function trains()
    {
        return $this->hasMany('App\Models\ResumeTrain');
    }
}
