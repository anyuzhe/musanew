<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class QuizSlot extends Model
{
    protected $table = 'quiz_slots';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];
}
