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
        $model = $model->where('status', 1);
    }

    public function beforeStore($data)
    {
        $oldId = Company::max('id');
        $oldYear = substr($oldId, 0, 4);
        if($oldId && $oldYear==date('Y') && strlen($oldId)==8){
            $newId = $oldId + 1;
        }else{
            $newId = date('Y').'0001';
        }
        $data['id'] = $newId;
        return $data;
    }

    public function afterStore($obj, $data)
    {
        app()->build(CompaniesRepository::class)->handleManger($obj, $data['manager_email']);
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


    public function destroy($id)
    {
        $model = $this->getModel()->find($id);
        $model->status = -1;
        $model->save();
        return responseZK(0);
    }


    public function getUsers($id)
    {
        $company_id = $id;
        $companyUsers = CompanyUser::where('company_id', $company_id)->get();
        $userIds = $companyUsers->pluck('user_id')->toArray();
        $roleIds = $companyUsers->pluck('company_role_id')->toArray();
        $users = \App\Models\User::whereIn('id', $userIds)->get();
        $users->load('info');
        $users = $users->keyBy('id')->toArray();
        $roles = CompanyRole::whereIn('id', $roleIds)->get()->keyBy('id')->toArray();
        $data = [];
        foreach ($companyUsers as $companyUser) {
            $user = $users[$companyUser->user_id];
            if(isset($roles[$companyUser->company_role_id]))
                $role = $roles[$companyUser->company_role_id];
            else
                $role = null;
            $info = $user['info'];
            $data[] = [
                'id'=>$user['id'],
                'name'=>$info?$info['realname']:'无姓名',
                'role_name'=>$role?$role['name']:'无角色',
            ];
        }
        return $this->apiReturnJson(0,$data);
    }

}
