<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelLog extends Model
{
    protected $table = 'model_logs';
    public $timestamps = false;
    protected $connection = 'musa';
    public $fillable = [
        'user_id',
        'url',
        'action',
        'ip',
        'model_id',
        'model_type',
        'data',
    ];
}
