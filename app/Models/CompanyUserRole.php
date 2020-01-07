<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUserRole extends Model
{
    protected $table = 'company_user_role';
    public $connection = 'musa';
    public $fillable = [
        'user_id',
        'company_id',
        'role_id',
    ];
}
