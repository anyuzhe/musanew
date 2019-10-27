<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class QuizGrade extends Model
{
    protected $table = 'quiz_grades';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];
}
