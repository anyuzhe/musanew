<?php

namespace App\Repositories;

use App\Models\CompanySetting;

class CompanySettingRepository
{
    public static function getDefaultResumeGrade($company_id)
    {
//        user_info 个人信息
//education 最高学历
//working_years 年资
//skills 专业技能
//necessary_skills 必要技能
//optional_skills 选择技能
        $value = [
            'user_info'=>30,
            'skills'=>70,
            'education'=>70,
            'working_years'=>30,
            'necessary_skills'=>50,
            'optional_skills'=>50,
        ];
        return CompanySetting::create([
            'key'=>'resume_grade',
            'display_name'=>'简历评分配置',
            'value'=>json_encode($value, 256),
            'company_id'=>$company_id
        ]);
    }

    public static function getResumeGrade($company_id)
    {
        $setting = CompanySetting::where('company_id', $company_id)->where('key','resume_grade')->first();
        if(!$setting){
            $setting = CompanySettingRepository::getDefaultResumeGrade($company_id);
        }
        return $setting;
    }
}
