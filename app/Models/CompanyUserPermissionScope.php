<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUserPermissionScope extends Model
{
    protected $table = 'company_user_permission_scopes';

    protected $connection = 'musa';

    public $fillable = [
        'company_id',
        'company_permission_id',
        'user_id',
        'key',
        'type',
        'department_ids',
        'user_ids',
    ];

//type
//1全部
//2所在的一级部门
//3所在的二级部门
//4自定义部门
}
