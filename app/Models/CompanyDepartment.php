<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDepartment extends Model
{
    protected $table = 'company_department';

    protected $connection = 'musa';
    public $fillable = [
        'name',
        'pid',
        'level',
    ];
}
