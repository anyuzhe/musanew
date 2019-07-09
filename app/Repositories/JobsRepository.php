<?php

namespace App\Repositories;


use App\Models\Area;

class JobsRepository
{
    public function getListData($data)
    {
        $data->load('skills');
        $area_ids = $data->pluck('province_id','city_id','district_id');
        foreach ($data as $v) {
            $area_ids[] = $v->province_id;
            $area_ids[] = $v->city_id;
            $area_ids[] = $v->district_id;
        }
        $areas = Area::whereIn('id', $area_ids)->get()->keyBy('id')->toArray();
        foreach ($data as &$v) {
            $v->province_text = isset($areas[$v->province_id])?$areas[$v->province_id]['cname']:'';
            $v->city_text = isset($areas[$v->city_id])?$areas[$v->city_id]['cname']:'';
            $v->district_text = isset($areas[$v->district_id])?$areas[$v->district_id]['cname']:'';
            getOptionsText($v);
            foreach ($v->skills as &$skill) {
                $skill->skill_level = $skill->pivot->skill_level;
                $skill->used_time = $skill->pivot->used_time;
                getOptionsText($skill);
            }
            $v->area = [$v->province_id,$v->city_id,$v->district_id];
        }
        $data->load('tests');
        return $data;
    }
    public function getData($data)
    {
        $data->skills;
        $data->tests;
        getOptionsText($data);
        foreach ($data->skills as &$skill) {
            $skill->skill_level = $skill->pivot->skill_level;
            $skill->used_time = $skill->pivot->used_time;
            getOptionsText($skill);
        }
        $areas = Area::whereIn('id', [$data->province_id, $data->city_id, $data->district_id])->get()->keyBy('id')->toArray();
        $data->province_text = isset($areas[$data->province_id])?$areas[$data->province_id]['cname']:'';
        $data->city_text = isset($areas[$data->city_id])?$areas[$data->city_id]['cname']:'';
        $data->district_text = isset($areas[$data->district_id])?$areas[$data->district_id]['cname']:'';
        $data->area = [$data->province_id,$data->city_id,$data->district_id];
        return $data;
    }
}
