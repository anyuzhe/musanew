<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $table = 'industries';
    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'name',
        'pid',
        'level',
    ];
}
