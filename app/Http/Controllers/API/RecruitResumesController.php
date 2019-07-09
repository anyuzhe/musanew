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
        foreach ($data as &$v) {
            $v->resume =  app()->build(ResumesRepository::class)->getData($v->resume);
            $this->recruitResumesRepository->addFieldText($v);
        }
        return $data;
    }

    public function _after_find(&$data)
    {
        $data->thirdParty;
        $this->recruitResumesRepository->addFieldText($data);
        $data->resume = app()->build(ResumesRepository::class)->getData($data->resume);
        $data->logs;
        $data->logs->load('creatorInfo');
    }

    public function resumeFlow()
    {
        $feedback = $this->request->get('feedback');
        $date = $this->request->get('date');
        $id = $this->request->get('id');
        $status = $this->request->get('status');

        $recruitResume = RecruitResume::find($id);
        $checkMsg = $this->recruitResumesRepository->checkFlow($recruitResume,$status,$feedback?$feedback:$date);
        if($checkMsg)
            return $this->apiReturnJson(0, null, $checkMsg);
        $this->recruitResumesRepository->generateLog($recruitResume,$status,null, $feedback?$feedback:$date,1);
        return $this->apiReturnJson(0);
    }
}
