<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    public $connection = 'musa';

    public $fillable = [
    ];

    public function thirdParty()
    {
        //status 雇佣关系 -1:解除 -2:审核不通过 0待审核 1正常雇佣关系
        return $this->belongsToMany('App\Models\Company','company_relationship','company_id','relationship_id')->wherePivotIn('status', [1]);
    }
    public function resumes()
    {
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
}
