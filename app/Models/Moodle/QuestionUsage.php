<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class QuestionUsage extends Model
{
    protected $table = 'question_usages';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
        'contextid',
        'component',
        'preferredbehaviour',
    ];
}
