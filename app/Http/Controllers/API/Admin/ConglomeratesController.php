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

    public function beforeStore($data)
    {
        $oldId = Conglomerate::max('id');
        $oldYear = substr($oldId, 0, 4);
        if($oldId && $oldYear==date('Y') && strlen($oldId)==7){
            $newId = $oldId + 1;
        }else{
            $newId = date('Y').'001';
        }
        $data['id'] = $newId;
        return $data;
    }

    public function afterUpdate($id, $data)
    {
        $conglomerate = Conglomerate::find($id);
        Company::where('conglomerate_id', $id)->update(['conglomerate_id'=>null]);
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
