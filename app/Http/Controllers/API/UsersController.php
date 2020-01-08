<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Course;
use App\Models\Resume;
use App\Models\ResumeEducation;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\Repositories\SkillsRepository;
use App\Repositories\TestsRepository;
use App\Repositories\UserRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersController extends CommonController
{
    protected $resumeRepository;
    protected $recruitResumesRepository;
    protected $usersRepository;

    public function __construct(Request $request, ResumesRepository $resumesRepository,
                                RecruitResumesRepository $recruitResumesRepository, UserRepository $userRepository)
    {
        parent::__construct($request);
        $this->resumeRepository = $resumesRepository;
        $this->recruitResumesRepository = $recruitResumesRepository;
        $this->usersRepository = $userRepository;
    }

    public function info()
    {
        $user = $this->getUser();
        $info = $this->usersRepository->getInfo($user);
        return $this->apiReturnJson(0, $info);
    }

    public function afterStore($obj, $data)
    {
        $user_id = $this->getUser()->id;
        $obj->creator_id = $user_id;
        $obj->user_id = $user_id;
        $obj->is_base = 1;
        $obj->is_personal = 1;
        $obj->type = 2;
        if(!$obj->education){
            $obj->education = $this->resumeRepository->getEducation(ResumeEducation::where('resume_id', $obj->id)->get());
        }
        $obj = $this->resumeRepository->saveDataForForm($obj, $data);

        $otherResumes = Resume::where('user_id', $user_id)->where('is_base', 0)->where('type', 2)->get();
        $skills = isset($data['skills'])?$data['skills']:[];
        foreach ($otherResumes as $otherResume) {
            $this->resumeRepository->mixResumes($otherResume, $obj);
            $this->resumeRepository->handleNewSkill($otherResume, $skills);
        }
    }

    public function afterUpdate($id, $data)
    {
        $obj = Resume::find($id);
        $educationValue = $this->resumeRepository->getEducation(ResumeEducation::where('resume_id', $obj->id)->get());
        if($educationValue){
            $obj->education = $educationValue;
        }
        $this->resumeRepository->saveDataForForm($obj, $data);
        $otherResumes = Resume::where('user_id', $this->getUser()->id)->where('is_base', 0)->where('type', 2)->get();
        $skills = isset($data['skills'])?$data['skills']:[];
        foreach ($otherResumes as $otherResume) {
            $this->resumeRepository->mixResumes($otherResume, $obj);
            $this->resumeRepository->handleNewSkill($otherResume, $skills);
        }
        return $this->apiReturnJson(0);
    }

    public function setInfo() {

    	$request = $this->request->all();

        if(isset($request['name']) && $request['name']){
            $name = $request['name'];
        }else{
            $name = $request['realname'];
        }
        $request['name'] = $name;
        $request['realname'] = $name;


        $user = $this->getUser();
        $obj = Resume::where('user_id', $user->id)->where('is_base', 1)->first();
    	if($obj){
            $obj->fill($request);
            $obj->save();
    	    $this->afterUpdate($obj->id, $request);
        }else{
            $obj = Resume::create($request);
            $obj->is_base = 1;
            $obj->is_personal = 1;
            $this->afterStore($obj, $request);
        }
        $info = $user->info;
        $info->realname = $name;

        $info->fill($request);
        $info->save();

        if(!$user->firstname && $info->realname){
            $realname = $info->realname;
            User::where('id', $user->id)->update([
                'firstname'=>$realname?substr_text($realname,0,1):'',
                'lastname'=>$realname?substr_text($realname,1, strlen($realname)):'',
            ]);
        }
    	return $this->info();
    }

    public function setCurrentCompany() {
    	$company_id = $this->request->get('company_id');
        $user = $this->getUser();
    	if($company_id){
            CompanyUser::where('user_id',$user->id)->update(['is_current'=>0]);
            CompanyUser::where('user_id',$user->id)->where('company_id',$company_id)->update(['is_current'=>1]);
        }else{

        }
    	return $this->apiReturnJson(0);
    }

    public function getTestData()
    {
        $skillsRes = app()->Build(TestsRepository::class);
        $user = $this->getUser();
        $cates = SkillsRepository::getTestCates();
        foreach ($cates as $cate) {
            foreach ($cate->courses as &$course) {
                $course->result = $skillsRes->getTestData($course, $user);
            }
        }
        return $this->apiReturnJson(0, $cates);
    }
}
