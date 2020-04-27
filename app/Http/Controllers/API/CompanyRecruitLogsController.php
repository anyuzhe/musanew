<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\CompanyPermission;
use App\Models\CompanySetting;
use App\Models\RecruitLog;
use App\Repositories\CompanySettingRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use App\ZL\ORG\Excel\ExcelHelper;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyRecruitLogsController extends ApiBaseCommonController
{
    protected $model_name = RecruitLog::class;

    public $search_field_array = [
    ];

    public function authLimit(&$model)
    {
        $request = $this->request;
        $recruit_id= $request->get('recruit_id');
        if($recruit_id){
            $model = $model->where('company_job_recruit_id', $recruit_id);
        }
        return null;
    }

    public function _after_get(&$data)
    {
        $data->load('user');
        return $data;
    }
}
