<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\CompanyDepartment;
use App\Models\CompanyPermission;
use App\Models\CompanyRolePermission;
use App\Models\CompanyUserPermissionScope;

class RoleRepository
{
    public static function getTree($role)
    {
        if($role->id==1)
            $all = CompanyPermission::all()->toArray();
        else
            $all = $role->permissions->toArray();
        $data = [];
        foreach ($all as $v) {
            if($v['pid']==0){
                self::getChild($v, $all);
                $data[] = $v;
            }
        }
        return $data;
    }

    public static function getTreeByRoles($roles)
    {
        $all = collect();
        foreach ($roles as $role) {
            if($role->id==1){
                $all = CompanyPermission::all();
                break;
            }else{
                $all = $all->merge($role->permissions);
            }
        }
        $all = $all->keyBy('id')->toArray();
        $data = [];
        foreach ($all as $v) {
            if($v['pid']==0){
                self::getChild($v, $all);
                $data[] = $v;
            }
        }
        return $data;
    }

    public static function getScopeByTree($permissions, $company_id, $user_id)
    {
        global $Department_data;
        global $Scope_data;
        if(!$Department_data)
            $Department_data = CompanyDepartment::where('company_id', $company_id)->get()->keyBy('id');
        if(!$Scope_data)
            $Scope_data = CompanyUserPermissionScope::where('company_id', $company_id)->where('user_id', $user_id)->get()->keyBy('key');

        foreach ($permissions as &$permission) {
            if(isset($permission['children']) && count($permission['children'])>0){
                $permission['scope'] = null;
                $permission['children'] = self::getScopeByTree($permission['children'], $company_id, $user_id);
            }else{
                $_scope = $Scope_data->get($permission['id'].'_'.$company_id.'_'.$user_id);
                if($_scope->type!=1 && $_scope->department_ids){
                    $_scope->departments = CompanyDepartment::whereIn('id', explode(',', $_scope->department_ids))->get();
                }else{
                    $_scope->departments = [];
                }
                $permission['scope'] = $_scope;
            }
        }
        return $permissions;
    }

    protected static  function getChild(&$v, $all)
    {
        $v['children'] = [];
        foreach ($all as $item) {
            if($item['pid']==$v['id']){
                $item['level'] = $v['level']+1;
                self::getChild($item, $all);
                $v['children'][] = $item;
            }
        }
    }

    public static function savePermissions($permissions, $role_id)
    {
        $hasP = [];
        CompanyRolePermission::where(
            'company_role_id',$role_id)->delete();
        foreach ($permissions as $permission) {
            $parent = null;
            if($permission->pid)
                $parent = $permission->parent;
            if($parent && !in_array($parent->id, $hasP)){
                $has = CompanyRolePermission::where('company_role_id', $role_id)->where('company_permission_id', $parent->id)->first();
                if(!$has){
                     CompanyRolePermission::create([
                        'company_role_id'=>$role_id,
                        'company_permission_id'=>$parent->id,
                    ]);
                }
                $hasP[] = $parent->id;

                $parentt = null;
                if($parent->pid)
                    $parentt = $parent->parent;
                if($parentt && !in_array($parentt->id, $hasP)){
                    $has = CompanyRolePermission::where('company_role_id', $role_id)->where('company_permission_id', $parentt->id)->first();
                    if(!$has){
                        CompanyRolePermission::create([
                            'company_role_id'=>$role_id,
                            'company_permission_id'=>$parentt->id,
                        ]);
                    }
                    $hasP[] = $parentt->id;
                }
            }
            $has = CompanyRolePermission::where('company_role_id', $role_id)->where('company_permission_id', $permission->id)->first();
            if(!in_array($permission->id, $hasP) && !$has){
                CompanyRolePermission::create([
                    'company_role_id'=>$role_id,
                    'company_permission_id'=>$permission->id,
                ]);
                $hasP[] = $permission->id;
            }
        }
    }
}
