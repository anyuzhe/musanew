<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\ExtendMap;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntrustResumesController extends ApiBaseCommonController
{
    public $model_name = Resume::class;
    public $resumeRepository;
    public $recruitResumesRepository;
    public $search_field_array = [
      ['name','like'],
    ];

    public function __construct(Request $request, ResumesRepository $resumesRepository,RecruitResumesRepository $recruitResumesRepository)
    {
        parent::__construct($request);
        $this->resumeRepository = $resumesRepository;
        $this->recruitResumesRepository = $recruitResumesRepository;
    }


    public function storeValidate()
    {
        return [
            [
                'hope_salary_min' => 'numeric|required|min:1|lt:hope_salary_max',
                'hope_salary_max' => 'numeric|required|min:1',
            ],
            [
                'hope_salary_min.lt'=>'期望薪资最少必须小于最大值',
                'hope_salary_min.min'=>'期望薪资最少必须大于0',
//                'hope_salary_min.max'=>'期望薪资最少必须小于99999',
                'hope_salary_max.min'=>'期望薪资最大必须大于0',
//                'hope_salary_max.max'=>'期望薪资最大必须小于99999',
            ]
        ];
    }

    public function authLimit(&$model)
    {
        $recruit_id = $this->request->get('recruit_id');
        $entrust_id = $this->request->get('entrust_id');
        $in_job = $this->request->get('in_job');
        $model = $model->where('status','!=',-1);
        $company = $this->getCurrentCompany();
        if ($company) {
            $model = $model->whereIn('id', $company->resumes()->pluck('resume_id')->toArray());
        }else{
            $model = $model->where('id', 0);
        }
        if($in_job || is_numeric($in_job)){
            $model = $model->where('in_job', $in_job);
        }
        if($entrust_id){
            $model = $model->whereNotIn('id', RecruitResume::where('company_job_recruit_entrust_id', $entrust_id)->pluck('resume_id')->toArray());
        }elseif ($recruit_id){
            $model = $model->whereNotIn('id', RecruitResume::where('company_job_recruit_id', $entrust_id)->pluck('resume_id')->toArray());
        }
        return null;
    }

    public function afterStore($obj, $data)
    {
        $id = $obj->id;
        $obj->creator_id = $this->getUser()->id;
        $obj->type = 1;
        $company = $this->getCurrentCompany();
        $company->resumes()->attach($id);
        $this->resumeRepository->saveDataForForm($obj, $data);
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data)
    {
        $obj = Resume::find($id);
//        $company = $this->getCurrentCompany();
//        $company->resumes()->attach($id);

        $this->resumeRepository->saveDataForForm($obj, $data);
        return $this->apiReturnJson(0);
    }

    public function sendResumes()
    {
        $data = $this->request->all();
        $ids = $data['resume_ids'];
        $recruit_id = null;
        if(isset($data['recruit_id'])){
            $recruit_id = $data['recruit_id'];
        }
        $entrust_id = null;
        if(isset($data['entrust_id'])){
            $entrust_id = $data['entrust_id'];
        }
        $recruit = Recruit::find($recruit_id);
        $entrust = Entrust::find($entrust_id);

        if(!$recruit_id || !$recruit){
            return $this->apiReturnJson(9999, null, '缺少招聘信息');
        }
        if(!$entrust_id || !$entrust){
            return $this->apiReturnJson(9999, null, '缺少委托信息');
        }

        app('db')->beginTransaction();

        foreach ($ids as $id) {
            $resume = Resume::find($id);
            $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                ->where('resume_id', $id)
                ->where('company_job_recruit_entrust_id', $entrust_id)->first();
            if($resume->in_job==1){
                app('db')->rollBack();
                return $this->apiReturnJson(9999, null, $resume->name.'已入职，无法添加');
            }
            if($has){
                app('db')->rollBack();
                return $this->apiReturnJson(9999, null, $resume->name.'已投递，无法重复添加');
            }

            $recruitResume = RecruitResume::create([
                'company_id'=>$recruit->company_id,
                'third_party_id'=>$entrust->third_party_id,
                'job_id'=>$recruit->job_id,
                'resume_id'=>$id,
                'company_job_recruit_id'=>$recruit->id,
                'company_job_recruit_entrust_id'=>$entrust_id,
                'status'=>1,
                'resume_source'=>$resume->type,
                'creator_id'=>$this->getUser()->id,
            ]);
            $this->recruitResumesRepository->haveLook($recruitResume);
            if($entrust_id && $entrust){
                $this->recruitResumesRepository->generateLog($recruitResume,1,$entrust->thirdParty, null,1);
                $recruit->resume_num++;
                $recruit->new_resume_num++;
                $recruit->save();

                $entrust->resume_num++;
                $entrust->new_resume_num++;
                $entrust->save();

            }else{
                //暂无
            }
        }

        app('db')->commit();
        return $this->apiReturnJson(0);
    }

    public function _after_get(&$data)
    {
        return app()->build(ResumesRepository::class)->getListData($data);
    }

    public function _after_find(&$data)
    {
        $data = app()->build(ResumesRepository::class)->getData($data);
    }

    public function destroy($id)
    {
        $model = Resume::find($id);
        $model->status = -1;
        $model->save();
        return responseZK(0);
    }

}
