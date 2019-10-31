<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Moodle\CourseCategory;
use App\Models\Moodle\Quiz;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Support\Facades\DB;

class TestsRepository
{
    public function getTestData($test, $user)
    {
        $data = Quiz::select(DB::raw('mdl_quiz.course as course_id, mdl_quiz.name, mdl_quiz.grade as total_grade, mdl_quiz_grades.grade'))->leftJoin('quiz_grades', 'quiz.id', '=', 'quiz_grades.quiz')->where('quiz.course', $test->id)->get();
        dd($data->toArray());
    }
}
