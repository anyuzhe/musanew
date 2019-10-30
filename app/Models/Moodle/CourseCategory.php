<?php

namespace App\Models\Moodle;

use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    protected $table = 'course_categories';
    public $timestamps = false;
    protected $connection = 'moodle';
    public $fillable = [
    ];

    public function children()
    {
        return $this->hasMany(CourseCategory::class, 'parent', 'id');
    }
}
