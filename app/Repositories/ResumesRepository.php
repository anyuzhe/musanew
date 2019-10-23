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
use mod_questionnaire\question\date;

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
        $data->load('attachments');
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
        $APP_URL = env('APP_URL');
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
            $v->resume_file_path = $APP_URL.$v->resume_file_path;

            foreach ($v->attachments as &$attachment) {
                $attachment->file_full_path = getPicFullUrl($v->file_path);
            }
        }
        return $data;
    }
    public function getData($data)
    {
        $data->jobCompany;
        $data->assignmentCompany;
        $data->skills;
        $data->attachments;
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
        foreach ($data->attachments as &$attachment) {
            $attachment->file_full_path = getPicFullUrl($attachment->file_path);
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

        $data->resume_file_path = env('APP_URL').$data->resume_file_path;
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
        //自我评价
        if(isset($data['sections']['summary']) && !isEmpty($data['sections']['summary'])){
            $obj->self_evaluation = $data['sections']['summary'];
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
//            if(strlen($data['basics']['birthday'])==10){
//                $obj->birthdate = $data['basics']['birthday'];
//            }else{
//                $obj->birthdate = date('Y-m-01', strtotime($data['basics']['birthday']));
//            }
        }
        //最高学历
        if(isset($data['basics']['top_edu_degree']) && !isEmpty($data['basics']['top_edu_degree'])){
            $obj->education = getEducationValue($data['basics']['top_edu_degree']);
        }
        //户籍地址 出生地址
        $area = null;
        if(isset($data['basics']['hukou'])){
            $data['basics']['birth_place'] = $data['basics']['hukou'];
        }
        if(isset($data['basics']['birth_place']) && !isEmpty($data['basics']['birth_place'])){
            $area = $this->getAreaByText($data['basics']['birth_place']);
            if($area && $area->level==3){
                $obj->permanent_province_id = $area->parent->parent->id;
                $obj->permanent_city_id = $area->parent->id;
                $obj->permanent_district_id = $area->id;
            }elseif($area && $area->level==2){
                $obj->permanent_province_id = $area->parent->id;
                $obj->permanent_city_id = $area->id;
            }elseif($area && $area->level==1){
                $obj->permanent_province_id = $area->id;
            }
            $area = null;
        }
        //现居地
        if(isset($data['basics']['current_job_location']))
            $area = $this->getAreaByText($data['basics']['current_job_location']);
        if($area || isset($data['basics']['current_location']) && !isEmpty($data['basics']['current_location'])){
            if(!$area)
                $area = $this->getAreaByText($data['basics']['current_location']);
            if($area && $area->level==3){
                $obj->residence_province_id = $area->parent->parent->id;
                $obj->residence_city_id = $area->parent->id;
                $obj->residence_district_id = $area->id;
            }elseif($area && $area->level==2){
                $obj->residence_province_id = $area->parent->id;
                $obj->residence_city_id = $area->id;
            }elseif($area && $area->level==1){
                $obj->residence_district_id = $area->id;
            }
        }


        $obj->is_upload_edit = 0;
        $obj->is_upload = 1;
        $obj->save();

        $id = $obj->id;

        $educations = isset($data['educations'])?$data['educations']:[];
        $companies = isset($data['employments'])?$data['employments']:[];
        $projects = isset($data['projects'])?$data['projects']:[];
        $skills = isset($data['skills'])?$data['skills']:[];
        $languages = isset($data['languages'])?$data['languages']:[];

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
                    'job_desc'=>isset($company['description'])?$company['description']:(isset($company['job_resp'])?$company['job_resp']:''),
                    'job_title'=>isset($company['title'])?$company['title']:'',
                    'company_name'=>isset($company['company_name'])?$company['company_name']:'',
                    'job_start'=>isset($company['start_date'])?$this->getDateByAllTo01($company['start_date']):'',
                    'job_end'=>isset($company['end_date'])?$this->getDateByAllTo01($company['end_date']):'',
                    'job_category'=>7,
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
                    'project_start'=>isset($project['start_date'])?$this->getDateByAllTo01($project['start_date']):'',
                    'project_end'=>isset($project['end_date'])?$this->getDateByAllTo01($project['end_date']):'',
                    'project_desc'=>isset($project['description'])?$project['description']:'',
                    'responsibility'=>isset($project['responsibility'])?$project['responsibility']:'',
                    'relate_company'=>isset($project['company'])?$project['company']:'',
                ];
                $_project['resume_id'] = $id;
                ResumeProject::create($_project);
            }
        }
        if($languages && is_array($languages)){
            foreach ($languages as $language) {
                $_language = [];
                $_language['resume_id'] = $id;
                $_language['used_time'] = 0;
                $_s = Skill::where('name',$language['language'])->first();
                if(!$_s){
                    $_s = Skill::create([
                        'name'=>$language['language'],
                        'category_l1_id'=>22,
                        'category_l2_id'=>21,
                    ]);
                }
                $_language['skill_id'] = $_s->id;
                $_language_level_str = '一般';
                $_language_level = 1;
                if(isset($language['listen_and_speak'])){
                    $_language_level_str = $language['listen_and_speak'];
                }elseif(isset($language['read_and_write'])){
                    $_language_level_str = $language['read_and_write'];
                }
                switch ($_language_level_str){
                    case '一般':
                        $_language_level = 1;
                        break;
                    case '良好':
                        $_language_level = 2;
                        break;
                    case '熟练':
                        $_language_level = 3;
                        break;
                    case '精通':
                        $_language_level = 4;
                        break;
                }
                $_language['level'] = $_language_level;
                ResumeSkill::create($_language);
            }
        }
        if($skills && is_array($skills)){
            foreach ($skills as $skill) {
                $_skill = [];
                $_skill['resume_id'] = $id;
                $_skill['used_time'] = 0;
                $_s = Skill::where('name',$skill['skill_name'])->first();
                if(!$_s){
                    $_s = Skill::create([
                        'name'=>$skill['skill_name'],
                        'category_l1_id'=>22,
                        'category_l2_id'=>23,
                    ]);
                }
                $_skill['skill_id'] = $_s->id;
                $_skill_level_str = '了解';
                $_skill_level = 1;
                if(isset($skill['skill_level'])){
                    $_skill_level_str = $skill['skill_level'];
                }
                switch ($_skill_level_str){
                    case '了解':
                        $_skill_level = 1;
                        break;
                    case '掌握':
                        $_skill_level = 1;
                        break;
                    case '良好':
                        $_skill_level = 2;
                        break;
                    case '擅长':
                        $_skill_level = 2;
                        break;
                    case '熟悉':
                        $_skill_level = 3;
                        break;
                    case '熟练':
                        $_skill_level = 3;
                        break;
                    case '精通':
                        $_skill_level = 4;
                        break;
                }
                $_skill['level'] = $_skill_level;
                $_skill['skill_level'] = $_skill_level;
                ResumeSkill::create($_skill);
            }
        }
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
                'resume_source_company_id'=>TokenRepository::getCurrentCompany()->id,
                'creator_id'=>TokenRepository::getUser()->id,
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

    public function getAreaByText($text)
    {
        $area = Area::where('fname', 'like', "%$text%")->first();
        if(!$area)
            $area = Area::where('cname', "%$text%")->first();
        if(!$area){
            //反向匹配
            $areas1 = Area::where('level', 1)->get();
            foreach ($areas1 as $area1) {
                $_text = $area1->cname;
                $_text = str_replace('省','',$_text);
                $_text = str_replace('市','',$_text);

                if(strstr($text, $_text)!==false){
                    $areas2 = Area::where('pid', $area1->id)->get();
                    foreach ($areas2 as $area2) {
                        $_text = $area2->cname;
                        $_text = str_replace('市','',$_text);
                        $_text = str_replace('区','',$_text);
                        if(strstr($text, $_text)!==false){
                            $areas3 = Area::where('pid', $area2->id)->get();
                            foreach ($areas3 as $area3) {
                                $_text = $area3->cname;
                                $_text = str_replace('市','',$_text);
                                $_text = str_replace('区','',$_text);
                                if(strstr($text, $_text)!==false){
                                    return $area3;
                                }
                            }
                            return $area2;
                        }
                    }
                    return $area1;
                }
            }
        }
        $areas2 = Area::where('level', 2)->get();

        foreach ($areas2 as $area2) {
            $_text = $area2->cname;
            $_text = str_replace('市','',$_text);
            $_text = str_replace('区','',$_text);
            if(strstr($text, $_text)!==false){
                $areas3 = Area::where('pid', $area2->id)->get();
                foreach ($areas3 as $area3) {
                    $_text = $area3->cname;
                    $_text = str_replace('市','',$_text);
                    $_text = str_replace('区','',$_text);
                    if(strstr($text, $_text)!==false){
                        return $area3;
                    }
                }
                return $area2;
            }
        }
        $areas3 = Area::where('level', 3)->get();
        foreach ($areas3 as $area3) {
            $_text = $area3->cname;
            $_text = str_replace('市','',$_text);
            $_text = str_replace('区','',$_text);
            if(strstr($text, $_text)!==false){
                return $area3;
            }
        }
        return $area;
    }

    protected function getDateByAllTo01($start_date)
    {
        if($start_date=='面议'){
            $_start_date = $start_date;
        }elseif($start_date){
            if(strlen($start_date)==10){
                $_start_date = $start_date;
            }else{
                $_start_date = date('Y-m-01',strtotime($start_date));
            }
        }else{
            $_start_date = '';
        }
        return $_start_date;
    }
}
