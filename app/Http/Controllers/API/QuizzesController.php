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
use App\Models\Moodle\QuizAttempt;
use App\Models\Moodle\QuizGrade;
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

    public function submit($id)
    {
        $answers = $this->request->get('answers');
        $quiz = Quiz::find($id);
        $fraction = 0;

//        'one_choice'=>[],
//            'multiple_choice'=>[],
//            'filling'=>[],
//            'true_false'=>[],
        $layoutStr = '';
        foreach ($answers as $k=>$answer) {
            $question = Question::find($answer['question_id']);
            $question_type = $answer['question_type'];
            if($question_type=='one_choice' || $question_type=='true_false'){
                $answer = QuestionAnswer::find($answer['answer_id']);
                $fraction += $answer->fraction;
            }elseif($question_type=='filling'){
                $fraction = QuestionAnswer::where('question', $question->id)->where('answer', 'like', $answer['answer_text'])->value('fraction');
                if($fraction)
                    $fraction += $fraction;
            }elseif ($question_type=='multiple_choice'){
                $_fraction = 0;
                $answerIds = is_array($answer['answer_id'])?$answer['answer_id']:[$answer['answer_id']];
                foreach ($answerIds as $answerId) {
                    $_fraction += QuestionAnswer::where('id', $answerId)->value('fraction');
                }
                if($_fraction>0)
                    $fraction += $_fraction;
//                multiple_choice
            }
            $layoutStr .= ($k+1).',0,';
        }
        $layoutStr = substr($layoutStr,0, strlen($layoutStr)-1);
        $user_id = $this->getUser()->id;
        $oldAttempt = QuizAttempt::where('quiz', $id)->where('userid', $user_id)->max('attempt');

//       quiz_attempts
        $attempt = new QuizAttempt();
        $attempt->quiz = $id;
        $attempt->userid = $user_id;
        $attempt->attempt = $oldAttempt?($oldAttempt+1):1;
        $attempt->uniqueid = QuizAttempt::max('uniqueid')+1;
        $attempt->layout = $layoutStr;
        $attempt->currentpage = 1;
        $attempt->preview = 0;
        $attempt->state = 'finished';
        $attempt->timestart = time();
        $attempt->timefinish = time();
        $attempt->timemodified = time();
        $attempt->timemodifiedoffline = 0;
        $attempt->timecheckstate = null;
        $attempt->sumgrades = $fraction;
        $attempt->save();
//         quiz_grades
        $oldQuizGrade = QuizGrade::where('quiz', $id)->where('userid', $user_id)->first();
//        1 最高分
//2 平均分
//3 第一次答题
//4 最后一次答题
        $grade = $quiz->grade / $quiz->sumgrades * $fraction;
        if($quiz->grademethod==1 && $oldQuizGrade->grade>=$grade){
        }elseif ($quiz->grademethod==2 && $oldQuizGrade){
            $quizGrade = new QuizGrade();
            $quizGrade->quiz = $id;
            $quizGrade->userid = $user_id;
            $quizGrade->grade = ($oldQuizGrade->grade+$grade)/2;
            $quizGrade->timemodified = time();
            $quizGrade->save();
            $oldQuizGrade->delete();
        }elseif ($quiz->grademethod==3 && $oldQuizGrade){
        }else{
            if($oldQuizGrade)
                $oldQuizGrade->delete();
            $quizGrade = new QuizGrade();
            $quizGrade->quiz = $id;
            $quizGrade->userid = $user_id;
            $quizGrade->grade = $grade;
            $quizGrade->timemodified = time();
            $quizGrade->save();
        }
        return $this->apiReturnJson(0, [
            'fraction'=>$fraction,
            'grade'=>round($grade, 2),
            'quiz'=>$quiz,
        ]);
    }
}
