<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRole extends Model
{
    protected $table = 'company_role';

    public $fillable = [
        'name',
        'alias',
        'sort',
    ];
}
