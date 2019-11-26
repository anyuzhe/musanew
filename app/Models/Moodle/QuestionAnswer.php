<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    protected $table = 'question_answers';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];
}
