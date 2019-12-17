<?php

namespace App\Repositories;


use App\Models\Entrust;
use App\Models\RecruitResume;
use Illuminate\Support\Facades\DB;

class EntrustsRepository
{
    public function getModelByType($type, $company=null, $in_recruit=null, $resume_id=null, $model=null)
    {
        if(!$model)
            $model = new Entrust();
        //简历投递功能--过滤简历
        if($resume_id){
            $model = $model->whereNotIn('company_job_recruit_id', RecruitResume::where('resume_id', $resume_id)->pluck('company_job_recruit_id')->toArray());
        }
        if($type==2){
            //外包出去的
            if($company)
                $model = $model->where('company_id', $company->id);
            $model = $model->where('status', '!=', '-2');
            if($in_recruit){
                $model = $model->whereIn('status', [1]);
            }
        }elseif ($type==3){
            //作为外包公司
            if($company)
                $model = $model->where('third_party_id', $company->id);
            if($in_recruit){
                $model = $model->whereIn('status', [1]);
            }else{
                $model = $model->whereIn('status', [1,2, 6, 7,-1]);
            }
        }elseif ($type==4){
            //委托申请
            if($company)
                $model = $model->whereIn('status', [0])->where('third_party_id', $company->id);
        }
        return $model;
    }

    public function getStatusByEntrustAndRecruit($entrust_status,$recruit_status)
    {
        //recruit status 1招聘中 2等待外包公司审核 3外包中 4结束

        //entrust status 状态 -3 外包方未确定直接取消  -2 拒绝  -1 取消 0申请中 1正常 2完成
        $_status = $entrust_status;
        if($recruit_status==6 ||$recruit_status==7){
            $_status = 6;
        }elseif($_status==0){
            $_status = 2;
        }elseif($_status==1){
            $_status = 3;
        }elseif($_status==2){
            $_status = 5;
        }elseif($_status==-1){
            $_status = 4;
        }elseif($_status==-2){
            $_status = 4;
        }elseif($_status==-3){
            $_status = 4;
        }else{
            $_status = $recruit_status;
        }

        if($recruit_status==4)
            $_status = 4;
        return $_status;
    }

    public function getStatusTextByRecruitAndEntrust($recruit,$entrust=null)
    {
        $entrust_status = $entrust['status'];
        $recruit_status = $recruit['status'];
        //recruit status 1招聘中 2等待外包公司审核 3外包中 4结束
        //entrust status 状态 -2 拒绝  -1 取消 0申请中 1正常 2完成

        switch ($recruit_status){
            case 1:
                $status_text = '招聘中';
                break;
            case 2:
                $status_text = '等待外包公司审核';
                break;
            case 3:
                $status_text = '外包中';
                break;
            case 4:
                $status_text = '结束招聘';
                break;
            case 5:
                $status_text = '招聘完成';
                break;
            case 6:
                $status_text = '暂停招聘';
                return $status_text;
                break;
            case 7:
                $status_text = '暂停招聘';
                return $status_text;
                break;
            default:
                $status_text = '未知状态';
                break;
        }
        if(!$entrust)
            return $status_text;

        switch ($entrust_status){
            case -2:
                $status_text = $entrust['third_party']['company_alias'].' 拒绝委托招聘';
                break;
            case -3:
                $status_text = '主动取消委托招聘';
                break;
            case -1:
                $status_text = '结束招聘';
                break;
            case 0:
                $status_text = '等待外包公司审核';
                break;
            case 1:
                $status_text = '外包中';
                break;
            case 2:
                $status_text = '招聘完成';
                break;
            case 6:
                $status_text = '暂停招聘';
                break;
            case 7:
                $status_text = '暂停招聘';
                break;
            default:
                $status_text = '未知状态';
                break;
        }
        if($recruit_status==4)
            $status_text = '结束招聘';
        return $status_text;
    }

    public function getEntrustsAmount($entrusts)
    {
        $recruitIds = array_unique($entrusts->pluck('company_job_recruit_id')->toArray());
        return Entrust::select(DB::raw('company_job_recruit_id,SUM(done_num) as total_done_num,SUM(resume_num) as total_resume_num,SUM(new_resume_num) as total_new_resume_num'))->whereIn('company_job_recruit_id', $recruitIds)->groupBy('company_job_recruit_id')->get()->keyBy('company_job_recruit_id')->toArray();
    }
}
