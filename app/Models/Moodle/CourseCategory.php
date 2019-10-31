<?php

namespace App\Models\Moodle;

use App\Models\Course;
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

    public function courses()
    {
        return $this->hasMany(Course::class, 'category');
    }
}
