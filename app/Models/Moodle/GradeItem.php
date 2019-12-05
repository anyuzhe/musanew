<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class GradeItem extends Model
{
    protected $table = 'grade_items';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];
}
