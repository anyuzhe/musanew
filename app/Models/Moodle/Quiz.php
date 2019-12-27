<?php

namespace App\Models\Moodle;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quiz';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];

    public function grades()
    {
        return $this->hasMany(QuizGrade::class, 'quiz');
    }

    public function slots()
    {
        return $this->hasMany(QuizSlot::class, 'quizid');
    }

    public function courseObj()
    {
        return $this->belongsTo(Course::class, 'course');
    }
}
