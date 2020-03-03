<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyResumeGradeSetting extends Model
{
    protected $table = 'company_resume_grate_settings';

    protected $connection = 'musa';

    public $fillable = [
        'name',
        'scope',
        'value',
        'company_id',
        'status',
    ];
}
