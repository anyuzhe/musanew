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

    public function users()
    {
        return $this->belongsToMany('App\Models\UserBasicInfo','musa_company_user_role', 'role_id','user_id','id','user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(CompanyPermission::class,'company_role_permission','company_role_id','company_permission_id');
    }

    public function getPermissions()
    {
        if($this->id==1){
            return CompanyPermission::pluck('full_key');
        }else{
            return $this->permissions->pluck('full_key');
        }
    }
}
