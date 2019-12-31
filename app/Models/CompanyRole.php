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
        return $this->belongsToMany('App\Models\UserBasicInfo','musa_company_user_role', 'user_id','role_id','id','user_id');
    }

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
