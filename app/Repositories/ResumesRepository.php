<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\Company;
use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
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

    public function getListData($data, Company $company=null)
    {
        if($company){
            $_blacklist_resume_ids = CompanyResume::where('company_id', $company->id)->where('type', 3)->pluck('resume_id')->toArray();
            $_mark_resume_ids = CompanyResume::where('company_id', $company->id)->where('type', 3)->pluck('resume_id')->toArray();
            $_source_resumes = CompanyResume::where('company_id', $company->id)->whereIn('source_type', [1])->get();
            $_source_resumes->load('sourceCompany');
            $source_resumes = $_source_resumes->keyBy('resume_id')->toArray();

            foreach ($data as &$v) {
                if(in_array($v->id, $_blacklist_resume_ids)){
                    $v->in_blacklist = 1;
                }else{
                    $v->in_blacklist = 0;
                }
                if(in_array($v->id, $_mark_resume_ids)){
                    $v->have_mark = 1;
                }else{
                    $v->have_mark = 0;
                }
                if(isset($source_resumes[$v->id])){
                    $_data = $source_resumes[$v->id];
                    $v->source_text = $_data['source_company']['company_alias'].'推荐入职';
                }else{
                    $v->source_text = '简历添加';
                }
            }
        }


        $data->load('jobCompany');
        $data->load('assignmentCompany');
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
            $v->residence_province_text = isset($areas[$v->residence_province_id])?$areas[$v->residence_province_id]['cname']:'';
            $v->residence_city_text = isset($areas[$v->residence_city_id])?$areas[$v->residence_city_id]['cname']:'';
            $v->residence_district_text = isset($areas[$v->residence_district_id])?$areas[$v->residence_district_id]['cname']:'';
            getOptionsText($v);
            foreach ($v->skills as $ks=>&$skill) {
                getOptionsText($skill);
                if(!isset($skills[$skill->skill_id])){
                    unset($v->skills[$ks]);
                    continue;
                }
                $skill->skill_name = $skills[$skill->skill_id]['name'];
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
        $data->assignmentCompany;
        $data->skills;
        $data->educations;
        $data->projects;
        $data->companies;
        getOptionsText($data);
        $skills = Skill::all()->keyBy('id')->toArray();
        foreach ($data->skills as $k=>&$skill) {
            getOptionsText($skill);
            if(!isset($skills[$skill->skill_id])){
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
        $obj->is_upload_edit = 1;
        $obj->save();
    }

    public function saveDataForBelloData($data)
    {
        $obj = new Resume();
        //名字
        if(isset($data['basics']['name']) && !isEmpty($data['basics']['name'])){
            $obj->name = $data['basics']['name'];
        }
        //手机号
        if(isset($data['basics']['phone']) && !isEmpty($data['basics']['phone'])){
            $obj->phone = $data['basics']['phone'];
        }
        //期望职位
        if(isset($data['basics']['expected_job_title']) && !isEmpty($data['basics']['expected_job_title'])){
            $obj->hope_job_text = $data['basics']['expected_job_title'];
        }
        //性别
        if(isset($data['basics']['gender']) && !isEmpty($data['basics']['gender'])){
            $obj->gender = $data['basics']['gender']=='男'?1:0;
        }
        //婚姻状态
        if(isset($data['basics']['marital_status']) && !isEmpty($data['basics']['marital_status'])){
            $obj->is_married = $data['basics']['marital_status']=='未婚'?0:1;
        }
        //开始工作时间
        if(isset($data['basics']['start_year_of_employment']) && !isEmpty($data['basics']['start_year_of_employment'])){
            $obj->start_work_at = $data['basics']['start_year_of_employment'];
        }
        //生日
        if(isset($data['basics']['birthday']) && !isEmpty($data['basics']['birthday'])){
            $obj->birthdate = $data['basics']['birthday'];
        }
        //最高学历
        if(isset($data['basics']['top_edu_degree']) && !isEmpty($data['basics']['top_edu_degree'])){
            $obj->education = getEducationValue($data['basics']['top_edu_degree']);
        }
        $obj->save();

        $id = $obj->id;

        $educations = isset($data['educations'])?$data['educations']:[];
        $companies = isset($data['employments'])?$data['employments']:[];
        $projects = isset($data['projects'])?$data['projects']:[];
//        $skills = isset($data['skills'])?$data['skills']:[];

        if($educations && is_array($educations)){
            foreach ($educations as $education) {
                if(isset($education['is_tongzhao'])){
                    if($education['is_tongzhao']){
                        $is_tongzhao = 1;
                    }else{
                        $is_tongzhao = 0;
                    }
                }else{
                    $is_tongzhao = 1;
                }
                $_education = [
                    'start_date'=>isset($education['start_date'])?$education['start_date']:'',
                    'end_date'=>isset($education['end_date'])?$education['end_date']:'',
                    'school_name'=>isset($education['school_name'])?$education['school_name']:'',
                    'major'=>isset($education['major'])?$education['major']:'',
                    'national'=>$is_tongzhao,
                    'education'=>isset($education['degree'])?getEducationValue($education['degree']):0,
                ];
                $_education['resume_id'] = $id;
                ResumeEducation::create($_education);
            }
        }
        if($companies && is_array($companies)){
            foreach ($companies as $company) {
                $_company = [
                    'job_desc'=>isset($company['description'])?$company['description']:'',
                    'job_title'=>isset($company['title'])?$company['title']:'',
                    'company_name'=>isset($company['company_name'])?$company['company_name']:'',
                    'job_start'=>isset($company['start_date'])?$company['start_date']:'',
                    'job_end'=>isset($company['end_date'])?$company['end_date']:'',
                ];
                $_company['resume_id'] = $id;
                if(isset($company['salary']))
                    $_company['salary'] = $company['salary'];
                ResumeCompany::create($_company);
            }
        }
        if($projects && is_array($projects)){
            foreach ($projects as $project) {
                $_project = [
                    'project_name'=>isset($project['project_name'])?$project['project_name']:'',
                    'project_start'=>isset($project['start_date'])?$project['start_date']:'',
                    'project_end'=>isset($project['end_date'])?$project['end_date']:'',
                    'project_desc'=>isset($project['description'])?$project['description']:'',
                    'responsibility'=>isset($project['responsibility'])?$project['responsibility']:'',
                    'relate_company'=>isset($project['company'])?$project['company']:'',
                ];
                $_project['resume_id'] = $id;
                ResumeProject::create($_project);
            }
        }
//        if($skills && is_array($skills)){
//            foreach ($skills as $skill) {
//                $_skill = [
//                  ''
//                ];
//                $_skill['resume_id'] = $id;
//                ResumeSkill::create($_skill);
//            }
//        }
        return $obj;
    }

    public function companyAddHandle(Resume $obj, $data)
    {
        $recruit = null;
        $entrust = null;
        if(isset($data['recruit_id'])){
            $recruit_id = $data['recruit_id'];
            $recruit = Recruit::find($recruit_id);
        }
        if(isset($data['entrust_id'])){
            $entrust_id = $data['entrust_id'];
            $entrust = Entrust::find($entrust_id);
            if($entrust)
                $recruit = $entrust->recruit;
        }
        if($recruit){
            $recruitResume = RecruitResume::create([
                'company_id'=>$recruit->company_id,
                'third_party_id'=>$entrust?$entrust->third_party_id:null,
                'job_id'=>$recruit->job_id,
                'resume_id'=>$obj->id,
                'company_job_recruit_id'=>$recruit->id,
                'company_job_recruit_entrust_id'=>$entrust?$entrust->id:null,
                'status'=>1,
                'resume_source'=>$obj->type,
                'resume_source_company_id'=>$this->getCurrentCompany()->id,
                'creator_id'=>$this->getUser()->id,
            ]);
            $recruitResumesRepository = app()->build(RecruitResumesRepository::class);
            $recruitResumesRepository->haveLook($recruitResume);
            $log = $recruitResumesRepository->generateLog($recruitResume,1,$entrust?$entrust->thirdParty:null, null,1);
            $recruit->resume_num++;
            $recruit->new_resume_num++;
            $recruit->save();
            if($entrust){
                $entrust->resume_num++;
                $entrust->new_resume_num++;
                $entrust->save();
            }
            sendLogsEmail([$log]);
        }
    }
}
