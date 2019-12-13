<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Company;
use App\Models\Conglomerate;
use App\ZL\Controllers\ApiBaseCommonController;

class CompaniesController extends ApiBaseCommonController
{
    protected $model_name = Company::class;
    public $search_field_array = [
//        ['xxx','like'],
//        ['xxx','='],
    ];

    public function authLimit(&$model)
    {

    }

    public function afterStore($obj, $data)
    {
        return $this->apiReturnJson(0);
    }


    public function afterUpdate($id, $data)
    {
        return $this->apiReturnJson(0);
    }

    public function _after_get(&$data)
    {
        $data->load('conglomerate');
        return $data;
    }

    public function _after_find(&$data)
    {
        $data->conglomerate;
    }


//    public function destroy($id)
//    {
//        $model = $this->getModel()->find($id);
//        $model->status = -1;
//        $model->save();
//        return responseZK(0);
//    }
}
