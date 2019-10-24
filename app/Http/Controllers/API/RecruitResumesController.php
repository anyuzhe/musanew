<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;

class RecruitResumesController extends ApiBaseCommonController
{
    public $model_name = RecruitResume::class;
    public $recruitResumesRepository;
    public $resumeRepository;
    public $search_field_array = [
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
            $model = $model->where('company_job_recruit_entrust_id', $entrust_id);
        }
        return null;
    }

    public function _after_get(&$data)
    {
        $data->load('resume');
        $data->load('thirdParty');
        $data->load('company');

        //已被其他公司录用 查询简历id
        $_resumeIds = $data->pluck('resume_id')->toArray();
        $_recruitResumeIds = $data->pluck('id')->toArray();
        $_resumeHireIds = RecruitResume::whereIn('resume_id', $_resumeIds)->whereNotIn('id',$_recruitResumeIds)->where('status','>=',6)
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

            if(in_array($v->id, $_blacklist_resume_ids)){
                $v->in_blacklist = 1;
                $v->resume->in_blacklist = 1;
            }else{
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
        $data->company->logo_url = getPicFullUrl($data->company->logo);
        $this->recruitResumesRepository->addFieldText($data);
        $data->resume = app()->build(ResumesRepository::class)->getData($data->resume);
        $data->logs->load('creatorInfo');

        $this->recruitResumesRepository->haveLook($data);
        $data->matching = $this->recruitResumesRepository->matching($data);
        $data = $data->toArray();
        $data['logs'] = array_reverse($data['logs']);
    }

    public function resumeFlow()
    {
        $feedback = $this->request->get('feedback');
        $date = $this->request->get('date');
        $id = $this->request->get('id');
        $status = $this->request->get('status');

        $recruitResume = RecruitResume::find($id);
        $this->recruitResumesRepository->haveLook($recruitResume);
        $checkMsg = $this->recruitResumesRepository->checkFlow($recruitResume,$status,$feedback?$feedback:$date);
        if($checkMsg)
            return $this->apiReturnJson(9999, null, $checkMsg);
        $this->recruitResumesRepository->generateLog($recruitResume,$status,null, $feedback?$feedback:$date,1);
        return $this->apiReturnJson(0);
    }
}
