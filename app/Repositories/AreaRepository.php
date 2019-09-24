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
                Area::where('id', $v['id'])->update(['fname'=>$v['cname'],'level'=>1]);
            }
        }
        return $data;
    }

    protected static  function getChild(&$v, $all)
    {
        $v['children'] = [];
        foreach ($all as $item) {
            if($item['pid']==$v['id']){
                $item['fname'] = $v['fname'].$item['cname'];
                $item['level'] = $v['level']+1;
                Area::where('id', $item['id'])->update(['fname'=>$item['fname'], 'level'=>$item['level']]);
                self::getChild($item, $all);
                $v['children'][] = $item;
            }
        }
        if(count($v['children'])==0){
            $v['children'] = null;
        }
    }
}
