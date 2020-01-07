<?php

namespace App\Repositories;

use App\Mail\RecruitResumeLogEmail;
use App\Models\Area;
use App\Models\CompanyResume;
use App\Models\CompanyUser;
use App\Models\DataMapOption;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\RecruitResumeLook;
use App\Models\Resume;
use App\User;
use App\ZL\Moodle\TokenHelper;
use Illuminate\Support\Facades\Mail;

class RecruitResumesRepository
{
    public function checkFlow($recruitResume,$status,$data)
    {
        if($status==2 && !in_array($recruitResume->status,[1])){
            return '简历不是初始状态,不能邀请面试';
        }elseif($status==3 && !in_array($recruitResume->status,[2,3,5])){
            return '简历不是邀请或再次邀请面试状态,不能修改时间';
        }elseif($status==4 && !in_array($recruitResume->status,[2,3,5])){
            return '简历不是邀请或再次邀请面试状态,不能待定';
        }elseif($status==5 && !in_array($recruitResume->status,[4])){
            return '简历不是邀请面试状态或者完成状态,不能再次邀请面试';
        }elseif($status==6 && !in_array($recruitResume->status,[4])){
            return '简历不是待定状态,不能面试通过';
        }elseif($status==7 && !in_array($recruitResume->status,[6])){
            return '简历不是面试通过状态,不能录用';
        }elseif($status==8 && !in_array($recruitResume->status,[7])){
            return '简历不是录用状态,不能成功入职';
        }

        if($status==-1 && !in_array($recruitResume->status,[1])){
            return '简历不是初始状态,不能简历不匹配';
        }elseif($status==-2 && !in_array($recruitResume->status,[2,3,5])){
            return '简历不是邀请或再次邀请面试状态,不能面试没来';
        }elseif($status==-3 && !in_array($recruitResume->status,[4])){
            return '简历不是待定状态,不能面试不通过';
        }elseif($status==-4 && !in_array($recruitResume->status,[4,6])){
            return '简历不是待定状态,不能面试通过但不合适';
        }elseif($status==-5 && !in_array($recruitResume->status,[7])){
            return '简历不是录用状态,不能录用之后未到岗';
        }
        if(($status==2||$status==3) && !$data){
            return '邀请时间必须填写';
        }
        if(($status==4) && !$data){
            return '面试反馈必须填写';
        }
        return null;
    }

    public function generateLog(RecruitResume $recruitResume, $status, $company, $otherData='', $type=1,$interviewer=null)
    {
        //    -5 录用之后未到岗
        //    -4 面试通过但不合适
        //    -3 面试不通过
        //    -2 面试没来
        //    -1 简历不匹配
        //1 简历投递
        //2 邀请面试 可以修改面试时间再次邀约
        //3 修改时间
        //4 待定(填写反馈后-待定状态)
        //5 再次邀请面试
        //6 面试通过
        //7 录用
        //8 成功入职

        global $LOGIN_USER;
        global $LOGIN_USER_CURRENT_COMPANY;
        $log = new RecruitResumeLog();
        $log->user_id = $LOGIN_USER->id;
        $log->company_id = $company?$company->id:null;
        $log->status = $status;
        $log->resume_id = $recruitResume->resume_id;
        $log->company_job_recruit_id = $recruitResume->company_job_recruit_id;
        $log->company_job_recruit_entrust_id = $recruitResume->company_job_recruit_entrust_id;
        $log->job_id = $recruitResume->job_id;
        $log->interviewer = $interviewer;
        $log->other_data = $otherData;
        if($status==1){
            if($type==1){
                $log->text = $company->company_alias.' 添加简历';
            }else{
                if($recruitResume->company_job_recruit_entrust_id){
                    $log->text =  $LOGIN_USER->info->realname." 向($company->company_alias)投递简历";
                }else{
                    $log->text =  $LOGIN_USER->info->realname." 投递简历";
                }
            }
            $this->companyRelevanceResume($recruitResume->resume_id, $recruitResume->company_id, $recruitResume->recruit, $recruitResume->entrust);
        }elseif($status==2){
            $recruitResume->interview_at = $otherData;
            $log->text =  '邀请面试-'.$otherData;
            $this->minusNewResumeHandle($recruitResume);
        }elseif($status==3){
            $recruitResume->interview_at = $otherData;
            $log->text =  '修改面试时间-'.$otherData;
        }elseif($status==4){
            if($recruitResume->status==2 || $recruitResume->status==3){
                $log->text =  '面试结果反馈：'.$otherData;
            }else{
                $log->text =  '再次面试结果反馈：'.$otherData;
            }
        }elseif($status==5){
            $recruitResume->interview_at = $otherData;
            $log->text =  '再次邀请面试-'.$otherData;
        }elseif($status==6){
            $log->text =  '面试通过:'.$otherData;
        }elseif($status==7){
            $log->text =  '录用-计划入职时间:'.$otherData;
            $recruitResume->entry_at = $otherData;
            $recruit = $recruitResume->recruit;
            $recruit->wait_entry_num++;
            $recruit->save();
        }elseif($status==8){
            $_time = $otherData?$otherData:date('Y-m-d H:i:s');
            $log->text =  '成功入职-'.$_time;
            $recruitResume->formal_entry_at = $_time;
            $this->hiredEntryHandle($recruitResume);
            $this->companyRelevanceUser($recruitResume->company_id, $recruitResume->user, $recruitResume->job->department_id);
        }elseif($status==-1){
            $log->text =  '简历不匹配';
            $this->minusNewResumeHandle($recruitResume);
        }elseif($status==-2){
            $log->text =  '面试没来';
        }elseif($status==-3){
            $log->text =  '面试不通过';
        }elseif($status==-4){
            $log->text =  '面试通过但不合适';
        }elseif($status==-5){
            $log->text =  '录用之后未到岗';
        }
        $logObj = $recruitResume->logs()->save($log);
        $recruitResume->status = $status;
        $recruitResume->save();
        //给负责人发送邮件通知
        if($logObj->status!=1){
            sendLogsEmail([$logObj]);
        }
        return $logObj;
    }

    public function haveLook(RecruitResume $recruitResume)
    {
        $has = RecruitResumeLook::where('company_job_recruit_resume_id',$recruitResume->id)
            ->where('user_id', TokenRepository::getUser()->id)
            ->where('company_id',TokenRepository::getCurrentCompany()->id)->first();
        if(!$has){
            RecruitResumeLook::create([
                'company_job_recruit_resume_id'=>$recruitResume->id,
                'status'=>1,
                'resume_id'=>$recruitResume->resume_id,
                'company_id'=>TokenRepository::getCurrentCompany()->id,
                'user_id'=>TokenRepository::getUser()->id,
            ]);
        }
    }

    public function hiredEntryHandle(RecruitResume $recruitResume)
    {
        $recruitRepository = app()->build(RecruitRepository::class);
        $recruit = $recruitResume->recruit;
        $entrust = $recruitResume->entrust;
        $resume = $recruitResume->resume;
        $recruit->done_num++;
        $recruit->wait_entry_num--;
        if($recruit->done_num>=$recruit->need_num){
            //如果是委托 就不改成结束状态
            if(!$entrust)
                $recruit->status = 5;
            foreach ($recruit->entrusts as $_entrust) {
                $_entrust->status = 2;
                $_entrust->end_at = date('Y-m-d H:i:s');
                $_entrust->save();
                $recruitRepository->generateEndLog($entrust->recruit, $_entrust);
            }

            $recruit->end_at = date('Y-m-d H:i:s');
            $recruitRepository->generateEndLog($recruit);
        }
        $recruit->save();
        if($entrust){
            $entrust->done_num++;
            if($recruit->done_num>=$recruit->need_num){
                $entrust->status = 2;
            }
            $entrust->save();

            $resume->company_id = $entrust->third_party_id;
            $resume->assignment_id = $entrust->company_id;

            $this->companyRelevanceResume($resume->id, $entrust->company_id, $recruit, $entrust);
        }else{
            $resume->company_id = $recruit->company_id;
        }
        $resume->company_id = $recruit->company_id;
        $resume->in_job = 1;
        $resume->save();
    }

    public function companyRelevanceResume($resume_id, $company_id, Recruit $recruit=null, Entrust $entrust=null)
    {
        //往需求方添加人才库关联
        $_has = CompanyResume::where('company_id', $company_id)->where('resume_id', $resume_id)->where('type', 1)->first();
        if(!$_has){
            CompanyResume::create([
                'company_id'=>$company_id,
                'resume_id'=>$resume_id,
                'type'=>1,
                'source_type'=>1,
                'source_recruit_id'=>$recruit?$recruit->id:null,
                'source_entrust_id'=>$entrust?$entrust->id:null,
                'source_job_id'=>$recruit?$recruit->job->id:null,
                'source_company_id'=>$entrust?$entrust->company_id:null,
                'creator_id'=>TokenRepository::getUser()->id,
            ]);
        }
    }

    public function companyRelevanceUser($company_id, $user_id, $department_id)
    {
        //往需求方添加人才库关联
        $_has = CompanyUser::where('company_id', $company_id)->where('user_id', $user_id)->first();
        if(!$_has){
            CompanyUser::create([
                'company_id'=>$company_id,
                'user_id'=>$user_id,
                'department_id'=>$department_id,
            ]);
        }else{
            if($_has->department_id!=$department_id){
                $_has->department_id;
                $_has->save();
            }
        }
    }

    public function minusNewResumeHandle(RecruitResume $recruitResume)
    {
        $recruit = $recruitResume->recruit;
        $entrust = $recruitResume->entrust;
        if($recruit->new_resume_num>0)
            $recruit->new_resume_num--;
        $recruit->save();
        if($entrust){
            $entrust->new_resume_num--;
            $entrust->save();
        }
    }

    public function addFieldText(&$data, $isPerSon=false)
    {
        switch ($data->status){
            case -5:
                $data->status_str = '录用之后未到岗';
                break;
            case -4:
                $data->status_str = '面试通过但不合适';
                break;
            case -3:
                $data->status_str = '面试不通过';
                break;
            case -2:
                $data->status_str = '面试没来';
                break;
            case -1:
                $data->status_str = '简历不匹配';
                break;
            case 1:
                if($isPerSon)
                    $data->status_str = '等待反馈';
                else
                    $data->status_str = '简历投递';
                break;
            case 2:
                $data->status_str = '邀请面试';
                break;
            case 3:
                $data->status_str = '修改面试时间';
                break;
            case 4:
                $data->status_str = '待定';
                break;
            case 5:
                $data->status_str = '再次邀请面试';
                break;
            case 6:
                $data->status_str = '面试通过';
                break;
            case 7:
                $data->status_str = '录用';
                break;
            case 8:
                $data->status_str = '成功入职';
                break;
            default:
                $data->status_str = '未知状态';
                break;

        }
        switch ($data->resume_source){
            case 1:
                $data->resume_source_str = $data->thirdParty?"外包({$data->thirdParty->company_alias})":"{$data->company->company_alias}";
                break;
            case 2:
                $data->resume_source_str = '个人投递';
                break;
            case 3:
//                $data->resume_source_str = '导入简历';
                $data->resume_source_str = $data->thirdParty?"外包({$data->thirdParty->company_alias})":"{$data->company->company_alias}";
                break;
            default:
                $data->resume_source_str = '来源未知';
                break;

        }
    }

    public function getQuizResults($data)
    {
        $job = $data->job;
        $resume = $data->resume;
        $user = $resume->user;
        $tests = [];
        foreach ($job->tests as $test) {
            $quizs = [];
            foreach ($test->quizs as $quiz) {
                $_quiz = [
                  'name'=>$quiz->name,
                  'grade'=>$quiz->grades()->where('userid',$user->id)->value('grade'),
                ];
                $quizs[] = $_quiz;
            }
            $_data = [
              'name'=>$test->shortname,
              'quizs'=>$quizs,
            ];
            $tests[] = $_data;
        }
        return $tests;
    }

    public function matching($data)
    {
        $job = $data->job;
        $resume = $data->resume;
        $company = $job->company;

        $resumeGrade = CompanySettingRepository::getResumeGrade($company->id);
        $resumeGradeArr = json_decode($resumeGrade->value, true);
        //学历要求
        $config_education_num = DataMapOption::where('data_map_id',7)->count()-1;
        $config_education_score = 100/$config_education_num;
        if($job->educational_requirements>1){
            if($resume->education){
                $education_score = 100-($job->educational_requirements-1-$resume->education)*$config_education_score;
                if($education_score>100)
                    $education_score = 100;
            }else{
                $education_score = 0;
            }
        }else{
            $education_score = 100;
        }
//        //工作年数
        if($job->working_years){
            $config_working_years_score = 100/(DataMapOption::where('data_map_id',9)->count()-1);
            $years = (time()-strtotime($resume->start_work_at))/(3600*24*30*12);
            if($years<1){
                $year_value = 1;
            }elseif ($years<3){
                $year_value = 2;
            }elseif ($years<5){
                $year_value = 3;
            }elseif ($years<10){
                $year_value = 4;
            }else{
                $year_value = 5;
            }
            $working_years_score = 100-($config_working_years_score)*($job->working_years-$year_value);
            if($working_years_score>100)
                $working_years_score = 100;
        }else{
            $working_years_score = 100;
        }
        //技能
        $resume_skills = $resume->skills->keyBy('skill_id')->toArray();
        $necessarySkillsGradeArr = checkSkillsGrade($resume_skills, $job->necessarySkills);
        $optionalSkillsGradeArr = checkSkillsGrade($resume_skills, $job->optionalSkills);

        $necessary_skills_score = $necessarySkillsGradeArr[0];
        $necessary_skills_data = $necessarySkillsGradeArr[1];

        $optional_skills_score = $optionalSkillsGradeArr[0];
        $optional_skills_data = $optionalSkillsGradeArr[1];

        $skills_data = [];
        //以后无用的技能匹配代码
        $job_skills_count = count($job->skills);
        $skills_score = 0;
        //单分
        $config_skill_score = 100/(DataMapOption::where('data_map_id',10)->count());
        $config_skill_data = DataMapOption::where('data_map_id',10)->get()->keyBy('value')->toArray();
        foreach ($job->skills as $job_skill) {
            $_job_skill_name = $job_skill->name;
            $_job_skill_id = $job_skill->pivot->skill_id;
            $_job_skill_level = $job_skill->pivot->skill_level;
            if(isset($resume_skills[$_job_skill_id])){
                $resume_skill = $resume_skills[$_job_skill_id];
                $_score = (int)(100 - ($_job_skill_level - $resume_skill['skill_level'])*$config_skill_score);
                $skills_data[] = [
                    'skill_name'=>$_job_skill_name,
                    'job_level'=>$_job_skill_level,
                    'job_level_text'=>$config_skill_data[$_job_skill_level]['text'],
                    'resume_level'=>$resume_skill['skill_level'],
                    'resume_level_text'=>$config_skill_data[$resume_skill['skill_level']]['text'],
                    'sroce'=>$_score,
                ];
                $skills_score += (int)($_score/$job_skills_count);
            }else{
                $skills_score += 0;
                $skills_data[] = [
                    'skill_name'=>$_job_skill_name,
                    'job_level'=>$_job_skill_level,
                    'job_level_text'=>$config_skill_data[$_job_skill_level]['text'],
                    'resume_level'=>0,
                    'resume_level_text'=>'无',
                    'sroce'=>0,
                ];
            }
        }
        //以后无用的技能匹配代码
        $skills_score = (
            $necessary_skills_score*$resumeGradeArr['necessary_skills']/100 +
            $optional_skills_score*$resumeGradeArr['optional_skills']/100
        );
        $score = (int)(
            (
                $education_score*$resumeGradeArr['education']/100
                + $working_years_score*$resumeGradeArr['working_years']/100)*$resumeGradeArr['user_info']/100
            + $skills_score*$resumeGradeArr['skills']/100
        );
        return compact('score', 'education_score', 'working_years_score', 'skills_data'
            , 'necessary_skills_data', 'optional_skills_data', 'skills_score', 'necessary_skills_score', 'optional_skills_score');
    }

    public function handleUpdateAt($recruitResume)
    {
        $updateAt = null;
        foreach ($recruitResume->logs as $log) {
            if(!$updateAt || moreTime($log->created_at, $updateAt)){
                $updateAt = $log->created_at;
            }
        }
        $recruitResume->updated_at = $updateAt;
        $recruitResume->save();
    }
}
