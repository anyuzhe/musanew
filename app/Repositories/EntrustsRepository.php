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
//            $model = $model->whereIn('status', [1])->where('third_party_id', $company->id);
            $model = $model->where('third_party_id', $company->id);
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
            $_status = 4;
        }elseif($_status==-1){
            $_status = 4;
        }elseif($_status==-2){
            $_status = 4;
        }else{
            $_status = $recruit_status;
        }
        return $_status;
    }
}
