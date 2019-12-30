<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRole extends Model
{
    protected $table = 'company_role';

    protected $connection = 'musa';

    public $fillable = [
        'name',
        'alias',
        'sort',
        'remark',
        'icon',
    ];

//
//    public function users()
//    {
//        $userModel = Voyager::modelClass('User');
//
//        return $this->belongsToMany($userModel, 'user_roles')
//            ->select(app($userModel)->getTable().'.*')
//            ->union($this->hasMany($userModel))->getQuery();
//    }

    public function permissions()
    {
        return $this->belongsToMany(CompanyPermission::class,'company_role_permission','company_permission_id','company_role_id');
    }

    public function getPermissions()
    {
        if($this->id==1){
            return CompanyPermission::pluck('key');
        }else{
            return $this->permissions->pluck('key');
        }
    }
}
