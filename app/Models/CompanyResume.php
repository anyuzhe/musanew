<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyResume extends Model
{
    protected $table = 'company_resume';

    protected $connection = 'musa';
    public $fillable = [
        'resume_id',
        'company_id',
        'type',//公司名称
    ];
}
