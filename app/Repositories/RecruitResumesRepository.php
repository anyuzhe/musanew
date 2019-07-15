<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\RecruitResumeLook;
use App\Models\Resume;

class RecruitResumesRepository
{
    public function checkFlow($recruitResume,$status,$data)
    {
        if($status==2 && !in_array($recruitResume->status,[1])){
            return '简历不是初始状态,不能邀请面试';
        }elseif($status==3 && !in_array($recruitResume->status,[2,3,5])){
            return '简历不是邀请或再次邀请面试状态,不能修改时间';
        }elseif($status==4 && !in_array($recruitResume->status,[2,3,5])){
            return '简历不是邀请或再次邀请面试状态,不能面试完成';
        }elseif($status==5 && !in_array($recruitResume->status,[4])){
            return '简历不是邀请面试状态或者完成状态,不能再次邀请面试';
        }elseif($status==6 && !in_array($recruitResume->status,[4])){
            return '简历不是面试完成状态,不能录用';
        }elseif($status==7 && !in_array($recruitResume->status,[6])){
            return '简历不是录用状态,不能成功入职';
        }

        if($status==-1 && !in_array($recruitResume->status,[1])){
            return '简历不是初始状态,不能简历不匹配';
        }elseif($status==-2 && !in_array($recruitResume->status,[2,3,5])){
            return '简历不是邀请或再次邀请面试状态,不能面试没来';
        }elseif($status==-3 && !in_array($recruitResume->status,[4])){
            return '简历不是面试完成状态,不能面试不通过';
        }elseif($status==-4 && !in_array($recruitResume->status,[4])){
            return '简历不是面试完成状态,不能面试通过但不合适';
        }elseif($status==-5 && !in_array($recruitResume->status,[6])){
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

    public function generateLog(RecruitResume $recruitResume, $status, $company, $otherData='', $type=1)
    {
//        -5 录用之后未到岗
//    -4 面试通过但不合适
//    -3 面试不通过
//    -2 面试没来
//    -1 简历不匹配
//1 简历投递
//2 邀请面试 可以修改面试时间再次邀约
//3 修改时间
//4 面试完成(填写反馈后-待定状态)
//5 再次邀请面试
//6 录用
//7 成功入职
        global $LOGIN_USER;
        $log = new RecruitResumeLog();
        $log->user_id = $LOGIN_USER->id;
        if(!$company){
            $company = $LOGIN_USER->company->first();
            if(!$company)
                return;
        }
        $log->company_id = $company->id;
        $log->status = $status;
        $log->resume_id = $recruitResume->resume_id;
        $log->other_data = $otherData;
        if($status==1){
            if($type==1){
                $log->text = $company->company_alias.' 添加简历';
            }else{
                $log->text =  $LOGIN_USER->info->realname.' 投递简历';
            }
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
            $log->text =  '录用';
        }elseif($status==7){
            $log->text =  '成功入职';
            $this->hiredEntryHandle($recruitResume);
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
        $recruitResume->logs()->save($log);
        $recruitResume->status = $status;
        $recruitResume->save();
    }

    public function haveLook(RecruitResume $recruitResume)
    {
        $has = RecruitResumeLook::where('company_job_recruit_resume_id',$recruitResume->id)->where('company_id',TokenRepository::getCurrentCompany()->id)->first();
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
        $recruit = $recruitResume->recruit;
        $entrust = $recruitResume->entrust;
        $resume = $recruitResume->resume;
        $recruit->done_num++;
        if($recruit->done_num>=$recruit->need_num){
            //如果是委托 就不改成结束状态
            if(!$entrust)
                $recruit->satus = 4;
            foreach ($recruit->entrusts as $_entrust) {
                $_entrust->status = 2;
                $_entrust->save();
            }
        }
        $recruit->save();
        if($entrust){
            $entrust->done_num++;
            if($recruit->done_num>=$recruit->need_num){
                $entrust->status = 2;
            }
            $entrust->save();
        }
        $resume->company_id = $recruit->company_id;
        $resume->in_job = 1;
        $resume->save();
    }

    public function minusNewResumeHandle(RecruitResume $recruitResume)
    {
        $recruit = $recruitResume->recruit;
        $entrust = $recruitResume->entrust;
        $recruit->new_resume_num--;
        $recruit->save();
        if($entrust){
            $entrust->new_resume_num--;
            $entrust->save();
        }
    }

    public function addFieldText(&$data)
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
                $data->status_str = '简历投递';
                break;
            case 2:
                $data->status_str = '邀请面试';
                break;
            case 3:
                $data->status_str = '面试完成';
                break;
            case 4:
                $data->status_str = '再次邀请面试';
                break;
            case 5:
                $data->status_str = '录用';
                break;
            case 6:
                $data->status_str = '成功入职';
                break;
            default:
                $data->status_str = '未知状态';
                break;

        }
        switch ($data->resume_source){
            case 1:
                $data->resume_source_str = "外包({$data->thirdParty->company_alias})";
                break;
            case 2:
                $data->resume_source_str = '个人投递';
                break;
            default:
                $data->resume_source_str = '来源未知';
                break;

        }
    }
}
