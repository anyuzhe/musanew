<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Repositories\AreaRepository;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Support\Facades\Log;

class CompaniesController extends ApiBaseCommonController
{
    protected $model_name = Company::class;

    public function thirdPartyList()
    {
        $this->requireMoodleConfig();

        $company = $this->getCurrentCompany();

        $thirdParty = $company->thirdParty();
        $model_data = clone $thirdParty;
        $count = $thirdParty->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
        ],$model_data);

        ## 招聘完成率 recruitFinishingRate
        ## 招聘成功率 recruitSuccessRate
        ## 累计负责岗位数量 allJobCount
        ## 负责本企业岗位数量 ourJobCount
        ## 当前进行招聘岗位数量 currentRecruitCount

//        招聘完成率
//文本
//增加职位简历资料并转转交还需求方的职位数量/总职位数量
//招聘成功率
//文本
//需求方采纳第三方公司提交的简历/总职位数量
//累计负责职位数量
//文本
//该第三方企业同意接受的所有职位数量
//负责本企业职位数量
//文本
//该第三方企业同意接受的本企业所有职位数量
//当前进行招聘职位数量
//文本
//该第三方企业正在为本企业招聘的职位数量

        foreach ($list as &$v) {
            $_ids1 = Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->pluck('id')->toArray();
            $_finish_count = RecruitResume::where('company_job_recruit_entrust_id', $_ids1)->whereNotIn('status',[1,-1])->count();
            $_success_count = RecruitResume::where('company_job_recruit_entrust_id', $_ids1)->whereNotIn('status',[6])->count();
            $_all_count = Recruit::whereIn('id', Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->pluck('company_job_recruit_id')->toArray())->sum('need_num');
            $v->recruitFinishingRate = floor($_finish_count/$_all_count*100, 2);
            $v->recruitSuccessRate = floor($_success_count/$_all_count*100, 2);
            $v->allJobCount = Entrust::where('third_party_id',$v->id)->count();
            $v->ourJobCount = Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->count();
            $v->currentRecruitCount = Entrust::where('third_party_id',$v->id)->where('company_id',$company->id)->whereIn('status', [1])->count();

            $v->logo_url = getMoodlePICURL($v->logo);
        }
        unset($v);

        $pageSize = app('request')->get('pageSize',3);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;

        return $this->apiReturnJson(0, $list,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }

    public function thirdPartyListIdName()
    {
        $company = $this->getCurrentCompany();
        $thirdParty = $company->thirdParty;
        $arr = [];
        foreach ($thirdParty as $key=>$item) {
            $_arr = [];
            $_arr['id'] = $item['id'];
            $_arr['name'] = $item['company_name'];
            $arr[] = $_arr;
        }
        return $this->apiReturnJson(0, $arr);
    }

    public function entrustsList()
    {
        $this->requireMoodleConfig();
        $jobRes = app()->build(JobsRepository::class);

        $company = $this->getCurrentCompany();

        $type = 4;

        $model = app()->build(EntrustsRepository::class)->getModelByType($type, $company);
        $company_ids = $model->pluck('company_id')->toArray();
        $entrust_ids = $model->pluck('id')->toArray();
        $companyModel = Company::whereIn('id', $company_ids);


        $model_data = clone $companyModel;
        $count = $companyModel->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
        ],$model_data);
        if($type==2){
            $list->load(['requirements' => function ($query)use($entrust_ids) {
                $query->whereIn('id', $entrust_ids);
            }]);
        }elseif ($type==3){
            $list->load(['requirements' => function ($query)use($entrust_ids) {
                $query->whereIn('id', $entrust_ids);
            }]);
        }elseif ($type==4){
            $list->load(['entrusts' => function ($query)use($entrust_ids) {
                $query->whereIn('id', $entrust_ids);
            }]);
        }
        $newList = [];
        foreach ($list as &$v) {
            if($type==2){
                $entrusts = $v->requirements;
            }elseif ($type==3){
                $entrusts = $v->requirements;
            }elseif ($type==4){
                $entrusts = $v->entrusts;
            }else{
                continue;
            }

            $v->logo_url = getMoodlePICURL($v->logo);

            $entrusts->load('job');
            $entrusts->load('recruit');

            $item = $v->toArray();

            $job_ids = [];
            $entrusts = $entrusts->toArray();
            foreach ($entrusts as $entrust) {
                $job_ids[] = $entrust['job']['id'];
            }
            $jobs = $jobRes->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
            foreach ($entrusts as &$entrust) {
                $entrust['job'] = $jobs[$entrust['job']['id']];
                $entrust['need_num'] = $entrust['recruit']['need_num'];
                $entrust['recruit_id'] = $entrust['recruit']['id'];
            }
            unset($entrust);

            if($type==2){
                $item['entrusts'] = $entrusts;
            }elseif ($type==3){
                $item['entrusts'] = $entrusts;
                unset($item['requirements']);
                unset($item['recruit']);
            }elseif ($type==4){
                $item['entrusts'] = $entrusts;
                unset($item['requirements']);
                unset($item['recruit']);
            }else{
                continue;
            }
            $newList[] = $item;
        }
        unset($v);

        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;
        return $this->apiReturnJson(0, $newList,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }
}
