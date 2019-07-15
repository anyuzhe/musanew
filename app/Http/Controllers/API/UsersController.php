<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyRole;
use App\Models\UserBasicInfo;
use DB;
use Illuminate\Support\Facades\Log;

class UsersController extends CommonController
{
    public function info()
    { 
        $user = $this->getUser();
        $info = $user->info;
        if(!$info)
            $info = UserBasicInfo::create(['user_id'=>$user->id]);
        $info->companies = $user->companies;
        $info->current_company = $user->company->first();
        $this->requireMoodleConfig();
        foreach ($info->companies as &$company) {
            $company->logo_url = getMoodlePICURL($company->logo);
            $company->role_name = CompanyRole::find($company->pivot->company_role_id)->name;
        }

        unset($company);
        if($info->current_company){
            $info->current_company->logo_url = getMoodlePICURL($info->current_company->logo);
            $info->current_company->role_name = CompanyRole::find($info->current_company->pivot->company_role_id)->name;
        }

        return $this->apiReturnJson(0, $info);
    }

    public function setInfo() {
//    	$this->validate(
//        $this->request,
//        [
//        	'realname' =>'required',
//        	'idcard_no' =>'required'
//        ]
//    	);
    	$request = $this->request->all();
    	if (isset($request['token'])) {
    		unset($request['token']);
    	}

    	$user = $this->getUser();
    	$request['user_id'] = $user->id;
    	$user->info()->update($request);

    	return $this->apiReturnJson(0);
    }

    public function setCurrentCompany() {
    	$company_id = $this->request->get('company_id');
        $user = $this->getUser();
    	if($company_id){
            app('db')->table('company_user')->where('user_id',$user->id)->update(['is_current'=>0]);
            app('db')->table('company_user')->where('user_id',$user->id)->where('company_id',$company_id)->update(['is_current'=>1]);
        }else{

        }
    	return $this->apiReturnJson(0);
    }

}
