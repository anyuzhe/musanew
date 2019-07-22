<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDepartment extends Model
{
    protected $table = 'company_departments';

    protected $connection = 'musa';
    public $fillable = [
        'company_id',
        'name',
        'pid',
        'level',
    ];
}
