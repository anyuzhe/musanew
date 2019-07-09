<?php

namespace App\Repositories;

use App\Models\Area;

class AreaRepository
{
    public static function getTree()
    {
        $all = Area::all()->toArray();
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
                self::getChild($item, $all);
                $v['children'][] = $item;
            }
        }
        if(count($v['children'])==0){
            $v['children'] = null;
        }
    }
}
