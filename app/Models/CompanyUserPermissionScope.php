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
}
