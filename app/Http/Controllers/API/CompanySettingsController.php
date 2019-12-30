<?php

namespace App\Http\Controllers\API;

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
        $setting = CompanySetting::where('company_id', $company->id)->where('key','resume_grade')->first();
        if(!$setting){
            $setting = CompanySettingRepository::getDefaultResumeGrade($company->id);
        }
        return $this->apiReturnJson(0, json_decode($setting->value, 256));
    }

    public function setResumeGrade(Request $request)
    {
        $text = $request->get('user_info');
        $text = $request->get('skills');
        $text = $request->get('education');
        $text = $request->get('working_years');
        $text = $request->get('necessary_skills');
        $text = $request->get('optional_skills');
        $company = $this->getCurrentCompany();
        $setting = CompanySetting::where('company_id', $company->id)->where('key','resume_grade')->first();
        if(!$setting){
            $setting = CompanySettingRepository::getDefaultResumeGrade($company->id);
        }
        return $this->apiReturnJson(0, json_decode($setting->value, 256));
    }
}
