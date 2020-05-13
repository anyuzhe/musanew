<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\CompanyResume;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
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
//        ['third_party_id','='],
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

    public function _after_get(&$data)
    {
        $data->load('resume');
        $data->load('thirdParty');
        $data->load('company');
        $data->load('logs');

        //已被其他公司录用 查询简历id
        $_resumeIds = $data->pluck('resume_id')->toArray();
        $_recruitResumeIds = $data->pluck('id')->toArray();
        $_resumeHireIds = RecruitResume::whereIn('resume_id', $_resumeIds)->whereNotIn('id',$_recruitResumeIds)->where('status','>=',7)
            ->pluck('resume_id')->toArray();
        $recruit = null;
        foreach ($data as &$v) {
            $v->resume =  app()->build(ResumesRepository::class)->getData($v->resume);
            $this->recruitResumesRepository->addFieldText($v);

            if(in_array($v->resume_id, $_resumeHireIds)){
                $v->is_other_hired = 1;
            }else{
                $v->is_other_hired = 0;
            }
            if(!$recruit)
                $recruit = $v->recruit;
            $v->has_time_error = !$this->checkError($v, $recruit)?1:0;
        }
        return $data;
    }

    protected function checkError($data, $recruit)
    {
        $recruit_created_at = $recruit->created_at;
        if(!moreTime($data->created_at, $recruit_created_at))
            return false;
        foreach ($data->logs as $log) {
            if(!moreTime($log->created_at, $recruit_created_at))
                return false;
            if(in_array($log->status,[2,3,5,7,8]) && $log->other_data && !moreTime($log->other_data, $recruit_created_at))
                return false;
        }
        return true;
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
                $log->creatorInfo->avatar_url = getAvatarFullUrl($log->creatorInfo->avatar);
            }else{
                $log->creatorInfo->avatar_url = "";
            }
        }
        $data->matching = $this->recruitResumesRepository->matching($data);
        $data = $data->toArray();
        $data['logs'] = array_reverse($data['logs']);


        $resume = $data['resume'];
        $resume['educations'] = (new Collection($resume['educations']))->sortByDesc('start_date')->values()->toArray();
        $resume['projects'] = (new Collection($resume['projects']))->sortByDesc('project_start')->values()->toArray();
        $resume['companies'] = (new Collection($resume['companies']))->sortByDesc('job_start')->values()->toArray();
        $data['resume'] = $resume;
    }

    public function updateLog($id, Request $request)
    {
        $log = $request->all();
        $logObj = RecruitResumeLog::find($id);
        $logObj->fill($log);
        $recruitResume = $logObj->recruitResume;
        if(!isset($log['status']))
            $log['status'] = $logObj->status;

        if($log['status']==1){
            if(isset($log['user_id']) && $log['user_id'])
                $recruitResume->creator_id = $log['user_id'];
            if(isset($log['created_at']) && $log['created_at'])
                $recruitResume->created_at = $log['created_at'];
        }elseif($log['status']==2){
            $logObj->text =  '邀请面试-'.$log['other_data'];
        }elseif($log['status']==3){
            $logObj->text =  '修改面试时间-'.$log['other_data'];
        }elseif($log['status']==5){
            $logObj->text =  '再次邀请面试-'.$log['other_data'];
        }elseif($log['status']==7){
            $logObj->text =  '录用-计划入职时间:'.$log['other_data'];
        }elseif($log['status']==8){
            $logObj->text =  '成功入职-'.$log['other_data'];
            $recruitResume->formal_entry_at = $log['other_data'];
        }
        $logObj->save();
        $recruitResume->save();
        $this->recruitResumesRepository->handleUpdateAt($recruitResume);
        return $this->apiReturnJson(0, $logObj);
    }


    public function resumeFlow()
    {
        $feedback = $this->request->get('feedback');
        $date = $this->request->get('date');
        $id = $this->request->get('id');
        $status = $this->request->get('status');

        $recruitResume = RecruitResume::find($id);
        if($this->getCurrentCompany())
            $this->recruitResumesRepository->haveLook($recruitResume);
        $checkMsg = $this->recruitResumesRepository->checkFlow($recruitResume,$status,$feedback?$feedback:$date);
        if($checkMsg)
            return $this->apiReturnJson(9999, null, $checkMsg);
        $this->recruitResumesRepository->generateLog($recruitResume,$status,$this->getCurrentCompany(), $feedback?$feedback:$date,1);
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



    public function getUsers($id)
    {
        $recruitResumes = RecruitResume::find($id);
        $companyUsers = CompanyUser::whereIn('company_id', [$recruitResumes->company_id, $recruitResumes->third_party_id])->get();
        $userIds = $companyUsers->pluck('user_id')->unique()->toArray();
        $roleIds = $companyUsers->pluck('company_role_id')->toArray();
        $users = \App\Models\User::whereIn('id', $userIds)->get();
        $users->load('info');
        $users = $users->keyBy('id')->toArray();
        $roles = CompanyRole::whereIn('id', $roleIds)->get()->keyBy('id')->toArray();
        $data = [];
        $hasIds = [];
        foreach ($companyUsers as $companyUser) {
            $user = $users[$companyUser->user_id];
            if(isset($roles[$companyUser->company_role_id]))
                $role = $roles[$companyUser->company_role_id];
            else
                $role = null;
            $info = $user['info'];
            if(in_array($user['id'], $hasIds))
                continue;
            $data[] = [
                'id'=>$user['id'],
                'name'=>$info?$info['realname']:'无姓名',
                'role_name'=>$role?$role['name']:'无角色',
            ];
            $hasIds[] = $user['id'];
        }
        return $this->apiReturnJson(0,$data);
    }

    public function afterUpdate($id,$data)
    {
        $obj = $this->getModel()->find($id);
        $this->recruitResumesRepository->handleUpdateAt($obj);
        return $this->apiReturnJson(0,$obj);
    }
}
