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
        $data = Quiz::select(DB::raw('mdl_quiz.course as course_id,mdl_quiz.id ,  mdl_quiz.id as quiz_id, mdl_quiz.name, mdl_quiz.grade as total_grade, mdl_quiz_grades.grade'))
            ->leftJoin('quiz_grades', 'quiz.id', '=', 'quiz_grades.quiz')->where('quiz.course', $test->id)->where('quiz_grades.userid', $user->id)->get();
        $allNum = 0;
        $doneNum = 0;
        $allGrade = 0;
        $doneGrade = 0;
        $data = $data->keyBy('quiz_id');
        $quizs = Quiz::where('course', $test->id)->get();

        foreach ($quizs as $quiz) {
            if($data->get($quiz->id)){
                $v = $data->get($quiz->id);
            }else{
                $v = null;
            }
            $allNum++;
            $allGrade+= $quiz->grade;
            if($v!==null){
                $doneNum++;
                $doneGrade+= $v->grade;
            }
        }
        return [
            'name'=>$test->shortname,
            'done_rate'=>$allNum==0?0:(int)($doneNum/$allNum*100),
            'grade'=>$allGrade==0?0:(int)($doneGrade/$allGrade*100)
        ];
    }
}
