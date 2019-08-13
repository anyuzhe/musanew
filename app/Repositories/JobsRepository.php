<?php

namespace App\Repositories;


use App\Models\Area;

class JobsRepository
{
    public function getListData($data)
    {
        $data->load('skills');
        $data->load('address');
        $area_ids = [];
        foreach ($data as $v) {
            if($v->address){
                $area_ids[] = $v->address->province_id;
                $area_ids[] = $v->address->city_id;
                $area_ids[] = $v->address->district_id;
            }
        }
        $areas = Area::whereIn('id', $area_ids)->get()->keyBy('id')->toArray();
        foreach ($data as &$v) {
            if($v->address){
                $v->address->province_text = isset($areas[$v->address->province_id])?$areas[$v->address->province_id]['cname']:'';
                $v->address->city_text = isset($areas[$v->address->city_id])?$areas[$v->address->city_id]['cname']:'';
                $v->address->district_text = isset($areas[$v->address->district_id])?$areas[$v->address->district_id]['cname']:'';
            }
            getOptionsText($v);
            foreach ($v->skills as &$skill) {
                $skill->skill_level = $skill->pivot->skill_level;
                $skill->used_time = $skill->pivot->used_time;
                getOptionsText($skill);
            }
        }
        $data->load('tests');
        return $data;
    }
    public function getData($data)
    {
        $data->skills;
        $data->tests;
        $data->address;
        getOptionsText($data);
        foreach ($data->skills as &$skill) {
            $skill->skill_level = $skill->pivot->skill_level;
            $skill->used_time = $skill->pivot->used_time;
            getOptionsText($skill);
        }
        if($data->address){
            $areas = Area::whereIn('id', [$data->address->province_id, $data->address->city_id, $data->address->district_id])->get()->keyBy('id')->toArray();
            $data->address->province_text = isset($areas[$data->province_id])?$areas[$data->province_id]['cname']:'';
            $data->address->city_text = isset($areas[$data->city_id])?$areas[$data->city_id]['cname']:'';
            $data->address->district_text = isset($areas[$data->district_id])?$areas[$data->district_id]['cname']:'';
        }
        return $data;
    }
}
