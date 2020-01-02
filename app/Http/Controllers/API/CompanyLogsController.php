<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\CompanyPermission;
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

    public function _after_get(&$data)
    {
        $data->load('user');
        $moduleArr = CompanyPermission::where('level','<',3)->get()->keyBy('key')->toArray();
        $operationArr = CompanyPermission::where('level',3)->get()->groupBy('pid')->toArray();
        foreach ($data as &$v) {
            if(isset($moduleArr[$v['module']])){
                $v->module_text = $moduleArr[$v['module']]['display_name'];
                foreach ($operationArr[$moduleArr[$v['module']]['id']] as $item) {
                    if($item['key']==$v['operation'])
                        $v->operation_text = $item['display_name'];
                }
            }
        }
        return $data;
    }
}
