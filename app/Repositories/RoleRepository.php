<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\CompanyPermission;

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

    public function savePermissions($permissions)
    {

    }
}
