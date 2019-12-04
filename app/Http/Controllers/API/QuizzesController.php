<?php

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Moodle\Quiz;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\Skill;
use App\Models\UserBasicInfo;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Job;

class QuizzesController extends ApiBaseCommonController
{
    use SoftDeletes;

    public $model_name = Quiz::class;

    public function _after_get(&$quizzes)
    {
        $user = $this->getUser();
        if($user){
            $quizzes->load('grades');
            foreach ($quizzes as &$quiz) {
                $_q = $quiz->grades->keyBy('userid')->get($user->id);
                if($_q){
                    $quiz->user_grade = $_q->grade;
                }else{
                    $quiz->user_grade = null;
                }
            }
        }
        return $quizzes;
    }

    public function _after_find(&$data)
    {
    }

    public function authLimit(&$model)
    {
        $course_id = $this->request->get('course_id');
        if($course_id){
            $model = $model->where('course', $course_id);
        }
    }
}
