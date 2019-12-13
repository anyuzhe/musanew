<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Company;
use App\Models\Conglomerate;
use App\ZL\Controllers\ApiBaseCommonController;

class ConglomeratesController extends ApiBaseCommonController
{
    protected $model_name = Conglomerate::class;
    public $search_field_array = [
        ['name','like'],
//        ['xxx','='],
    ];

    public function authLimit(&$model)
    {

    }

    public function afterStore($obj, $data)
    {
        if(isset($data['company_ids']) && is_array($data['company_ids'])){
            foreach ($data['company_ids'] as $company_id) {
                $company = Company::find($company_id);
                $company->conglomerate()->associate($obj);
                $company->save();
            }
        }
        return $this->apiReturnJson(0);
    }


    public function afterUpdate($id, $data)
    {
        $conglomerate = Conglomerate::find($id);
        if(isset($data['company_ids']) && is_array($data['company_ids'])){
            foreach ($data['company_ids'] as $company_id) {
                $company = Company::find($company_id);
                $company->conglomerate()->associate($conglomerate);
                $company->save();
            }
        }

        return $this->apiReturnJson(0);
    }

    public function _after_get(&$data)
    {
        $data->load('companies');
        return $data;
    }

    public function _after_find(&$data)
    {
        $data->companies;
    }


//    public function destroy($id)
//    {
//        $model = $this->getModel()->find($id);
//        $model->status = -1;
//        $model->save();
//        return responseZK(0);
//    }
}
