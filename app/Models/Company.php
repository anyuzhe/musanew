<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'company';

    public $connection = 'musa';

    public $fillable = [
        'company_alias',
        'company_name',
        'tax_no',
        'company_scale',
        'contact_name',
        'contact_phone',
        'industry_id',
        'logo',
        'description',
        'conglomerate_id',
        'id',
    ];

    public function thirdParty()
    {
        //status 雇佣关系 -1:解除 -2:审核不通过 0待审核 1正常雇佣关系
        return $this->belongsToMany('App\Models\Company','company_relationship','company_id','relationship_id')->wherePivotIn('status', [1]);
    }

    public function demandSides()
    {
        //status 雇佣关系 -1:解除 -2:审核不通过 0待审核 1正常雇佣关系
        return $this->belongsToMany('App\Models\Company','company_relationship','relationship_id','company_id')->wherePivotIn('status', [1]);
    }

    public function resumes()
    {
        //人才库
        return $this->belongsToMany('App\Models\Resume','company_resume','company_id','resume_id')->wherePivotIn('type', [1]);
    }

    public function looks()
    {
        return $this->hasMany('App\Models\RecruitResumeLook', 'company_id');
    }

    public function entrusts()
    {
        return $this->hasMany('App\Models\Entrust', 'company_id');
    }

    public function requirements()
    {
        return $this->hasMany('App\Models\Entrust', 'third_party_id');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\CompanyAddress', 'company_id');
    }

    public function industry()
    {
        return $this->belongsTo('App\Models\Industry');
    }

    public function conglomerate()
    {
        return $this->belongsTo('App\Models\Conglomerate');
    }

    public function managers()
    {
        return $this->belongsToMany(UserBasicInfo::class, 'musa_company_user', 'company_id', 'user_id', null, 'user_id')
            ->wherePivot('company_role_id', 1);
    }
    public function getManager()
    {
        return $this->managers()->first();
    }
}
