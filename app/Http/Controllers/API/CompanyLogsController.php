<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\CompanySetting;
use App\Repositories\CompanySettingRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyLogsController extends ApiBaseCommonController
{
    protected $model_name = CompanyLog::class;

    public $search_field_array = [
        ['module','like'],
        ['user_id','='],
    ];

    public function authLimit(&$model)
    {
        $company = $this->getCurrentCompany();
        $request = $this->request;
        $start_at = $request->get('start_at');
        $end_at = $request->get('end_at');
        if($company){
            $model = $model->where('company_id', $company->id);
        }
        if($start_at && !$end_at){
            $model = $model->where('created_at', '>=' ,$start_at);
        }elseif (!$start_at && $end_at){
            $model = $model->where('created_at', '<=' ,$end_at);
        }elseif ($start_at && $end_at){
            $model = $model->where('created_at', '>=' ,$start_at)->where('created_at', '<=' ,$end_at);
        }
        return null;
    }
}
