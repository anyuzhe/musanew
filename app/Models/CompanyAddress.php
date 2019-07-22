<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    protected $table = 'company_addresses';

    protected $connection = 'musa';
    public $fillable = [
        'name',
        'company_id',
        'province_id',
        'city_id',
        'district_id',
        'address',
    ];
}
