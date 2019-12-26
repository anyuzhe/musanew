<?php

namespace App\Repositories;


use App\Models\Area;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Resume;
use App\Models\User;
use App\Models\UserBasicInfo;

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
            $company->logo_url = getPicFullUrl($company->logo);
            $company->role_name = CompanyRole::find($company->pivot->company_role_id)->name;
        }

        unset($company);
        if($info->current_company){
//            $info->current_company->is_demand_side = count($info->current_company->thirdParty)>0?1:0;
            $info->current_company->logo_url = getPicFullUrl($info->current_company->logo);
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
}
