<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\CompanyPermission;
use App\Models\CompanyRolePermission;

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

    public function savePermissions($permissions, $role_id)
    {
        $hasP = [];
        foreach ($permissions as $permission) {
            $parent = null;
            if($permission->pid)
                $parent = $permission->parent;
            if($parent && !in_array($permission->id, $hasP)){
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
                if($parentt && !in_array($permission->id, $hasP)){
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
            if(!$has && !in_array($permission->id, $hasP)){
                CompanyRolePermission::create([
                    'company_role_id'=>$role_id,
                    'company_permission_id'=>$permission->id,
                ]);
                $hasP[] = $permission->id;
            }
        }
    }
}
