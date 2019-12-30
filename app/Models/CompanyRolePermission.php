<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRolePermission extends Model
{
    protected $table = 'company_role_permission';

    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'company_role_id',
        'company_permission_id',
    ];
}
