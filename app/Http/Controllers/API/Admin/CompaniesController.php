<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Conglomerate;
use App\Models\UserBasicInfo;
use App\Repositories\CompaniesRepository;
use App\User;
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
        $data->load('addresses');
        $data->load('industry');
        $data->load('conglomerate');
        $data->load('thirdParty');
        foreach ($data as &$company) {
            getOptionsText($company);
            $company->full_logo = getPicFullUrl($company->logo);
            foreach ($company->addresses as &$v) {
                $v->area = [$v->province_id,$v->city_id,$v->district_id];
                $v->area_text = Area::where('id', $v->province_id)->value('cname').
                    Area::where('id', $v->city_id)->value('cname').
                    Area::where('id', $v->district_id)->value('cname');
            }

            $company->is_demand_side = count($company->thirdParty)>0?1:0;
            $_manager = $company->getManager();
            if($_manager){
                $company->manager = $_manager;
                $company->manager->email = $_manager->user->email;
            }else{
                $company->manager = null;
            }
        }
        return $data;
    }

    public function _after_find(&$company)
    {
        $company->addresses;
        foreach ($company->addresses as &$v) {
            $v->area = [$v->province_id,$v->city_id,$v->district_id];
            $v->area_text = Area::where('id', $v->province_id)->value('cname').
                Area::where('id', $v->city_id)->value('cname').
                Area::where('id', $v->district_id)->value('cname');
        }
        $company->full_logo = getPicFullUrl($company->logo);
        $company->industry;
        $company->conglomerate;
        $company->thirdParty;
        $company->departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        getOptionsText($company);
        $company->is_demand_side = count($company->thirdParty)>0?1:0;
        $_manager = $company->getManager();
        if($_manager){
            $company->manager = $_manager;
            $company->manager->email = $_manager->user->email;
        }else{
            $company->manager = null;
        }
    }


//    public function destroy($id)
//    {
//        $model = $this->getModel()->find($id);
//        $model->status = -1;
//        $model->save();
//        return responseZK(0);
//    }
}
