<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLog extends Model
{
    protected $table = 'company_logs';

    protected $connection = 'musa';
    public $fillable = [
        'company_id',
        'user_id',
        'operation',
        'content',
        'module',
    ];
}
