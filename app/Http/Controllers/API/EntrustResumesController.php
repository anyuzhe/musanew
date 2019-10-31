<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Models\ResumeAttachment;
use App\Models\ResumeSkill;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use TCG\Voyager\Facades\Voyager;

class EntrustResumesController extends ApiBaseCommonController
{
    public $model_name = Resume::class;
    public $resumeRepository;
    public $recruitResumesRepository;
    public $search_field_array = [
      ['name','like'],
      ['third_party_id','='],
      ['gender','='],
      ['gender','='],
      ['education','>='],
      ['hope_job_text','like'],
    ];

    protected $fileSuffixes = [
        'bin',
        'msg',
        'doc',
        'ppt',
        'pptx',
        'htm',
        'html',
        'mht',
        'png',
        'jpg',
        'jpeg',
        'docx',
        'pdf',
        'rtf',
        'txt',
        'text',
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
//            [
//                'hope_salary_min' => 'numeric|required|min:1|lt:hope_salary_max',
//                'hope_salary_max' => 'numeric|required|min:1',
//            ],
//            [
//                'hope_salary_min.lt'=>'期望薪资最少必须小于最大值',
//                'hope_salary_min.min'=>'期望薪资最少必须大于0',
////                'hope_salary_min.max'=>'期望薪资最少必须小于99999',
//                'hope_salary_max.min'=>'期望薪资最大必须大于0',
////                'hope_salary_max.max'=>'期望薪资最大必须小于99999',
//            ]
        ];
    }

    public function authLimit(&$model)
    {
        $recruit_id = $this->request->get('recruit_id');
        $area = $this->request->get('area');
        $entrust_id = $this->request->get('entrust_id');
        $in_job = $this->request->get('in_job');
        $skills = $this->request->get('skills');
        $in_blacklist = $this->request->get('in_blacklist',0);
        $have_mark = $this->request->get('have_mark',null);
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
//            $model = $model->whereNotIn('id', RecruitResume::where('company_job_recruit_entrust_id', $entrust_id)->pluck('resume_id')->toArray());
            $model = $model->where(function ($query)use($entrust_id){
                $query->whereNotIn('id', RecruitResume::where('company_job_recruit_entrust_id', $entrust_id)->pluck('resume_id')->toArray())->orWhereNull('company_job_recruit_entrust_id');
            });
        }elseif ($recruit_id){
            $model = $model->whereNotIn('id', RecruitResume::where('company_job_recruit_id', $recruit_id)->pluck('resume_id')->toArray());
        }

        if($in_blacklist!==null){
            $_resume_ids = CompanyResume::where('company_id', $this->getCurrentCompany()->id)->where('type', 3)->pluck('resume_id')->toArray();
            if($in_blacklist==1){
                $model = $model->whereIn('id', $_resume_ids);
            }else{
                $model = $model->whereNotIn('id', $_resume_ids);
            }
        }
        if($have_mark!==null){
            $_resume_ids = CompanyResume::where('company_id', $this->getCurrentCompany()->id)->where('type', 2)->pluck('resume_id')->toArray();
            if($have_mark==1){
                $model = $model->whereIn('id', $_resume_ids);
            }else{
                $model = $model->whereNotIn('id', $_resume_ids);
            }
        }
        if(is_array($skills) && count($skills)>0){
            $resume_skill_ids = [];
            foreach ($skills as $skill) {
                if(is_string($skill))
                    $skill = json_decode($skill, true);
                $resume_skill_ids = array_merge($resume_skill_ids,
                    ResumeSkill::where('skill_id',$skill[0])->where('skill_level','>=',$skill[1])->pluck('resume_id')->toArray());
            }
            $model = $model->whereIn('id', $resume_skill_ids);
        }
        if(is_array($area) && count($area)>0){
            if(isset($area[0])){
                $model = $model->where('residence_province_id', $area[0]);
            }
            if(isset($area[1])){
                $model = $model->where('residence_city_id', $area[1]);
            }
            if(isset($area[2])){
                $model = $model->where('residence_district_id', $area[2]);
            }
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
        $this->resumeRepository->companyAddHandle($obj, $data);
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

    public function entrustSendResumes()
    {
        $data = $this->request->all();
        $ids = $data['resume_ids'];
        $recruit_id = null;
        $recruit = null;
        if(isset($data['recruit_id'])){
            $recruit_id = $data['recruit_id'];
            $recruit = Recruit::find($recruit_id);
        }
        $entrust_id = null;
        $entrust = null;
        if(isset($data['entrust_id'])){
            $entrust_id = $data['entrust_id'];
            $entrust = Entrust::find($entrust_id);
            if($entrust)
                $recruit = $entrust->recruit;
        }

        if(!$recruit){
            return $this->apiReturnJson(9999, null, '缺少招聘信息');
        }
//        if(!$entrust_id || !$entrust){
//            return $this->apiReturnJson(9999, null, '缺少委托信息');
//        }

        app('db')->beginTransaction();
        $logs = [];
        foreach ($ids as $id) {
            $resume = Resume::find($id);

            if(CompanyResume::where('company_id', $recruit->company_id)->where('resume_id', $id)->where('type',3)->first()){
                return $this->apiReturnJson(9999, null, $resume->name.'在黑名单中，无法添加');
            }
            if($entrust_id){
                $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                    ->where('resume_id', $id)
                    ->where('company_job_recruit_entrust_id', $entrust_id)->first();
            }else{
                $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                    ->where('resume_id', $id)->first();
            }

//            if($resume->in_job==1){
//                app('db')->rollBack();
//                return $this->apiReturnJson(9999, null, $resume->name.'已入职，无法添加');
//            }
            if($has){
                app('db')->rollBack();
                return $this->apiReturnJson(9999, null, $resume->name.'已投递，无法重复添加');
            }

            $recruitResume = RecruitResume::create([
                'company_id'=>$recruit->company_id,
                'third_party_id'=>$entrust?$entrust->third_party_id:null,
                'job_id'=>$recruit->job_id,
                'resume_id'=>$id,
                'company_job_recruit_id'=>$recruit->id,
                'company_job_recruit_entrust_id'=>$entrust?$entrust_id:null,
                'status'=>1,
                'resume_source'=>$resume->type,
                'resume_source_company_id'=>$this->getCurrentCompany()->id,
                'creator_id'=>$this->getUser()->id,
            ]);
            $this->recruitResumesRepository->haveLook($recruitResume);
            if($entrust_id && $entrust){
                $log = $this->recruitResumesRepository->generateLog($recruitResume,1,$entrust->thirdParty, null,1);
                $entrust->resume_num++;
                $entrust->new_resume_num++;
                $entrust->save();
            }else{
                $log = $this->recruitResumesRepository->generateLog($recruitResume,1,$this->getCurrentCompany(), null,1);
            }
            $logs[] = $log;
            $recruit->resume_num++;
            $recruit->new_resume_num++;
            $recruit->save();
        }
        sendLogsEmail($logs);
        app('db')->commit();
        return $this->apiReturnJson(0,$logs);
    }

    public function resumeSendEntrust()
    {
        $resume_id = $this->request->get('resume_id');
        $recruit_ids = $this->request->get('recruit_ids');
        $entrust_ids = $this->request->get('entrust_ids');
        $company = $this->getCurrentCompany();
        $resume = Resume::find($resume_id);
        if(!$resume_id || !$resume){
            return $this->apiReturnJson(9999, null, '缺少简历信息');
        }

        app('db')->beginTransaction();
        if($recruit_ids && is_array($recruit_ids)){
            $logs = [];
            foreach ($recruit_ids as $recruit_id) {
                $recruit = Recruit::find($recruit_id);

                if(CompanyResume::where('company_id', $recruit->company_id)->where('resume_id', $resume_id)->where('type',3)->first()){
                    return $this->apiReturnJson(9999, null, $resume->name.'在黑名单中，无法添加');
                }

                $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                    ->where('resume_id', $resume_id)->first();
//                if($resume->in_job==1){
//                    app('db')->rollBack();
//                    return $this->apiReturnJson(9999, null, $resume->name.'已入职，无法添加');
//                }
                if($has){
                    app('db')->rollBack();
                    return $this->apiReturnJson(9999, null, $resume->name.'已投递，无法重复添加');
                }

                $recruitResume = RecruitResume::create([
                    'company_id'=>$recruit->company_id,
                    'job_id'=>$recruit->job_id,
                    'resume_id'=>$resume_id,
                    'company_job_recruit_id'=>$recruit->id,
                    'status'=>1,
                    'resume_source'=>$resume->type,
                    'resume_source_company_id'=>$this->getCurrentCompany()->id,
                    'creator_id'=>$this->getUser()->id,
                ]);
                $this->recruitResumesRepository->haveLook($recruitResume);
                $log = $this->recruitResumesRepository->generateLog($recruitResume,1, $company, null,1);
                $recruit->resume_num++;
                $recruit->new_resume_num++;
                $recruit->save();
                $logs[] = $log;
            }
            sendLogsEmail($logs);
        }
        if($entrust_ids && is_array($entrust_ids)){
            $logs=[];
            foreach ($entrust_ids as $entrust_id) {
                $entrust = Entrust::find($entrust_id);
                $recruit = $entrust->recruit;
                if(CompanyResume::where('company_id', $recruit->company_id)->where('resume_id', $resume_id)->where('type',3)->first()){
                    return $this->apiReturnJson(9999, null, $resume->name.'在黑名单中，无法添加');
                }

                $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                    ->where('resume_id', $resume_id)
                    ->where('company_job_recruit_entrust_id', $entrust_id)->first();
//                if($resume->in_job==1){
//                    app('db')->rollBack();
//                    return $this->apiReturnJson(9999, null, $resume->name.'已入职，无法添加');
//                }
                if($has){
                    app('db')->rollBack();
                    return $this->apiReturnJson(9999, null, $resume->name.'已投递，无法重复添加');
                }

                $recruitResume = RecruitResume::create([
                    'company_id'=>$recruit->company_id,
                    'third_party_id'=>$entrust->third_party_id,
                    'job_id'=>$recruit->job_id,
                    'resume_id'=>$resume_id,
                    'company_job_recruit_id'=>$recruit->id,
                    'company_job_recruit_entrust_id'=>$entrust_id,
                    'status'=>1,
                    'resume_source'=>$resume->type,
                    'resume_source_company_id'=>$this->getCurrentCompany()->id,
                    'creator_id'=>$this->getUser()->id,
                ]);
                $this->recruitResumesRepository->haveLook($recruitResume);
                if($entrust_id && $entrust){
                    $log = $this->recruitResumesRepository->generateLog($recruitResume,1,$entrust->thirdParty, null,1);
                    $recruit->resume_num++;
                    $recruit->new_resume_num++;
                    $recruit->save();

                    $entrust->resume_num++;
                    $entrust->new_resume_num++;
                    $entrust->save();
                    $logs[] = $log;
                }else{
                    //暂无
                }
            }

            sendLogsEmail($logs);
        }

        app('db')->commit();
        return $this->apiReturnJson(0,$logs);
    }

    public function _after_get(&$data)
    {
        return app()->build(ResumesRepository::class)->getListData($data, $this->getCurrentCompany());
    }

    public function _after_find(&$data)
    {
        $recruit_resume_id = $this->request->get('recruit_resume_id');
        if($recruit_resume_id){
            $recruitResume = RecruitResume::find($recruit_resume_id);
            if($recruitResume){
                $this->recruitResumesRepository->haveLook($recruitResume);

                $data->recruit = $recruitResume;

                $data->matching = $this->recruitResumesRepository->matching($recruitResume);
            }else{
                $data->recruit = null;
            }
        }else{
            $data->recruit = null;
        }
        $data = app()->build(ResumesRepository::class)->getData($data);
        $data = $data->toArray();
        $data['educations'] = (new Collection($data['educations']))->sortByDesc('start_date')->values()->toArray();
        $data['projects'] = (new Collection($data['projects']))->sortByDesc('project_start')->values()->toArray();
        $data['companies'] = (new Collection($data['companies']))->sortByDesc('job_start')->values()->toArray();
    }

    public function destroy($id)
    {
        if(RecruitResumeLog::whereIn('status',[1,2,3,4,5,6,7])->where('resume_id', $id)->first()){
            return responseZK(9999, null, '该简历当前正在使用中，不可删除！');
        }
        $model = Resume::find($id);
        $model->status = -1;
        $model->save();
        return responseZK(0);
    }

    public function upload(Request $request)
    {
        $fullFilename = null;
        $slug = 'resumes';
        $file = $request->file('file');
        if(!$file){
            return responseZK(9999,null,'没有上传文件');
        }

        $path = $slug.'/'.date('F').date('Y').'/';

        $filename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
        $filename_counter = 1;

        // Make sure the filename does not exist, if it does make sure to add a number to the end 1, 2, 3, etc...
        while (Storage::disk(config('voyager.storage.disk'))->exists($path.$filename.'.'.$file->getClientOriginalExtension())) {
            $filename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension()).(string) ($filename_counter++);
        }

        $fullPath = $path.$filename.'.'.$file->getClientOriginalExtension();

        $ext = $file->guessClientExtension();

        if (!in_array($ext, $this->fileSuffixes)) {
            return responseZK(9999,null,'不正确的上传格式');
        }

        $_content = file_get_contents($file->getRealPath());
        // move uploaded file from temp to uploads directory
        if (Storage::disk(config('voyager.storage.disk'))->put($fullPath, $_content, 'public')) {
            $fullFilename = $fullPath;
//            $res = [
//                'path'=>$fullFilename,
//                'full_path'=>Voyager::image($fullFilename),
//            ];
            //保存简历完毕

            //解析简历
            $data = [
                'filename'=>$filename,
                'content'=>base64_encode($_content),
                'need_avatar'=>0
            ];
            $headers = [
                'X-API-KEY: '.config('app.BELLO-API-KEY')
            ];
            $url = "http://47.92.100.9/api/resume/parse";
//            $url = "https://www.belloai.com/v2/open/resume/parse";
            $res = http_post_json($url, json_encode($data, 256) ,$headers);
            if(isset($res[1])){
                $res_array = json_decode($res[1], true);
            }else{
                $res_array = null;
            }
            if($res[0]=='200' && $res_array && $res_array['status']['code']==200){
                $array = $res_array['result'];
                if($request->get('is_test')){
//                    $e = $this->resumeRepository->getAreaByText($array['basics']['current_location']);
//                    dd($e);
                    dd($array);
                }
                $obj = $this->resumeRepository->saveDataForBelloData($array);
                if($this->getUser())
                    $obj->creator_id = $this->getUser()->id;
                $obj->type = 3;
                $company = $this->getCurrentCompany();
                if($company)
                    $company->resumes()->attach($obj->id);

                $obj->resume_file_path = '/storage/'.$fullPath;
                $obj->resume_file_name = $filename.'.'.$file->getClientOriginalExtension();

                $obj->save();

                $this->resumeRepository->companyAddHandle($obj, $request->all());

                $this->_after_find($obj);
                return responseZK(0,$obj);
            }else{
                return responseZK(9999,null,'简历解析出错');
            }
        } else {
            return responseZK(9999,null,'保存出错');
        }
    }

    public function attachmentUpload(Request $request)
    {
        $resume_id = $request->get('resume_id');
        if(!$resume_id)
            $resume_id = $request->get('id');

        if(!$resume_id || !$resume = Resume::find($resume_id)){
            return responseZK(9999,null,'没有简历id或id错误');
        }

        $fullFilename = null;
        $slug = 'attachments';
        $file = $request->file('file');
        if(!$file){
            return responseZK(9999,null,'没有上传文件');
        }

        $path = $slug.'/'.date('F').date('Y').'/';

        $filename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
        $filename_counter = 1;

        // Make sure the filename does not exist, if it does make sure to add a number to the end 1, 2, 3, etc...
        while (Storage::disk(config('voyager.storage.disk'))->exists($path.$filename.'.'.$file->getClientOriginalExtension())) {
            $filename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension()).(string) ($filename_counter++);
        }

        $fullPath = $path.$filename.'.'.$file->getClientOriginalExtension();

        $ext = $file->guessClientExtension();

        if (!in_array($ext, $this->fileSuffixes)) {
            return responseZK(9999,null,'不正确的上传格式');
        }

        $_content = file_get_contents($file->getRealPath());
        // move uploaded file from temp to uploads directory
        if (Storage::disk(config('voyager.storage.disk'))->put($fullPath, $_content, 'public')) {

            $obj = ResumeAttachment::create([
                'resume_id'=>$resume_id,
                'file_path'=>$fullPath,
                'file_name'=>$filename.'.'.$file->getClientOriginalExtension(),
                'creator_id'=>$this->getUser()->id,
            ]);

            return responseZK(0,$obj);
        } else {
            return responseZK(9999,null,'保存出错');
        }
    }

    public function attachmentDestroy($id)
    {
        $model = ResumeAttachment::find($id);
        if($model){
            $model->delete();
            return responseZK(0);
        }else{
            return responseZK(9999,null,'没有该附件');
        }
    }
}
