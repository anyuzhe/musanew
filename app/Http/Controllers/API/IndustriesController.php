<?php

namespace App\Http\Controllers\API;

use App\Models\Industry;

class AreasController extends CommonController
{
    public static function getTree()
    {
        $all = Industry::all()->toArray();
        $data = [];
        foreach ($all as $v) {
            if($v['pid']==0){
                self::getChild($v, $all);
                $data[] = $v;
            }
        }
        return self::apiReturnJson(0, $all);
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
