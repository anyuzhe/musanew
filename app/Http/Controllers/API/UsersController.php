<?php

namespace App\Http\Controllers\API;

use DB;
use Illuminate\Support\Facades\Log;

class UsersController extends CommonController
{
    public function info()
    { 
        $user = $this->getUser();
        $info = $user->info;
        $info->companies = $user->companies;
        $info->current_company = $user->company->first();
        $this->requireMoodleConfig();
        foreach ($info->companies as &$company) {
            $company->logo_url = getMoodlePICURL($company->logo);
        }

        unset($company);
        if($info->current_company){
            $info->current_company->logo_url = getMoodlePICURL($info->current_company->logo);
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
