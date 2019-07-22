<?php

namespace App\Http\Controllers\API;

use App\Models\Industry;

class IndustriesController extends CommonController
{
    public function getTree()
    {
        $all = Industry::all()->toArray();
        $data = [];
        foreach ($all as $v) {
            if($v['pid']==0){
                $this->getChild($v, $all);
                $data[] = $v;
            }
        }
        return $this->apiReturnJson(0, $all);
    }

    protected function getChild(&$v, $all)
    {
        $v['children'] = [];
        foreach ($all as $item) {
            if($item['pid']==$v['id']){
                $this->getChild($item, $all);
                $v['children'][] = $item;
            }
        }
        if(count($v['children'])==0){
            $v['children'] = null;
        }
    }
}
