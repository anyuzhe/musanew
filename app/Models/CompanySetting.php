<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $connection = 'musa';
    public $fillable = [
        'key',
        'display_name',
        'value',
        'company_id',
    ];
}
