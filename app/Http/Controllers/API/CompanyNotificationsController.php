<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\CompanyNotification;
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

class CompanyNotificationsController extends ApiBaseCommonController
{
    protected $model_name = CompanyNotification::class;

    public $search_field_array = [
    ];

    public function authLimit(&$model)
    {
        $request = $this->request;
        $is_read = $request->get('is_read');
        $model = $model->where('company_id', $this->getCurrentCompany()->id);
        if($is_read!==null && $is_read!==''){
            $model = $model->where('is_read', $is_read);
        }
        return null;
    }

    public function _after_get(&$data)
    {
        foreach ($data as &$v) {
            $v->other_data = json_decode($v->other_data, true);
        }
        return $data;
    }

    public function _after_find(&$data)
    {
        if($data->status==0){
            $data->is_read=1;
            $data->save();
        }
    }
}
