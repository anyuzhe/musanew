<?php

namespace App\Repositories;


use App\Models\Entrust;

class EntrustsRepository
{
    public function getModelByType($type, $company)
    {
        $model = new Entrust();
        if($type==2){
            //外包出去的
            $model = $model->where('company_id', $company->id);
//            $model = $model->whereIn('status', [1,0])->where('company_id', $company->id);
        }elseif ($type==3){
            //作为外包公司
            $model = $model->whereIn('status', [1,2,-1])->where('third_party_id', $company->id);
//            $model = $model->where('third_party_id', $company->id);
        }elseif ($type==4){
            //委托申请
            $model = $model->whereIn('status', [0])->where('third_party_id', $company->id);
        }
        return $model;
    }

    public function getStatusByEntrustAndRecruit($entrust_status,$recruit_status)
    {
        //recruit status 1招聘中 2等待外包公司审核 3外包中 4结束

        //entrust status 状态 -2 拒绝  -1 取消 0申请中 1正常 2完成
        $_status = $entrust_status;
        if($_status==0){
            $_status = 2;
        }elseif($_status==1){
            $_status = 3;
        }elseif($_status==2){
            $_status = 5;
        }elseif($_status==-1){
            $_status = 4;
        }elseif($_status==-2){
            $_status = 4;
        }else{
            $_status = $recruit_status;
        }
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
                $status_text = '招聘取消';
                break;
            case 5:
                $status_text = '招聘完成';
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
            case -1:
                $status_text = '招聘取消';
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
            default:
                $status_text = '未知状态';
                break;
        }
        return $status_text;
    }
}
