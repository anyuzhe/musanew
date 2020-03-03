<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyResumeGradeSetting;
use App\Models\CompanySetting;
use App\Repositories\CompanySettingRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyResumeGradeSettingsController extends ApiBaseCommonController
{
    protected $model_name = CompanyResumeGradeSetting::class;
    protected $search_field_array = [
      ['status','=']
    ];
    public function authLimit(&$model)
    {
        $company = $this->getCurrentCompany();
        $model = $model->where('company_id', $company->id);
        $has = CompanyResumeGradeSetting::where('company_id', $company->id)->count();
        if(!$has){
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
            return CompanyResumeGradeSetting::create([
                'name'=>'默认评分配置',
                'scope'=>'默认范围',
                'status'=>1,
                'value'=>json_encode($value, 256),
                'company_id'=>$company->id
            ]);
        }
    }


    public function _after_get(&$settings)
    {
        foreach ($settings as &$setting) {
            $setting->value = json_decode($setting->value, 256);
        }
        return $settings;
    }


    public function _after_find(&$data)
    {
        $data->value = json_decode($data->value, 256);
    }

    public function afterStore($setting, $data)
    {
        $setting->company_id = $this->getCurrentCompany()->id;
        $request = \request();
        $user_info = $request->get('user_info');
        $skills = $request->get('skills');
        $education = $request->get('education');
        $working_years = $request->get('working_years');
        $necessary_skills = $request->get('necessary_skills');
        $optional_skills = $request->get('optional_skills');
        $setting->value = json_encode([
            'user_info'=>$user_info,
            'skills'=>$skills,
            'education'=>$education,
            'working_years'=>$working_years,
            'necessary_skills'=>$necessary_skills,
            'optional_skills'=>$optional_skills,
        ], 256);
        $setting->save();

        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data)
    {
        $setting = CompanyResumeGradeSetting::find($id);
        $setting->company_id = $this->getCurrentCompany()->id;
        $request = \request();
        $user_info = $request->get('user_info');
        $skills = $request->get('skills');
        $education = $request->get('education');
        $working_years = $request->get('working_years');
        $necessary_skills = $request->get('necessary_skills');
        $optional_skills = $request->get('optional_skills');
        $setting->value = json_encode([
            'user_info'=>$user_info,
            'skills'=>$skills,
            'education'=>$education,
            'working_years'=>$working_years,
            'necessary_skills'=>$necessary_skills,
            'optional_skills'=>$optional_skills,
        ], 256);
        $setting->save();
        return $this->apiReturnJson(0);
    }
}
