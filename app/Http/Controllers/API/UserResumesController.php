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

class UserResumesController extends ApiBaseCommonController
{
    public $model_name = Resume::class;
    public $resumeRepository;
    public $recruitResumesRepository;
    public $search_field_array = [
      ['name','like'],
      ['is_used','='],
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
        $area = $this->request->get('area');
        $in_job = $this->request->get('in_job');
        $skills = $this->request->get('skills');
        $model = $model->where('status','!=',-1);

        $user_id = $this->getUser()->id;
        $model = $model->where('user_id', $user_id)->where('is_base', 0);

        if($in_job || is_numeric($in_job)){
            $model = $model->where('in_job', $in_job);
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
        $user_id = $this->getUser()->id;
        $obj->creator_id = $user_id;
        $obj->user_id = $user_id;
        $obj->type = 2;
        $obj->is_personal = 1;
        $obj = $this->resumeRepository->saveDataForForm($obj, $data);

        $skills = isset($data['skills'])?$data['skills']:[];

        $this->resumeRepository->handleNewSkill(Resume::where('user_id', $this->getUser()->id)->where('is_base', 1)->first(), $skills);
        $this->resumeRepository->mixResumes($obj, $this->resumeRepository->getBaseResume());
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data)
    {
        $obj = Resume::find($id);
        $skills = isset($data['skills'])?$data['skills']:[];

        $this->resumeRepository->handleNewSkill(Resume::where('user_id', $this->getUser()->id)->where('is_base', 1)->first(), $skills);

        $this->resumeRepository->saveDataForForm($obj, $data);
        return $this->apiReturnJson(0);
    }

    public function _after_get(&$data)
    {
        return app()->build(ResumesRepository::class)->getListData($data);
    }

    public function _after_find(&$data)
    {
        $data = app()->build(ResumesRepository::class)->getData($data);
        $data = $data->toArray();
        $data['educations'] = (new Collection($data['educations']))->sortByDesc('start_date')->values()->toArray();
        $data['projects'] = (new Collection($data['projects']))->sortByDesc('project_start')->values()->toArray();
        $data['companies'] = (new Collection($data['companies']))->sortByDesc('job_start')->values()->toArray();
    }

    public function usedList()
    {
        $model = $this->getModel();
        $user_id = $this->getUser()->id;
        $data = $model->where('user_id', $user_id)->where('is_base', 0)->where('is_used', 1)->where('status','!=',-1)->get();
        return $this->apiReturnJson(0, $data);
    }

    public function destroy($id)
    {
        if(RecruitResumeLog::whereIn('status',[1,2,3,4,5,6,7])->where('resume_id', $id)->first()){
            return responseZK(9999, null, '该简历当前正在使用中，不可删除！');
        }
        $model = Resume::find($id);
        if($model->user_id!=$this->getUser()->id){
            return responseZK(9999);
        }
        $model->status = -1;
        $model->save();
        return responseZK(0);
    }


    public function sendResume(Request $request)
    {
        $recruit_id = $request->get('recruit_id');
        $recruit = Recruit::find($recruit_id);
        $entrust_id = $request->get('entrust_id');
        $entrust = Entrust::find($entrust_id);
        if($entrust && !$recruit){
            $recruit = $entrust->recruit;
        }
        $resume_id = $request->get('resume_id');
        if(!$recruit){
            return $this->apiReturnJson(9999, null, '缺少招聘信息');
        }

        app('db')->beginTransaction();
        $logs = [];
        $resume = Resume::find($resume_id);
        if(!$resume){
            return $this->apiReturnJson(9999, null, '缺少简历信息');
        }
        if($resume->user_id!=$this->getUser()->id){
            return $this->apiReturnJson(9999, null, '非法操作');
        }

        if(CompanyResume::where('company_id', $recruit->company_id)->where('resume_id', $resume_id)->where('type',3)->first()){
            return $this->apiReturnJson(9999, null, $resume->name.'在黑名单中，无法添加');
        }
        if($entrust_id){
            $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                ->where('resume_id', $resume_id)
                ->where('company_job_recruit_entrust_id', $entrust_id)->first();
        }else{
            $has = RecruitResume::where('company_job_recruit_id', $recruit->id)
                ->where('resume_id', $resume_id)->first();
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
            'resume_id'=>$resume_id,
            'company_job_recruit_id'=>$recruit->id,
            'company_job_recruit_entrust_id'=>$entrust?$entrust_id:null,
            'status'=>1,
            'resume_source'=>$resume->type,
            'resume_source_company_id'=>null,
            'creator_id'=>$this->getUser()->id,
        ]);
        if($entrust_id && $entrust){
            $log = $this->recruitResumesRepository->generateLog($recruitResume,1, $entrust->thirdParty, null,2);
            $entrust->resume_num++;
            $entrust->new_resume_num++;
            $entrust->save();
        }else{
            $log = $this->recruitResumesRepository->generateLog($recruitResume,1,$recruit->company, null,2);
        }
        $logs[] = $log;
        $recruit->resume_num++;
        $recruit->new_resume_num++;
        $recruit->save();

        sendLogsEmail($logs);
        app('db')->commit();
        return $this->apiReturnJson(0,$logs);
    }

    public function checkUpdate($id,$request)
    {
        $obj = Resume::find($id);
        if($obj && $obj->user_id != $this->getUser()->id){
            return '不能编辑不是自己的简历';
        }
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
                'filename'=>$filename.'.'.$file->getClientOriginalExtension(),
//                'filename'=>$filename,
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
                    dump($data);
                    dd($res_array);
                }
                $obj = $this->resumeRepository->saveDataForBelloData($array);
                $obj->resume_file_path = '/storage/'.$fullPath;
                $obj->resume_file_name = $filename.'.'.$file->getClientOriginalExtension();


                $user_id = $this->getUser()->id;
                $obj->creator_id = $user_id;
                $obj->user_id = $user_id;
                $obj->type = 2;
                $obj->is_personal = 1;

                $obj->save();
                $skills = $obj->skills->toArray();

                $this->resumeRepository->handleNewSkill(Resume::where('user_id', $this->getUser()->id)->where('is_base', 1)->first(), $skills);
                $this->resumeRepository->mixResumes($obj, $this->resumeRepository->getBaseResume());

                $this->_after_find($obj);
                return responseZK(0,$obj);
            }else{
                return responseZK(9999,null,'简历解析出错');
            }
        } else {
            return responseZK(9999,null,'保存出错');
        }
    }
}
