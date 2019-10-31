<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Moodle\CourseCategory;
use App\Models\Skill;
use App\Models\SkillCategory;

class SkillsRepository
{
    public static function getTree()
    {
        $cates = SkillCategory::all()->toArray();
        $skills = Skill::all()->toArray();
        $data = [];
        foreach ($cates as $v) {
            if($v['pid']==0){
                self::getChild($v, $cates, $skills);
                $data[] = $v;
            }
        }
        return $data;
    }

    protected static  function getChild(&$v, $cates, $skills)
    {
        $v['name'] = $v['category_name'];
        $v['children'] = [];
        foreach ($cates as $item) {
            if($item['pid']==$v['id']){
                self::getChild($item, $cates, $skills);
                $v['children'][] = $item;
            }
        }
        if(count($v['children'])==0){
            foreach ($skills as $skill) {
                if($skill['category_l2_id']==$v['id']){
                    $v['children'][] = $skill;
                }
            }
        }
        if(count($v['children'])==0){
            $v['children'] = null;
        }
    }

    public static function getTestCateId()
    {
        return array_merge([6],CourseCategory::where('id',6)->first()->children->pluck('id')->toArray());
    }
    public static function getTestCates()
    {
        return CourseCategory::where('id',6)->first()->children()->get();
    }
}
