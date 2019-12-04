<?php

namespace App\Models\Moodle;

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
}
