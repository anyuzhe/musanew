<?php

namespace App\Models;

use App\Models\Moodle\Quiz;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'course';
    protected $connection = 'moodle';
    public $fillable = [
    ];

    public function quizs()
    {
        return $this->hasMany(Quiz::class, 'course', 'id');
    }
}
