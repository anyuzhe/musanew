<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Repositories\CompanySettingRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanySettingsController extends CommonController
{
    public function getResumeGrade()
    {
        $company = $this->getCurrentCompany();
        $company = Company::find(20190001);
        $setting = CompanySetting::where('company_id', $company->id)->where('key','resume_grade')->first();
        if(!$setting){
            $setting = CompanySettingRepository::getDefaultResumeGrade($company->id);
        }
        return $this->apiReturnJson(0, json_decode($setting->value, 256));
    }

    public function setResumeGrade(Request $request)
    {
        $user_info = $request->get('user_info');
        $skills = $request->get('skills');
        $education = $request->get('education');
        $working_years = $request->get('working_years');
        $necessary_skills = $request->get('necessary_skills');
        $optional_skills = $request->get('optional_skills');
        $company = $this->getCurrentCompany();
        $setting = CompanySetting::where('company_id', $company->id)->where('key','resume_grade')->first();
        if(!$setting){
            $setting = CompanySettingRepository::getDefaultResumeGrade($company->id);
        }
        $setting->value = json_encode([
            'user_info'=>$user_info,
            'skills'=>$skills,
            'education'=>$education,
            'working_years'=>$working_years,
            'necessary_skills'=>$necessary_skills,
            'optional_skills'=>$optional_skills,
            ], 256);
        $setting->save();
        return $this->apiReturnJson(0, json_decode($setting->value, 256));
    }
}
