<?php

namespace App\Http\Controllers\API;

use App\Models\Company;
use App\Models\CompanyPermission;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\UserBasicInfo;
use App\Repositories\CompanyLogRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RolesController extends ApiBaseCommonController
{
    protected $model_name = CompanyRole::class;

    public function authLimit(&$model)
    {
        $currentCompany = $this->getCurrentCompany();
        if($currentCompany){
            $company_id = $currentCompany->id;
            $model = $model->where(function ($query)use($company_id){
                $query = $query->where('id', 1)->orWhere('company_id',$company_id);
            });
        }
    }


    //排序
    protected function modelGetSort(&$model)
    {
        $sortBy = app('request')->get('sortBy',false);
        $orderBy = app('request')->get('orderBy','asc');

        $model = $model->when($sortBy, function ($query) use ($sortBy,$orderBy){
            return $query->orderBy($sortBy,$orderBy);
        }, function ($query) use ($orderBy){
            return $query->orderBy('id',$orderBy);
        });
        return $model;
    }

    public function _after_get(&$data)
    {
        $company = $this->getCurrentCompany();
        if(!$company)
            $company = Company::find(20190002);
        $data->load('users');
        foreach ($data as &$v) {
            if($v->id==1){
                $manager = CompanyUser::where('company_id',$company->id)->where('company_role_id', 1)->value('user_id');
                if($manager)
                    $v->users->push(UserBasicInfo::where('user_id',$manager)->first());
            }
            $v->permissions_tree = RoleRepository::getTree($v);
//            $v->users = $userRepository->getUsersByRoleId($v->id);
        }
        CompanyLogRepository::addLog('role_manage','show_role',"查看角色列表 第".request('pagination', 1)."页");

        return $data;
    }

    public function _after_find(&$data)
    {
//        $data->users = $userRepository->getUsersByRoleId($data->id);
        $data->permissions_tree = RoleRepository::getTree($data);
    }

    public function afterStore($obj, $data)
    {
        $currentCompany = $this->getCurrentCompany();
        if($currentCompany){
            $company_id = $currentCompany->id;
            $obj->company_id = $company_id;
            $obj->save();
        }
        if(isset($data['permissions']))
            RoleRepository::savePermissions(CompanyPermission::whereIn('id',$data['permissions'])->get(), $obj->id);

        CompanyLogRepository::addLog('role_manage','add_role',"添加角色 $obj->name");
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data, $role)
    {
        $obj = $this->getModel()->find($id);
        if(isset($data['permissions']))
            RoleRepository::savePermissions(CompanyPermission::whereIn('id',$data['permissions'])->get(), $obj->id);

        $editText = CompanyLogRepository::getDiffText($role);
        CompanyLogRepository::addLog('role_manage','edit_role', $editText);

        return $this->apiReturnJson(0);
    }
    public function destroy($id)
    {
        $model = $this->getModel()->find($id);
        if($model->id!=1){
            CompanyLogRepository::addLog('role_manage','delete_role',"删除职位 $model->name ");

            $model->delete();
            return responseZK(0);
        }else{
            return responseZK(9999,null,'企业管理员无法删除');
        }
    }

}
