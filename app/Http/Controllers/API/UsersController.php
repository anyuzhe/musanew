<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Resume;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersController extends CommonController
{
    protected $resumeRepository;
    protected $recruitResumesRepository;

    public function __construct(Request $request, ResumesRepository $resumesRepository,RecruitResumesRepository $recruitResumesRepository)
    {
        parent::__construct($request);
        $this->resumeRepository = $resumesRepository;
        $this->recruitResumesRepository = $recruitResumesRepository;
    }

    public function info()
    {
        $user = $this->getUser();
        $info = $user->info;
        if(!$info)
            $info = UserBasicInfo::create(['user_id'=>$user->id]);

        if(!$user->firstname && $info->realname){
            $realname = $info->realname;
            User::where('id', $user->id)->update([
                'firstname'=>$realname?substr_text($realname,0,1):'',
                'lastname'=>$realname?substr_text($realname,1, strlen($realname)):'',
            ]);
        }
        $info->companies = $user->companies;
        $info->current_company = $user->company->first();
        if(!$info->current_company){
            $info->current_company = $info->companies->first();
            if($info->current_company){
                $info->current_company->is_current = 1;
                $r = CompanyUser::where('company_id',$info->current_company->id)->first();
                $r->is_current = 1;
                $r->save();
            }
        }
        $this->requireMoodleConfig();
        foreach ($info->companies as &$company) {
            $company->logo_url = getPicFullUrl($company->logo);
            $company->role_name = CompanyRole::find($company->pivot->company_role_id)->name;
        }

        unset($company);
        if($info->current_company){
            $info->current_company->is_demand_side = count($info->current_company->thirdParty)>0?1:0;
            $info->current_company->logo_url = getPicFullUrl($info->current_company->logo);
            $info->current_company->role_name = CompanyRole::find($info->current_company->pivot->company_role_id)->name;
        }


        $resume = Resume::where('user_id', $user->id)->where('is_base', 1)->first();
        if(!$resume){
            $resume = Resume::create([
                'is_personal'=>1,
                'is_base'=>1,
                'type'=>2,
                'creator_id'=>$user->id,
                'user_id'=>$user->id,
            ]);
        }
        $info = $info->toArray();
        $resumeInfo = $this->resumeRepository->getData($resume)->toArray();
        $resumeInfo['resumeCompanies'] = $resumeInfo['companies'];
        unset($resumeInfo['companies']);
        $info = array_merge($info, $resumeInfo);
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
        $this->resumeRepository->saveDataForForm($obj, $data);
    }

    public function afterUpdate($id, $data)
    {
        $obj = Resume::find($id);

        $this->resumeRepository->saveDataForForm($obj, $data);
        return $this->apiReturnJson(0);
    }

    public function setInfo() {

    	$request = $this->request->all();

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
        $info->realname = $request['name'];
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

}
