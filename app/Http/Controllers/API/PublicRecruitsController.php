<?php

namespace App\Http\Controllers\API;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Repositories\EntrustsRepository;
use App\Repositories\JobsRepository;
use App\Repositories\RecruitRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Job;

class PublicRecruitsController extends ApiBaseCommonController
{
    use SoftDeletes;

    public $model_name = Recruit::class;

    public function getModel()
    {
        $educational_requirements = $this->request->get('educational_requirements', null);
        $work_nature = $this->request->get('work_nature', null);
        $working_years = $this->request->get('working_years', null);
        $city = $this->request->get('city', null);
        $text = $this->request->get('text', null);

        $job_model = new Job();
        if($educational_requirements){
            $job_model = $job_model->where('educational_requirements', $educational_requirements);
        }
        if($work_nature){
            $job_model = $job_model->where('work_nature', $work_nature);
        }
        if($working_years){
            $job_model = $job_model->where('working_years', $working_years);
        }
        if($city){
            $areaIds = Area::where('fname','like',"%$city%")->where('level', 2)->pluck('id')->toArray();
            $addressIds = CompanyAddress::whereIn('city_id', $areaIds)->pluck('id')->toArray();
            $job_model = $job_model->whereIn('address_id', $addressIds);
        }
        $searchPublicJobIds = $job_model->pluck('id')->toArray();

        $select1 = DB::raw('id, company_id, job_id, id as company_job_recruit_id, 0, need_num, done_num, resume_num, leading_id, created_at');
        $select2 = DB::raw('id, third_party_id, job_id, company_job_recruit_id, id as company_job_recruit_entrust_id, "need_num", done_num, resume_num, leading_id, created_at');
        if($text){
            $companyIds = Company::where('company_alias', 'like', "%$text%")->orWhere('company_name', 'like', "%$text%")->pluck('id')->toArray();
            $jobIds = Job::where('name', 'like', "%$text%")->pluck('id')->toArray();

            $job_model = $job_model->where(function ($query)use ($companyIds, $jobIds){
                $query->whereIn('company_id', $companyIds)
                    ->orWhereIn('id', $jobIds);
            });
            $searchJobIds = $job_model->pluck('id')->toArray();

            //招聘
            $recruit = DB::connection('musa')->table('company_job_recruit')
                ->select($select1)
                ->where('status', 1)
                ->whereIn('job_id', $searchPublicJobIds)
                ->whereIn('job_id', $searchJobIds);

            //委托
            $model = DB::connection('musa')->table('company_job_recruit_entrust')
                ->select($select2)
                ->where('status', 1)
                ->whereIn('job_id', $searchPublicJobIds)
                ->where(function ($query)use($companyIds, $jobIds){
                    $query->whereIn('third_party_id', $companyIds)->orWhereIn('job_id', $jobIds);
                })
                ->union($recruit);
        }else{
            $recruit = DB::connection('musa')->table('company_job_recruit')
                ->select($select1)
                ->where('status', 1)
                ->whereIn('job_id', $searchPublicJobIds);

            $model = DB::connection('musa')->table('company_job_recruit_entrust')
                ->select($select2)
                ->where('status', 1)
                ->whereIn('job_id', $searchPublicJobIds)
                ->union($recruit);
        }

        return $model;
    }

    public function _after_get(&$recruits)
    {
        dd($recruits->toarray());
        getObjRelationBelongsTo($recruits, 'recruit', new Recruit(),'company_job_recruit_id');
        getObjRelationBelongsTo($recruits, 'entrust', new Entrust(),'company_job_recruit_entrust_id');
        dd($recruits);
        $recruits->load('job');
        $recruits->load('leading');

        $entrustRes = app()->build(EntrustsRepository::class);

        $job_ids = [];
        $recruits = $recruits->toArray();
        foreach ($recruits as $recruit) {
            $job_ids[] = $recruit['job']['id'];
        }
        $jobs = app()->build(JobsRepository::class)->getListData(Job::whereIn('id', $job_ids)->get())->keyBy('id')->toArray();
        foreach ($recruits as &$recruit) {
            $recruit['job'] = $jobs[$recruit['job']['id']];
            $recruit['residue_num'] = $recruit['need_num'] - $recruit['done_num'] - $recruit['wait_entry_num'];
            $recruit['residue_num'] = $recruit['residue_num']>0?$recruit['residue_num']:0;
            $recruit['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($recruit);
        }
        return $recruits;
    }

    public function _after_find(&$data)
    {
        $data->leading;
        $entrust_id = $this->request->get('entrust_id');
        if($entrust_id){
            $entrust = Entrust::find($entrust_id);
            if($entrust){
                $data->status = app()->build(EntrustsRepository::class)->getStatusByEntrustAndRecruit($entrust->status, $data->status);
                $data->resume_num = $entrust->resume_num;
                $data->new_resume_num = $entrust->new_resume_num;
                $data->created_at = $entrust->created_at;
            }
        }
        if($data->company_id==$this->getCurrentCompany()->id){
            $data->is_party = 1;
        }else{
            $data->is_party = 0;
        }
        $data->job = app()->build(JobsRepository::class)->getData($data->job);

        $entrustRes = app()->build(EntrustsRepository::class);
        $data['status_text'] = $entrustRes->getStatusTextByRecruitAndEntrust($data);
        $data['residue_num'] = $data['need_num'] - $data['done_num'] - $data['wait_entry_num'];
        $data['residue_num'] = $data['residue_num']>0?$data['residue_num']:0;
    }

    //排序
    protected function modelGetSort(&$model)
    {
        $model = $model->orderBy('id','desc');
        return $model;
    }

    public function index(Request $request)
    {
        $model = $this->getModel();
        $model = $this->modelGetSort($model);
        $data = $this->modelGetPageData($model);
        $this->_after_get($data);
        dd($data);
    }
}