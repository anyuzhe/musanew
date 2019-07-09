<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\ZL\Controllers\ApiBaseCommonController;

class EntrustsController extends ApiBaseCommonController
{
    protected $model_name = Entrust::class;
//}elseif($type==2){
//    //外包出去的
//$company_job_recruit_ids = app('db')->table('company_job_recruit_entrust')->whereIn('status', [2,3]);
//->where('company_id', $company->id)->pluck('company_job_recruit_id');
//$model = $model->whereIn('id', $company_job_recruit_ids);
//}elseif($type==3){
//    //作为外包公司
//$company_job_recruit_ids = app('db')->table('company_job_recruit_entrust')
//    ->where('third_party_id', $company->id)->pluck('company_job_recruit_id');
//$model = $model->whereIn('id', $company_job_recruit_ids)->where('status', 3);

    public function authLimit(&$model)
    {
        $user = $this->getUser();
        if ($user) {
            $company = $this->getCurrentCompany();
            $type = $this->request->type;
            $model = app()->build(EntrustsRepository::class)->getModelByType($type, $company);
        }
        return null;
    }

    public function _after_get(&$entrusts)
    {
        $entrusts->load('job');
        $entrusts->load('recruit');
        $entrusts->load('company');
        $entrusts->load('thirdParty');

        $entrustRes = app()->build(EntrustsRepository::class);
        $job_ids = [];
        $entrusts = $entrusts->toArray();
        foreach ($entrusts as $entrust) {
            $job_ids[] = $entrust['job']['id'];
        }
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($entrusts as &$entrust) {
            $entrust['job'] = $jobs[$entrust['job']['id']];
            $entrust['need_num'] = $entrust['recruit']['need_num'];
            $entrust['recruit_id'] = $entrust['recruit']['id'];
            $entrust['status'] = $entrustRes->getStatusByEntrustAndRecruit($entrust['status'],$entrust['recruit']['status']);
        }
        return $entrusts;
    }

    public function _after_find(&$data)
    {
        $data->need_num = $data->recruit->need_num;
        $data->need_num = app()->build(EntrustsRepository::class)->getStatusByEntrustAndRecruit($data->status, $data->recruit->status);
        $data->company;
        $data->thirdParty;
        $data->job = app()->build(JobsRepository::class)->getData($data->job);
    }

    public function applyEntrust()
    {
        $company_job_recruit_id = $this->request->get('company_job_recruit_id');
        $recruit = Recruit::find($company_job_recruit_id);
        $recruit->status = 2;
        $recruit->save();
        $third_party_ids = $this->request->get('third_party_ids');
        $thirdPartyIds = $this->getCurrentCompany()->thirdParty->pluck('id')->toArray();
        if(is_array($third_party_ids)){
            foreach ($third_party_ids as $third_party_id) {
                if(in_array($third_party_id, $thirdPartyIds)){
                    Entrust::create([
                        'job_id'=>$recruit->job_id,
                        'company_id'=>$recruit->company_id,
                        'third_party_id'=>$third_party_id,
                        'company_job_recruit_id'=>$company_job_recruit_id,
                        'done_num'=>0,
                        'resume_num'=>0,
                        'new_resume_num'=>0,
                        'status'=>0,
                        'creator_id'=>$this->getUser()->id,
                    ]);
                }
            }
        }
        return $this->apiReturnJson(0);
    }

    public function cancelEntrust()
    {
        $id = $this->request->get('id');
        $entrust = Entrust::find($id);
        $recruit = $entrust->recruit;
        if($recruit->status==2){
            //假如委托没有进行中的了 才会变成1
            if(!Entrust::where('company_job_recruit_id', $recruit->id)->whereIn('status',[0,1])->first()){
                $recruit->status = 1;
                $recruit->save();
            }

            $entrust->status = -1;
            $entrust->save();
            return $this->apiReturnJson(0);
        }else{
            return $this->apiReturnJson(9999);
        }
    }

    public function acceptEntrust()
    {
        $id = $this->request->get('id');
        $entrust = Entrust::find($id);
        $recruit = $entrust->recruit;
        if($recruit->status==2){
            $recruit->status = 3;
            $recruit->save();

            $entrust->status = 1;
            $entrust->save();
            return $this->apiReturnJson(0);
        }else{
            return $this->apiReturnJson(9999);
        }
    }

    public function rejectEntrust()
    {
        $id = $this->request->get('id');
        $entrust = Entrust::find($id);
        $recruit = $entrust->recruit;
        if($recruit->status==2){
            //假如委托没有进行中的了 才会变成1
            if(!Entrust::where('company_job_recruit_id', $recruit->id)->whereIn('status',[0,1])->first()){
                $recruit->status = 1;
                $recruit->save();
            }

            $entrust->status = -2;
            $entrust->save();
            return $this->apiReturnJson(0);
        }else{
            return $this->apiReturnJson(9999);
        }
    }

    public function returnEntrust()
    {
        $company_job_recruit_id = $this->request->get('company_job_recruit_id');
        $third_party_id = $this->request->get('third_party_id');
        $recruit = Recruit::find($company_job_recruit_id);
        if($recruit->status==3){
            $recruit->status = 1;
            $recruit->save();

            Entrust::where('company_job_recruit_id',$company_job_recruit_id)
                ->where('third_party_id', $third_party_id)->update([
                'status'=>-1
            ]);
            return $this->apiReturnJson(0);
        }else{
            return $this->apiReturnJson(9999);
        }
    }
}
