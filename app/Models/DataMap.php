<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataMap extends Model
{
    protected $table = 'data_map';
    protected $connection = 'musa';

    public $fillable = [
    ];

    public function options() {
        return $this->hasMany('App\Models\DataMapOption','data_map_id','id');
    }
}
