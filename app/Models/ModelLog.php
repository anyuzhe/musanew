<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelLog extends Model
{
    protected $table = 'model_logs';
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
