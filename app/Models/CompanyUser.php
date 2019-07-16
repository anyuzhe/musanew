<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    protected $table = 'company_user';
    public $timestamps = false;
    public $connection = 'musa';
    public $fillable = [
        'user_id',
        'company_id',
        'company_role_id',
        'is_current',
    ];
}
