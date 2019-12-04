<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];
}
