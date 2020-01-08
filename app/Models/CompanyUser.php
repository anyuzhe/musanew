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
        'department_id',
        'entry_at',
        'address_id',
    ];

    public function department()
    {
        return $this->belongsTo(CompanyDepartment::class, 'department_id');
    }
}
