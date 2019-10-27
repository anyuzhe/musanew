<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $table = 'quiz_attempts';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];
}
