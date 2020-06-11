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
use App\Models\ResumeTrain;
use App\Models\ResumeSkill;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Support\Facades\DB;
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
        $data->load('trains');
        $data->load('skills');
        $data->load('attachments');
        $skills = Skill::all()->keyBy('id')->toArray();
        $skillCategories = SkillCategory::all()->keyBy('id')->toArray();
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
        $APP_URL = config('app.url');
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
                $skill->skill_full_name = self::getSkillFullName($skill->skill_id, $skills, $skillCategories);
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
            if($v->avatar){
                $v->avatar_url = getAvatarFullUrl($v->avatar, false);
            }else{
                $v->avatar_url = "";
            }
        }
        return $data;
    }

    public static function getSkillFullName($id, $skills, $skillCategories)
    {
        $level1 = $skills[$id];
        $qz = '';
        if(isset($skillCategories[$level1['category_l1_id']])){
            $qz .= $skillCategories[$level1['category_l1_id']]['category_name'].'/';
        }
        if(isset($skillCategories[$level1['category_l2_id']])){
            $qz .= $skillCategories[$level1['category_l2_id']]['category_name'].'/';
        }
        $fullName = $qz.$level1['name'];
        return $fullName;
    }

    public function getData($data)
    {
        $data->jobCompany;
        $data->assignmentCompany;
        $data->skills;
        $data->attachments;
        $data->educations;
        $data->trains;
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

        $data->resume_file_path = config('app.url').$data->resume_file_path;
        if($data->avatar){
            $data->avatar_url = getAvatarFullUrl($data->avatar);
        }else{
            $data->avatar_url = "";
        }
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
        $trains = isset($data['trains'])?$data['trains']:[];

        if($educations && is_array($educations)){
            $educations_ids = [];
            foreach ($educations as $education) {
                $education['resume_id'] = $id;
                $education = array_remove_by_key($education, 'education_name');
                $education = array_remove_by_key($education, 'education_text');
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
                $company = array_remove_by_key($company, 'industry_text');
                $company = array_remove_by_key($company, 'job_category_text');
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
        if($trains && is_array($trains)){
            $rains_ids = [];
            foreach ($trains as $rain) {
                $rain['resume_id'] = $id;
                if(isset($rain['id']) && $rain['id']){
                    $rains_ids[] = $rain['id'];
                    ResumeTrain::where('id', $rain['id'])->update($rain);
                }else{
                    $_obj = ResumeTrain::create($rain);
                    $rains_ids[] = $_obj->id;
                }
            }
            ResumeTrain::where('resume_id', $id)->whereNotIn('id', $rains_ids)->delete();
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
                $skill = array_remove_by_key($skill, 'skill_level_text');
                $skill = array_remove_by_key($skill, 'skill_name');
                $skill = array_remove_by_key($skill, 'skill_full_name');
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
        return $obj;
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
            $obj->start_work_at = $this->getDateByAllTo01($data['basics']['start_year_of_employment']);
        }
        //生日
        if(isset($data['basics']['birthday']) && !isEmpty($data['basics']['birthday'])){
            $obj->birthdate = $this->getDateByAllTo01($data['basics']['birthday']);
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
                    'start_date'=>isset($education['start_date'])?$this->getDateByAllTo01($education['start_date']):'',
                    'end_date'=>isset($education['end_date'])?$this->getDateByAllTo01($education['end_date']):'',
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
        $skillLevelStr1 = [
            '能够','能','理解','有','使用过','会','了解'
        ];
        $skillLevelStr2 = [
            '良好','熟练','掌握','熟练','熟知','熟悉','掌握','具有','具备'
        ];
        $skillLevelStr3 = [
            '较强','较好','较为丰富','精通','热爱','敏锐','擅长','扎实','富有','坚实','出色','优秀','丰富','fuent','流利'
        ];
        $skillLevelStr4 = [
            '深度理解','深刻理解','深刻','深入理解','深入了解','强烈卓越'
        ];
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
                if(in_array($_language_level_str, $skillLevelStr1)){
                    $_language_level = 1;
                }elseif(in_array($_language_level_str, $skillLevelStr2)){
                    $_language_level = 2;
                }elseif(in_array($_language_level_str, $skillLevelStr3)){
                    $_language_level = 3;
                }elseif(in_array($_language_level_str, $skillLevelStr4)){
                    $_language_level = 4;
                }
                $_language['level'] = $_language_level;
                ResumeSkill::create($_language);
            }
        }

        if($skills && is_array($skills)){
            $oldSkills = Skill::all();
            $oldSkillsData = [];
            foreach ($oldSkills as $oldSkill) {
                $name = strtolower($oldSkill->name);
                if(!isset($oldSkillsData[$name])){
                    $oldSkillsData[$name] = $oldSkill;
                }
            }
            foreach ($skills as $skill) {
                $_skill = [];
                $_skill['resume_id'] = $id;
                $_skill['used_time'] = 0;
                $_s = null;
                if(isset($oldSkillsData[strtolower($skill['skill_name'])]))
                    $_s = $oldSkillsData[strtolower($skill['skill_name'])];

                if(!$_s){

                    $s1 = str_replace(' ','',strtolower($skill['skill_name']));
                    if(strstr($s1,'/')){
                        $ss = explode('/', $s1);
                    }else{
                        $ss = [$s1];
                    }
                    foreach ($oldSkillsData as $name=>$oldSkill) {
                        foreach ($ss as $s) {
                            if(strstr($s, $name) || strstr($name, $s))
                                $_s = $oldSkill;
                        }
                    }
                }
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

                if(in_array($_skill_level_str, $skillLevelStr1)){
                    $_skill_level = 1;
                }elseif(in_array($_skill_level_str, $skillLevelStr2)){
                    $_skill_level = 2;
                }elseif(in_array($_skill_level_str, $skillLevelStr3)){
                    $_skill_level = 3;
                }elseif(in_array($_skill_level_str, $skillLevelStr4)){
                    $_skill_level = 4;
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
            $recruit = Recruit::lock('for update')->find($recruit_id);
        }
        if(isset($data['entrust_id'])){
            $entrust_id = $data['entrust_id'];
            $entrust = Entrust::lock('for update')->find($entrust_id);
            if($entrust)
                $recruit = Recruit::lock('for update')->find($entrust->company_job_recruit_id);
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
            global $LOGIN_USER_CURRENT_COMPANY;
            $log = $recruitResumesRepository->generateLog($recruitResume,1,$entrust?$entrust->thirdParty:$LOGIN_USER_CURRENT_COMPANY, null,1);
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
        if($start_date=='至今'){
            $_start_date = $start_date;
        }elseif($start_date){
            $len = strlen($start_date);
            if($len==10){
                $_start_date = $start_date;
            }if($len==7){
                $_start_date = date('Y-m-01',strtotime($start_date));
            }else{
                $_start_date = date('Y-01-01',strtotime($start_date));
            }
        }else{
            $_start_date = '';
        }
        return $_start_date;
    }

    public function getEducation($educations)
    {
        $value = 0;
        foreach ($educations as $education) {
            if($education->education>$value)
                $value = $education->education;
        }
        return $value;
    }

    public function mixResumes($resumeNew, $resumeOld)
    {
        $old = $resumeOld->toArray();
        unset($old['is_public']);
        unset($old['is_used']);
        unset($old['is_personal']);
        unset($old['is_base']);
        $newData = [
            'resume_name'=>$resumeNew->resume_name,
            'usable_range'=>$resumeNew->usable_range,
            'self_evaluation'=>$resumeNew->self_evaluation,
            'hope_job_text'=>$resumeNew->hope_job_text,
        ];
        if($resumeNew->name){
            $newData['name'] = $resumeNew['name'];
        }
        if($resumeNew->phone){
            $newData['phone'] = $resumeNew['phone'];
        }
        if($resumeNew->gender){
            $newData['gender'] = $resumeNew['gender'];
        }
        if($resumeNew->birthdate){
            $newData['birthdate'] = $resumeNew['birthdate'];
        }
        if($resumeNew->is_married){
            $newData['is_married'] = $resumeNew['is_married'];
        }
        if($resumeNew->start_work_at){
            $newData['start_work_at'] = $resumeNew['start_work_at'];
        }
        if($resumeNew->on_the_job){
            $newData['on_the_job'] = $resumeNew['on_the_job'];
        }
        if($resumeNew->permanent_province_id && $resumeNew->permanent_city_id && $resumeNew->permanent_district_id){
            $newData['permanent_province_id'] = $resumeNew['permanent_province_id'];
            $newData['permanent_city_id'] = $resumeNew['permanent_city_id'];
            $newData['permanent_district_id'] = $resumeNew['permanent_district_id'];
        }
        if($resumeNew->residence_province_id && $resumeNew->residence_city_id && $resumeNew->residence_district_id){
            $newData['residence_province_id'] = $resumeNew['residence_province_id'];
            $newData['residence_city_id'] = $resumeNew['residence_city_id'];
            $newData['residence_district_id'] = $resumeNew['residence_district_id'];
        }
        $resumeNew->fill(array_merge($old, $newData));
        $resumeNew->save();
        ResumeEducation::where('resume_id', $resumeNew->id)->delete();
        ResumeTrain::where('resume_id', $resumeNew->id)->delete();
        foreach ($resumeOld->educations as $education) {
            $_data = $education->toArray();
            $_data['resume_id'] = $resumeNew->id;
            $_data['id'] = null;
            ResumeEducation::create($_data);
        }
        foreach ($resumeOld->trains as $train) {
            $_data = $train->toArray();
            $_data['resume_id'] = $resumeNew->id;
            $_data['id'] = null;
            ResumeTrain::create($_data);
        }
    }

    public function handleNewSkill($resumeNew, $skills)
    {
        $skillsOld = $resumeNew->skills;
        if($skills && is_array($skills)){
            foreach ($skills as $skill) {
                $skill['resume_id'] = $resumeNew->id;
                if(isset($skill['id']) && $skill['id']){
                }else{
                    $has = false;
                    $old = false;
                    foreach ($skillsOld as $item) {
                        if($item->skill_id==$skill['skill_id'] && $skill['skill_level']<=$item->skill_level){
                            $has = $item->id;
                        }elseif($item->skill_id==$skill['skill_id'] && $skill['skill_level']>$item->skill_level){
                            $old = $item->id;
                        }
                    }
                    if($has){
                        continue;
                    }
                    if($old){
                        ResumeSkill::where('id', $old)->delete();
                    }
                    $_obj = ResumeSkill::create($skill);
                    $skill_ids[] = $_obj->id;
                }
            }
        }
    }

    public function getBaseResume()
    {
        $user= TokenRepository::getUser();
        return Resume::where('user_id', $user->id)->where('is_base', 1)->first();
    }
}
