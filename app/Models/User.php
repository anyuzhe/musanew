<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    public $timestamps = false;
    protected $connection = 'moodle';

    public $fillable = [
        'confirmed',
        'firstname',
        'lastname',
        'deleted',
        'suspended',
    ];


    public function info()
    {
        return $this->hasOne('App\Models\UserBasicInfo','user_id');
    }

    public function resume() {
    	return $this->belongsTo('App\Models\Resume','id', 'user_id');
    }

    public function companies()
    {
        return $this->belongsToMany('App\Models\Company', 'company_user','user_id','company_id')->wherePivot('status', 1)->withPivot('company_role_id');
    }

    public function company()
    {
        return $this->companies()->wherePivot('is_current', 1)->wherePivot('status', 1)->withPivot('company_role_id');
    }
}
