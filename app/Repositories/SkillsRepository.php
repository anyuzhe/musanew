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
        $cates = SkillCategory::orderBy('sort')->get()->toArray();
        $skills = Skill::orderBy('sort')->get()->toArray();
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

    public static function saveTree($tree)
    {
        $level1_sort = 0;
        $has_cate_ids = [];
        $has_skill_ids = [];
        foreach ($tree as &$item) {
            $item['sort'] = $level1_sort;
            if(isset($item['id'])){
                SkillCategory::where('id',$item['id'])->update([
                        'category_name'=>$item['category_name'],
                        'pid'=>$item['pid'],
                        'sort'=>$item['sort'],
                        'level'=>$item['level'],
                    ]);
                $has_cate_ids[] = $item['id'];
                $itemObj = null;
            }else{
                $itemObj = SkillCategory::create($item);
                $has_cate_ids[] = $itemObj->id;
                $item['id'] = $itemObj->id;
            }
            $pid = $itemObj?$itemObj->id:$item['id'];
            $level1_sort++;
            if(isset($item['children']) && is_array($item['children'])){
                $level2_sort = 0;
                foreach ($item['children'] as &$level2) {
                    $level2['sort'] = $level2_sort;
                    $level2['pid'] = $pid;
                    if(isset($level2['id'])){
                        SkillCategory::where('id',$level2['id'])->update([
                            'category_name'=>$level2['category_name'],
                            'pid'=>$level2['pid'],
                            'sort'=>$level2['sort'],
                            'level'=>$level2['level'],
                        ]);
                        $item2Obj = null;
                        $has_cate_ids[] = $level2['id'];
                    }else{
                        $item2Obj = SkillCategory::create($level2);
                        $has_cate_ids[] = $item2Obj->id;
                        $level2['id'] = $item2Obj->id;
                    }
                    $p2id = $item2Obj?$item2Obj->id:$level2['id'];
                    $level2_sort++;

                    if(isset($level2['children']) && is_array($level2['children'])){
                        $levels_sort = 0;
                        foreach ($level2['children'] as &$skill) {
                            $skill['sort'] = $level2_sort;
                            $skill['category_l1_id'] = $pid;
                            $skill['category_l2_id'] = $p2id;
                            if(isset($skill['id'])){
                                Skill::where('id',$skill['id'])->update([
                                    'name'=>$skill['name'],
                                    'category_l1_id'=>$skill['category_l1_id'],
                                    'sort'=>$skill['sort'],
                                    'category_l2_id'=>$skill['category_l2_id'],
                                ]);
                                $has_skill_ids[] = $skill['id'];
                            }else{
                                $skillObj = Skill::create($skill);
                                $has_skill_ids[] = $skillObj->id;
                                $skill['id'] = $skill;
                            }
                            $levels_sort++;
                        }
                    }
                }
            }
        }

        SkillCategory::whereNotIn('id', $has_cate_ids)->delete();
        Skill::whereNotIn('id', $has_skill_ids)->delete();

        return $tree;
    }
}
