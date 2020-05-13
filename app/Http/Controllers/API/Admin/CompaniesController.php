<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\CompanyUserRole;
use App\Models\Conglomerate;
use App\Models\UserBasicInfo;
use App\Repositories\CompaniesRepository;
use App\Repositories\CompanyLogRepository;
use App\Repositories\UserRepository;
use App\User;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;

class CompaniesController extends ApiBaseCommonController
{
    protected $model_name = Company::class;
    public $search_field_array = [
//        ['xxx','like'],
        ['is_third_party','='],
        ['is_demand_side','='],
        ['suspended','='],
    ];

    public function authLimit(&$model)
    {
        $model = $model->where('status', 1);

        $text = $this->request->get('text');
        $exclude_id = $this->request->get('exclude_id');
        if($exclude_id){
            $model = $model->where('id','!=',$exclude_id);
        }
        if($text) {
            $companyIds = Company::where('company_name', 'like', "%$text%")->orWhere('company_alias', 'like', "%$text%")->orWhere('id', 'like', "%$text%")->pluck('id')->unique()->toArray();
            $model = $model->whereIn('id', $companyIds);
        }
    }

    public function checkUpdate($id,$data)
    {
        if(Company::where('company_alias',$data->get('company_alias'))->where('status', 1)->where('id','!=', $id)->first()){
            return '该企业简称已经存在';
        }
        else
            return null;
    }

    public function checkStore($data)
    {
        if(Company::where('company_alias',$data->get('company_alias'))->where('status', 1)->where('status', 1)->first())
            return '该企业简称已经存在';
        else
            return null;
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
        if(isset($data['natures']) && is_array($data['natures'])){
            $is_third_party = 0;
            $is_demand_side = 0;
            foreach ($data['natures'] as $v) {
                if($v=='is_third_party'){
                    $is_third_party = 1;
                }elseif($v=='is_demand_side'){
                    $is_demand_side = 1;
                }
            }
            $obj->is_third_party = $is_third_party;
            $obj->is_demand_side = $is_demand_side;
        }
        $obj->save();
        if(isset($data['third_partys']) && is_array($data['third_partys'])){
            $obj->thirdParty()->sync($data['third_partys']);
        }
        app()->build(CompaniesRepository::class)->handleManger($obj, $data['manager_email']);
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data)
    {
        $obj = $this->getModel()->find($id);
        if(isset($data['natures']) && is_array($data['natures'])){
            $is_third_party = 0;
            $is_demand_side = 0;
            foreach ($data['natures'] as $v) {
                if($v=='is_third_party'){
                    $is_third_party = 1;
                }elseif($v=='is_demand_side'){
                    $is_demand_side = 1;
                }
            }
            $obj->is_third_party = $is_third_party;
            $obj->is_demand_side = $is_demand_side;
        }
        $obj->save();
        if(isset($data['third_partys']) && is_array($data['third_partys'])){
            $obj->thirdParty()->sync($data['third_partys']);
        }
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
            $company->full_logo = getCompanyLogo($company->logo);
            foreach ($company->addresses as &$v) {
                $v->area = [$v->province_id,$v->city_id,$v->district_id];
                $v->area_text = Area::where('id', $v->province_id)->value('cname').
                    Area::where('id', $v->city_id)->value('cname').
                    Area::where('id', $v->district_id)->value('cname');
            }

//            $company->is_demand_side = count($company->thirdParty)>0?1:0;
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
        $company->full_logo = getCompanyLogo($company->logo);
        $company->industry;
        $company->conglomerate;
        $company->thirdParty;
        $company->departments = app()->build(CompaniesRepository::class)->getDepartmentTree($company->id);
        getOptionsText($company);
//        $company->is_demand_side = count($company->thirdParty)>0?1:0;
        $_manager = $company->getManager();
        if($_manager){
            $company->manager = $_manager;
            $company->manager->email = $_manager->user->email;
        }else{
            $company->manager = null;
        }
        $natures = [];
        if($company->is_demand_side){
            $natures[] = 'is_demand_side';
        };
        if($company->is_third_party){
            $natures[] = 'is_third_party';
        };
        $company->natures = $natures;
        $company->roles = CompanyRole::where('id','>',1)->where(function ($query)use($company){
            $query->where('company_id', $company->id)->orWhereNull('company_id');
        })->get();
        $company->roles = CompanyRole::where('company_id', $company->id)->get();
//        $company->roles = CompanyRole::where('id','>',1)->where(function ($query)use($company){
//            $query->where('company_id', $company->id)->orWhereNull('company_id');
//        })->get();
    }


    public function destroy($id)
    {
        $model = $this->getModel()->find($id);
        $model->status = -1;
        $model->save();
        CompanyUser::where('company_id', $model->id)->update(['status'=>-1]);
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
                'confirmed'=>$user['confirmed'],
                'email'=>$user['email'],
            ];
        }
        return $this->apiReturnJson(0,$data);
    }

    public function userShow($id,$user_id)
    {
        $user = \App\Models\User::find($user_id);
        $company = Company::find($id);
        $info = UserBasicInfo::where('user_id', $user_id)->first();
        $companyUser = CompanyUser::where('company_id', $company->id)->where('user_id', $user_id)->first();

        if($companyUser->department && $companyUser->department->level==1){
            $department_ids = [$companyUser->department_id];
            $department_name = $companyUser->department->name;
        }elseif($companyUser->department && $companyUser->department->level==2){
            $department_ids = [$companyUser->department->parent->id,$companyUser->department_id];
            $department_name = $companyUser->department->parent->name.'-'.$companyUser->department->name;
        }else{
            $department_ids = [];
            $department_name = null;
        }
        $_info = app()->build(UserRepository::class)->getInfo($user);
        $info->department_name = $department_name;
        $info->department_ids = $department_ids;
        $info->start_work_at = $_info['start_work_at'];
        $info->entry_at = $companyUser->entry_at;

        $_roles = getCompanyRoles($company, $user);

        $info->address_id = $companyUser->address_id;
        if($info->address_id)
            $info->address = CompanyAddress::find($info->address_id);

        $role_ids = [];
        $role_names = [];
        $is_manager = 0;
        foreach ($_roles as $role) {
            if($role['id']==1)
                $is_manager = 1;
            $role_names[] = $role['name'];
            $role_ids[] = $role['id'];
        }
        $info->role_ids = $role_ids;
        $info->role_names = $role_names;
        $info->is_manager = $is_manager;
        $info->avatar_url = getAvatarFullUrl($info->avatar);
        $info->work_years = getYearsText($info->start_work_at, date('Y-m-d'));
        $info->entry_years = getYearsText($info->entry_at, date('Y-m-d'));

//        CompanyLogRepository::addLog('company_user_manage','show_user',"查看详情 $info->realname");

        return $this->apiReturnJson(0,$info);
    }

    public function storeUser($id, Request $request)
    {
        $department_id = $request->get('department_id');
        $email = $request->get('email');
        $roles =  $request->get('roles');
        $company = Company::find($id);


        $user = UserRepository::getUserByEmail($email);
        if($user && CompanyUser::where('company_id', $company->id)->where('user_id',$user->id)->first()){
            return $this->apiReturnJson(9999, null, '该用户已在企业中, 请直接修改');
        }
        $user = app()->build(CompaniesRepository::class)->handleUser($company, $email, $roles, $department_id);
//        CompanyLogRepository::addLog('company_user_manage','add_user',"新增企业人员 $email");
        return $this->apiReturnJson(0);
    }

    public function updateUser($id, $user_id, Request $request)
    {
        $department_id = $request->get('department_id');
        if(is_array($department_id) && count($department_id)>0)
            $department_id = $department_id[count($department_id)-1];
        $email = $request->get('email');
        $roles =  $request->get('roles');
        $entry_at =  $request->get('entry_at');
        $address_id =  $request->get('address_id');

        $company = Company::find($id);
        $user = \App\Models\User::find($user_id);

        app()->build(UserRepository::class)->setInfo($user, $request->all());

        if($user && $email &&$email!=$user->email){
            if(User::where('id','!=',$user->id)->where('confirmed',1)->where('email', $user->email)->where('deleted',0)->first()){
                return $this->apiReturnJson(9999, null, '该邮箱已经存在');
            }
            $user->email = $email;
            $user->save();
        }
        if(!$email){
            $email = $user->email;
        }
        $requestData = $request->all();
        unset($requestData['department_id']);
        $companyUser = CompanyUser::where('company_id', $company->id)->where('user_id', $user_id)->first();
        $companyUser->fill($requestData);
        $companyUser->save();
        $user = app()->build(CompaniesRepository::class)->handleUser($company, $email, $roles, $department_id);

//        CompanyLogRepository::addLog('company_user_manage','edit_user',"编辑企业人员 $email");
        return $this->apiReturnJson(0);
    }

    public function deleteUser($id, $user_id, Request $request)
    {
        $user = User::find($user_id);
//        CompanyLogRepository::addLog('company_user_manage','delete_user',"删除企业人员 $user->email");

        $company = Company::find($id);
        CompanyUser::where('user_id', $user_id)->where('company_id', $company->id)->delete();
        CompanyUserRole::where('user_id', $user_id)->where('company_id', $company->id)->delete();

        return $this->apiReturnJson(0);
    }
}
