<?php

namespace App\Repositories;


use App\Models\Area;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\PasswordFindCode;
use App\Models\Resume;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\ZL\Moodle\TokenHelper;

class UserRepository
{
    public function getInfo($user)
    {
        $resumeRepository = app()->build(ResumesRepository::class);
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
        requireMoodleConfig();

        foreach ($info->companies as &$company) {
            $company->logo_url = getCompanyLogo($company->logo);
            $company->role_name = CompanyRole::find($company->pivot->company_role_id)->name;
        }

        unset($company);
        if($info->current_company){
//            $info->current_company->is_demand_side = count($info->current_company->thirdParty)>0?1:0;
            $info->current_company->logo_url = getCompanyLogo($info->current_company->logo);
            $info->current_company->role_name = CompanyRole::find($info->current_company->pivot->company_role_id)->name;
        }


        $resume = Resume::where('user_id', $user->id)->where('is_base', 1)->first();
        if(!$resume){
            $resume = Resume::create([
                'is_personal'=>1,
                'is_base'=>1,
                'type'=>2,
                'name'=>$info->realname,
                'creator_id'=>$user->id,
                'user_id'=>$user->id,
            ]);
        }

        if(!$resume->name && $info->realname){
            $resume->name = $info->realname;
            $resume->save();

            $otherResumes = Resume::where('user_id', $user->id)->where('is_base', 0)->where('type', 2)->get();
            foreach ($otherResumes as $otherResume) {
                $resumeRepository->mixResumes($otherResume, $resume);
            }
        }


        $info = $info->toArray();
        $resumeInfo = $resumeRepository->getData($resume)->toArray();
        $resumeInfo['resume_companies'] = $resumeInfo['companies'];
        unset($resumeInfo['companies']);
        $info = array_merge($info, $resumeInfo);
        return $info;
    }

    public function getUsersByRoleId($role_id)
    {
        $userIds = CompanyUser::where('company_role_id', $role_id)->pluck('user_id')->toArray();
        $users = User::whereIn('id', $userIds);
        $users->load('info');
        return $users;
    }

    public function getListInfoData($list)
    {
        $area_ids = [];
        foreach ($list as $v) {
            $area_ids[] = $v->info->permanent_province_id;
            $area_ids[] = $v->info->permanent_city_id;
            $area_ids[] = $v->info->permanent_district_id;
            $area_ids[] = $v->info->residence_province_id;
            $area_ids[] = $v->info->residence_city_id;
            $area_ids[] = $v->info->residence_district_id;
        }
        $areas = Area::whereIn('id', $area_ids)->get()->keyBy('id')->toArray();
        foreach ($list as &$v) {
            $v->info->permanent_province_text = isset($areas[$v->info->permanent_province_id]) ? $areas[$v->info->permanent_province_id]['cname'] : '';
            $v->info->permanent_city_text = isset($areas[$v->info->permanent_city_id]) ? $areas[$v->info->permanent_city_id]['cname'] : '';
            $v->info->permanent_district_text = isset($areas[$v->info->permanent_district_id]) ? $areas[$v->info->permanent_district_id]['cname'] : '';
            $v->info->residence_province_text = isset($areas[$v->info->residence_province_id]) ? $areas[$v->info->residence_province_id]['cname'] : '';
            $v->info->residence_city_text = isset($areas[$v->info->residence_city_id]) ? $areas[$v->info->residence_city_id]['cname'] : '';
            $v->info->residence_district_text = isset($areas[$v->info->residence_district_id]) ? $areas[$v->info->residence_district_id]['cname'] : '';
        }
        return $list;
    }

    public function generateInviteUser($email)
    {
        requireMoodleConfig();
        global $CFG;
        require_once($CFG->dirroot . '/user/editlib.php');
        require_once($CFG->libdir . '/authlib.php');
        require_once(getMoodleRoot().'/login/lib.php');

        $user = ['email'=>$email,'password'=>'0101010101'];
        $user = json_decode(json_encode($user));

        $user->username = $user->email;
        $user = signup_setup_new_user($user);
        $this->userSignup($user, true);

        User::where('id', $user->id)->update([
            'confirmed'=>0,
        ]);
        UserBasicInfo::create(['user_id'=>$user->id]);
        $user = User::find($user->id);
        $token = TokenHelper::getTokenForUser($user);
        $user->token = $token->token;

        return $user;
    }

    protected function userSignup(&$user, $notify=true, $confirmationurl = null) {
        global $CFG, $DB, $SESSION;
        require_once(getMoodleRoot().'/user/profile/lib.php');
        require_once(getMoodleRoot().'/user/lib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

        // Save any custom profile field information.
        profile_save_data($user);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            set_user_preference('auth_email_wantsurl', $SESSION->wantsurl, $user);
        }

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();
        ##发送确认邮箱
//        if (! send_confirmation_email($user, $confirmationurl)) {
//            print_error('auth_emailnoemail', 'auth_email');
//        }

//        if ($notify) {
        return true;
//        } else {
//            return true;
//        }
    }

    public function getCurrentCompany($user, $current_company=null)
    {
        if(!$current_company)
            $current_company = $user->companies()->where('is_current', 1)->first();
        if(!$current_company){
            $current_company = $user->companies->first();
            if($current_company){
                $current_company->is_current = 1;
                $r = CompanyUser::where('company_id', $current_company->id)->where('user_id',$user->id)->first();
                $r->is_current = 1;
                $r->save();
            }
        }

        if($current_company){
//            $info->current_company->is_demand_side = count($info->current_company->thirdParty)>0?1:0;
            $current_company->logo_url = getCompanyLogo($current_company->logo);
            $current_company->role_name = getCompanyRoleName($current_company, $user);

            CompanyUser::where('user_id',$user->id)->update(['is_current'=>0]);
            CompanyUser::where('company_id', $current_company->id)->where('user_id',$user->id)->update(['is_current'=>1]);
        }
        return $current_company;
    }

    public function checkCurrentCompany($user)
    {
        $hasLack = false;
        foreach ($user->companies as $company) {
            if(!$company->company_alias || !$company->contact_name || !$company->contact_phone || !$company->industry_id || !$company->company_scale)
                $hasLack = true;
            if($company->addresses->count()==0)
                $hasLack = true;
            if($hasLack){
                $this->getCurrentCompany($user, $company);
                return $company;
            }
        }
    }
}
