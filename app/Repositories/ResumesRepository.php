<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Resume;
use App\Models\ResumeCompany;
use App\Models\ResumeEducation;
use App\Models\ResumeProject;
use App\Models\ResumeSkill;
use App\Models\Skill;

class ResumesRepository
{
    public function getLogText($status, $type, $otherText)
    {
        $text = '';
        if($status==1){
            if($type==1){
                $text .= $otherText.' 添加简历';
            }
        }
        return $text;
    }

    public function getListData($data)
    {
        $data->load('jobCompany');
        $data->load('educations');
        $data->load('companies');
        $data->load('projects');
        $data->load('skills');
        $skills = Skill::all()->keyBy('id')->toArray();
        $area_ids = $data->pluck('permanent_province_id','permanent_city_id','permanent_district_id','residence_province_id','residence_city_id','residence_district_id');
        foreach ($data as $v) {
            $area_ids[] = $v->permanent_province_id;
            $area_ids[] = $v->permanent_city_id;
            $area_ids[] = $v->permanent_district_id;
            $area_ids[] = $v->residence_province_id;
            $area_ids[] = $v->residence_city_id;
            $area_ids[] = $v->residence_district_id;
        }
        $areas = Area::whereIn('id', $area_ids)->get()->keyBy('id')->toArray();
        foreach ($data as &$v) {
            $v->permanent_province_text = isset($areas[$v->permanent_province_id])?$areas[$v->permanent_province_id]['cname']:'';
            $v->permanent_city_text = isset($areas[$v->permanent_city_id])?$areas[$v->permanent_city_id]['cname']:'';
            $v->permanent_district_text = isset($areas[$v->permanent_district_id])?$areas[$v->permanent_district_id]['cname']:'';
            $v->residence_province_text = isset($areas[$v->province_id])?$areas[$v->province_id]['cname']:'';
            $v->residence_city_text = isset($areas[$v->residence_city_id])?$areas[$v->residence_city_id]['cname']:'';
            $v->residence_district_text = isset($areas[$v->residence_district_id])?$areas[$v->residence_district_id]['cname']:'';
            getOptionsText($v);
            foreach ($v->skills as &$skill) {
                getOptionsText($skill);
                if(isset($skills[$skill->skill_id]))
                    $skill->skill_name = $skills[$skill->skill_id]['name'];
                else
                    $skill->skill_name = '未知技能';
            }
            foreach ($v->companies as &$company) {
                getOptionsText($company);
            }
            foreach ($v->projects as &$project) {
                getOptionsText($project);
            }
            foreach ($v->educations as &$education) {
                getOptionsText($education);
            }
            $v->permanent_area = [$v->permanent_province_id,$v->permanent_city_id,$v->permanent_district_id];
            $v->residence_area = [$v->residence_province_id,$v->residence_city_id,$v->residence_district_id];
            $v->age = getAge($v->birthdate);
        }
        return $data;
    }
    public function getData($data)
    {
        $data->jobCompany;
        $data->skills;
        $data->educations;
        $data->projects;
        $data->companies;
        getOptionsText($data);
        $skills = Skill::all()->keyBy('id')->toArray();
        foreach ($data->skills as $k=>&$skill) {
            getOptionsText($skill);
            if(!$skills[$skill->skill_id]){
                unset($data->skills[$k]);
                continue;
            }
            $skill->skill_name = $skills[$skill->skill_id]['name'];
        }
        foreach ($data->educations as &$education) {
            getOptionsText($education);
        }
        foreach ($data->projects as &$project) {
            getOptionsText($project);
        }
        foreach ($data->companies as &$company) {
            getOptionsText($company);
        }

        $areas = Area::whereIn('id', [
            $data->permanent_province_id,
            $data->permanent_city_id,
            $data->permanent_district_id,
            $data->residence_province_id,
            $data->residence_city_id,
            $data->residence_district_id,
        ])->get()->keyBy('id')->toArray();

        $data->permanent_province_text = isset($areas[$data->permanent_province_id])?$areas[$data->permanent_province_id]['cname']:'';
        $data->permanent_city_text = isset($areas[$data->permanent_city_id])?$areas[$data->permanent_city_id]['cname']:'';
        $data->permanent_district_text = isset($areas[$data->permanent_district_id])?$areas[$data->permanent_district_id]['cname']:'';

        $data->residence_province_text = isset($areas[$data->residence_province_id])?$areas[$data->residence_province_id]['cname']:'';
        $data->residence_city_text = isset($areas[$data->residence_city_id])?$areas[$data->residence_city_id]['cname']:'';
        $data->residence_district_text = isset($areas[$data->residence_district_id])?$areas[$data->residence_district_id]['cname']:'';

        $data->permanent_area = [$data->permanent_province_id,$data->permanent_city_id,$data->permanent_district_id];
        $data->residence_area = [$data->residence_province_id,$data->residence_city_id,$data->residence_district_id];

        $data->age = getAge($data->birthdate);
        return $data;
    }

    public function saveDataForForm(Resume $obj,$data)
    {
        $id = $obj->id;

        if(isset($data['residence_area']) && is_array($data['residence_area'])){
            if(isset($data['residence_area'][0]))
                $obj->residence_province_id = $data['residence_area'][0];
            if(isset($data['residence_area'][1]))
                $obj->residence_city_id = $data['residence_area'][1];
            if(isset($data['residence_area'][2]))
                $obj->residence_district_id = $data['residence_area'][2];
        }

        if(isset($data['permanent_area']) && is_array($data['permanent_area'])){
            if(isset($data['permanent_area'][0]))
                $obj->permanent_province_id = $data['permanent_area'][0];
            if(isset($data['permanent_area'][1]))
                $obj->permanent_city_id = $data['permanent_area'][1];
            if(isset($data['permanent_area'][2]))
                $obj->permanent_district_id = $data['permanent_area'][2];
        }

        $educations = isset($data['educations'])?$data['educations']:[];
        $companies = isset($data['companies'])?$data['companies']:[];
        $projects = isset($data['projects'])?$data['projects']:[];
        $skills = isset($data['skills'])?$data['skills']:[];

        if($educations && is_array($educations)){
            $educations_ids = [];
            foreach ($educations as $education) {
                $education['resume_id'] = $id;
                if(isset($education['id']) && $education['id']){
                    $educations_ids[] = $education['id'];
                    ResumeEducation::where('id', $education['id'])->update($education);
                }else{
                    $_obj = ResumeEducation::create($education);
                    $educations_ids[] = $_obj->id;
                }
            }
            ResumeEducation::where('resume_id', $id)->whereNotIn('id', $educations_ids)->delete();
        }
        if($companies && is_array($companies)){
            $companies_ids = [];
            foreach ($companies as $company) {
                $company['resume_id'] = $id;
                if(isset($company['id']) && $company['id']){
                    $companies_ids[] = $company['id'];
                    ResumeCompany::where('id', $company['id'])->update($company);
                }else{
                    $_obj = ResumeCompany::create($company);
                    $companies_ids[] = $_obj->id;
                }
            }
            ResumeCompany::where('resume_id', $id)->whereNotIn('id', $companies_ids)->delete();
        }
        if($projects && is_array($projects)){
            $projects_ids = [];
            foreach ($projects as $project) {
                $project['resume_id'] = $id;
                if(isset($project['id']) && $project['id']){
                    $projects_ids[] = $project['id'];
                    ResumeProject::where('id', $project['id'])->update($project);
                }else{
                    $_obj = ResumeProject::create($project);
                    $projects_ids[] = $_obj->id;
                }
            }
            ResumeProject::where('resume_id', $id)->whereNotIn('id', $projects_ids)->delete();
        }
        if($skills && is_array($skills)){
            $skill_ids = [];
            foreach ($skills as $skill) {
                $skill['resume_id'] = $id;
                if(isset($skill['id']) && $skill['id']){
                    $skill_ids[] = $skill['id'];
                    ResumeSkill::where('id', $skill['id'])->update($skill);
                }else{
                    $_obj = ResumeSkill::create($skill);
                    $skill_ids[] = $_obj->id;
                }
            }
            ResumeSkill::where('resume_id', $id)->whereNotIn('id', $skill_ids)->delete();
        }
        $obj->save();
    }
}
