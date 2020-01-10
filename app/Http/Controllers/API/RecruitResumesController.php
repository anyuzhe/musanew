<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Models\UserBasicInfo;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecruitResumesController extends ApiBaseCommonController
{
    public $model_name = RecruitResume::class;
    public $recruitResumesRepository;
    public $resumeRepository;
    public $search_field_array = [
        ['third_party_id','='],
    ];

    public function __construct(Request $request, ResumesRepository $resumesRepository, RecruitResumesRepository $recruitResumesRepository)
    {
        parent::__construct($request);
        $this->resumeRepository = $resumesRepository;
        $this->recruitResumesRepository = $recruitResumesRepository;
    }


    public function authLimit(&$model)
    {
        $recruit_id = $this->request->get('recruit_id');
        $entrust_id = $this->request->get('entrust_id');
        if ($recruit_id) {
            $model = $model->where('company_job_recruit_id', $recruit_id);
        }
        if ($entrust_id) {
//            $model = $model->where('company_job_recruit_entrust_id', $entrust_id);
            $model = $model->where(function ($query)use($entrust_id){
                $query->where('company_job_recruit_entrust_id',$entrust_id)->orWhereNull('company_job_recruit_entrust_id');
            });
        }
        return null;
    }

    public function personnel(Request $request)
    {
        $company = $this->getCurrentCompany();
        $type = $request->get('type');
        $third_party_id = $request->get('third_party_id');
        if($third_party_id){
            if($type==1){
                $userIds = RecruitResume::where('company_id', $company->id)->where('third_party_id', $third_party_id)->pluck('creator_id')->unique();
            }else{
                $userIds = Entrust::where('company_id', $company->id)->where('third_party_id', $third_party_id)->pluck('leading_id')->unique();
            }
        }else{
            if($type==1){
                $userIds = RecruitResume::where('company_id', $company->id)->pluck('creator_id')->unique();
            }else{
                $userIds = Recruit::where('company_id', $company->id)->pluck('leading_id')->unique();
            }

        }
        $users = UserBasicInfo::whereIn('user_id', $userIds)->get();
        return $this->apiReturnJson(0, $users);
    }

    public function _after_get(&$data)
    {
        $data->load('resume');
        $data->load('thirdParty');
        $data->load('company');

        //已被其他公司录用 查询简历id
        $_resumeIds = $data->pluck('resume_id')->toArray();
        $_recruitResumeIds = $data->pluck('id')->toArray();
        $_resumeHireIds = RecruitResume::whereIn('resume_id', $_resumeIds)->whereNotIn('id',$_recruitResumeIds)->where('status','>=',7)
            ->pluck('resume_id')->toArray();

        $_blacklist_resume_ids = CompanyResume::where('company_id', $this->getCurrentCompany()->id)->where('type', 3)->pluck('resume_id')->toArray();
        $has_loos_ids = $this->getCurrentCompany()->looks()->where('user_id', $this->getUser()->id)->pluck('company_job_recruit_resume_id')->toArray();
        foreach ($data as &$v) {
            $v->resume =  app()->build(ResumesRepository::class)->getData($v->resume);
            $this->recruitResumesRepository->addFieldText($v);
            if(in_array($v->id, $has_loos_ids)){
                $v->have_look = 1;
            }else{
                $v->have_look = 0;
            }

            if(in_array($v->resume->id, $_blacklist_resume_ids)){
                $v->in_blacklist = 1;
                $v->resume->in_blacklist = 1;
            }else{
                $v->in_blacklist = 0;
                $v->resume->in_blacklist = 0;
            }
            if(in_array($v->resume_id, $_resumeHireIds)){
                $v->is_other_hired = 1;
            }else{
                $v->is_other_hired = 0;
            }
        }
        return $data;
    }

    public function _after_find(&$data)
    {
        $data->thirdParty;
        $data->company;
        $data->company->logo_url = getCompanyLogo($data->company->logo);
        $this->recruitResumesRepository->addFieldText($data);
        $data->resume = app()->build(ResumesRepository::class)->getData($data->resume);
        $data->logs->load('creatorInfo');
        foreach ($data->logs as &$log) {
            if($log->creatorInfo->avatar){
                $log->creatorInfo->avatar_url = getPicFullUrl($log->creatorInfo->avatar);
            }else{
                $log->creatorInfo->avatar_url = "";
            }
        }
        if($this->getCurrentCompany())
            $this->recruitResumesRepository->haveLook($data);
        $data->matching = $this->recruitResumesRepository->matching($data);
        $data = $data->toArray();
        $data['logs'] = array_reverse($data['logs']);


        $resume = $data['resume'];
        $resume['educations'] = (new Collection($resume['educations']))->sortByDesc('start_date')->values()->toArray();
        $resume['projects'] = (new Collection($resume['projects']))->sortByDesc('project_start')->values()->toArray();
        $resume['companies'] = (new Collection($resume['companies']))->sortByDesc('job_start')->values()->toArray();
        $data['resume'] = $resume;
    }

    public function resumeFlow()
    {
        $feedback = $this->request->get('feedback');
        $interviewer = $this->request->get('interviewer');
        $date = $this->request->get('date');
        $id = $this->request->get('id');
        $status = $this->request->get('status');

        $recruitResume = RecruitResume::find($id);
        if($this->getCurrentCompany())
            $this->recruitResumesRepository->haveLook($recruitResume);
        $checkMsg = $this->recruitResumesRepository->checkFlow($recruitResume,$status,$feedback?$feedback:$date);
        if($checkMsg)
            return $this->apiReturnJson(9999, null, $checkMsg);
        $this->recruitResumesRepository->generateLog($recruitResume,$status,$this->getCurrentCompany(), $feedback?$feedback:$date,1, $interviewer);
        return $this->apiReturnJson(0);
    }

    public function userRecruitList(Request $request)
    {
        $model = $this->getModel();
        $model = $model->orderBy('updated_at','desc');
        $user = $this->getUser();
        $hasResumeIds = Resume::where('user_id', $user->id)->pluck('id')->toArray();
        $model = $model->whereIn('resume_id', $hasResumeIds);
        $is_end = $request->get('is_end',null);
        if($is_end!==null){
            if($is_end){
                $model = $model->whereIn('status',[-5,-4,-3,-2,-1,8]);
            }else{
                $model = $model->whereIn('status',[1,2,3,4,5,6,7]);
            }
        }
        $model_data = clone $model;
        $count = $model->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
            'collectionGetLoads'
        ],$model_data);

        $list->load('resume');
        $list->load('thirdParty');
        $list->load('company');
        $list->load('job');

        $job_ids = $list->pluck('job_id')->toArray();
        foreach ($list as &$v) {
            $this->recruitResumesRepository->addFieldText($v, true);
        }


        $recruits = $list->toArray();

        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($recruits as &$recruit) {
            $recruit['job'] = $jobs[$recruit['job']['id']];
        }

        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;

        return $this->apiReturnJson(0, $recruits,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
//        return responseZK(1,$list,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }
}
