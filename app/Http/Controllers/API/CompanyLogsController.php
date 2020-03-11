<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\CompanyPermission;
use App\Models\CompanySetting;
use App\Repositories\CompanySettingRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use App\ZL\ORG\Excel\ExcelHelper;
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
        $operationArr2 = CompanyPermission::where('level',2)->get()->groupBy('pid')->toArray();
        foreach ($data as &$v) {
            if(isset($moduleArr[$v['module']])){
                $v->module_text = $moduleArr[$v['module']]['display_name'];
                if(isset($operationArr[$moduleArr[$v['module']]['id']])){
                    foreach ($operationArr[$moduleArr[$v['module']]['id']] as $item) {
                        if($item['key']==$v['operation'])
                            $v->operation_text = $item['display_name'];
                    }
                }elseif (isset($operationArr2[$moduleArr[$v['module']]['id']])){
                    foreach ($operationArr2[$moduleArr[$v['module']]['id']] as $item) {
                        if($item['key']==$v['operation'])
                            $v->operation_text = $item['display_name'];
                    }
                }
            }
        }
        return $data;
    }

    public function exportExcel(Request $request)
    {
        if(method_exists($this,'checkIndex')) {
            $res = $this->checkIndex($request);
            if(is_string($res)){
                responseZK(0,null,$res);
            }
        }
        $model = $this->modelPipeline([
            'modelGetAuthLimit',
            'modelGetSearch',
            'modelGetSort',
        ]);
        $list = $this->modelPipeline([
            'collectionGetLoads',
            'modelByAfterGet',
        ],$model->get());
        $excelHelper = new ExcelHelper();
        $title = [
            '用户',
            '邮箱',
            '操作',
            '内容',
            '归属模块',
            '操作时间',
        ];

        $data = [];
        foreach ($list as $item) {
            $_data = [];
            $_data[] = $item->user->realname;
            $_data[] = $item->user->email;
            $_data[] = $item->module_text;
            $_data[] = $item->operation_text;
            $_data[] = $item->content;
            $_data[] = $item->created_at;
            $data[] = $_data;
        }
        $excelHelper->dumpExcel($title,$data,'操作日志', "操作日志数据");
    }
}
