<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Voyager\Custom\SkillCategoryController;
use App\Models\Company;
use App\Models\Conglomerate;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\ZL\Controllers\ApiBaseCommonController;

class SkillCategoriesController extends ApiBaseCommonController
{
    protected $model_name = SkillCategory::class;
    public $search_field_array = [
//        ['xxx','like'],
        ['pid','='],
    ];

    public function authLimit(&$model)
    {

    }

    public function afterStore($obj, $data)
    {
        $parent = $obj->parent;
        if($parent){
            $obj->level = $parent->level+1;
        }else{
            $obj->level = 1;
        }
        $obj->save();
        return $this->apiReturnJson(0);
    }


    public function afterUpdate($id, $data)
    {
        $data = SkillCategory::find($id);
        $parent = $data->parent;
        if($parent){
            $data->level = $parent->level+1;
        }else{
            $data->level = 1;
        }
        $data->save();
        return $this->apiReturnJson(0);
    }

    public function _after_get(&$data)
    {
        return $data;
    }

    public function _after_find(&$data)
    {
    }


//    public function destroy($id)
//    {
//        $model = $this->getModel()->find($id);
//        $model->status = -1;
//        $model->save();
//        return responseZK(0);
//    }
}
