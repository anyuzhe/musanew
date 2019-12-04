<?php

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Moodle\Question;
use App\Models\Moodle\QuestionAnswer;
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
                unset($quiz->grades);
            }
        }
        return $quizzes;
    }

    public function _after_find(&$data)
    {
        $slots = $data->slots->keyBy('questionid');
        $slots->load('question');
        $questions = [];
        $questionCateIds = [];
        foreach ($slots as $slot) {
            $questions[] = $slot->question;
            $questionIds[] = $slot->questionid;
            if ($slot->question['qtype'] == 'random') {
                $questionCateIds[] = $slot->question->category;
            }
        }

        $questions1 = Question::whereIn('category', $questionCateIds)->whereIn('qtype',[
            'truefalse',
            'shortanswer',
            'multichoice',
        ])->get();
        $answers = QuestionAnswer::whereIn('question', array_merge($questionIds, $questions1->pluck('id')->toArray()))->get();
        $questionsByCate = $questions1->groupBy('category');
        $answers = $answers->groupBy('question');
        $questionsType = [
            'one_choice'=>[],
            'multiple_choice'=>[],
            'filling'=>[],
            'true_false'=>[],
        ];
        foreach ($questions as $k=>&$question) {
            if ($question['qtype'] == 'random') {
                $questionsByCate[$question['category']] = $questionsByCate[$question['category']]->shuffle();
                $new = $questionsByCate->get($question['category'])->shift();
                $new['old_id'] = $question['id'];
                $questions[$k] = $new;
            }else{
                $question['old_id'] = null;
            }
        }
        foreach ($questions as $question) {
            $question['answers'] = $answers[$question->id];
            $question['grade'] = $slots->get($question->old_id?$question->old_id:$question->id)->maxmark;
            if($question['qtype']=='truefalse'){
                $questionsType['true_false'][] = $question;
            }if($question['qtype']=='shortanswer'){
                $questionsType['filling'][] = $question;
            }if($question['qtype']=='multichoice'){
                $is_one = true;
                foreach ($question['answers'] as $answer) {
                    if(0<$answer['fraction'] && $answer['fraction']<1){
                        $is_one = false;
                    }
                }
                if($is_one){
                    $questionsType['one_choice'][] = $question;
                }else{
                    $questionsType['multiple_choice'][] = $question;
                }
            }
        }
        unset($data->slots);
        $data->questions = $questionsType;
    }

    public function authLimit(&$model)
    {
        $course_id = $this->request->get('course_id');
        if($course_id){
            $model = $model->where('course', $course_id);
        }
    }
}
