<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataMapOption extends Model
{
    protected $table = 'data_map_option';
    public $timestamps = false;
    protected $connection = 'musa';
    public $fillable = [
        'data_map_id',
        'text',
        'value',
    ];
}
