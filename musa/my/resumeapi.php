<?php

require_once(__DIR__ . '/../config.php');

$resume = $_POST['resumeid'] ?
    $DB->get_record('resume',  ['id' => $_POST['resumeid']]) :
    $DB->get_record('resume',  ['userid' => $USER->id]); 
$resume->updatedate = time();
$DB->update_record('resume', $resume);

$category = $_POST['category'];
switch ($category) {
    case 'company':
        $id = $_POST["id"];
        $companyname = $_POST['companyname'];
        $industry = $_POST['industry'];
        $jobtitle = $_POST['jobtitle'];
        $jobtype = $_POST['jobtype'];
        $jobstart = $_POST['jobstart'];
        $jobend = $_POST['jobend'];
        $salary = $_POST['salary'];
        $jobdesc = $_POST['jobdesc'];
        
        if($DB->record_exists('resume_company', array('id' => $id))){
            $company = $DB->get_record('resume_company',  ['id' => $id]);
            $company->companyname = $companyname;
            $company->industry = $industry;
            $company->jobtitle = $jobtitle;
            $company->jobtype = $jobtype;
            $company->jobstart = $jobstart;
            $company->jobend = $jobend;
            $company->salary = $salary;
            $company->jobdesc = $jobdesc;
            $DB->update_record('resume_company', $company);
        }else {
            $obj = new \stdClass();
            $obj->resumeid = $resume->id;
            $obj->companyname = $companyname;
            $obj->industry = $industry;
            $obj->jobtitle = $jobtitle;
            $obj->jobtype = $jobtype;
            $obj->jobstart = $jobstart;
            $obj->jobend = $jobend;
            $obj->salary = $salary;
            $obj->jobdesc = $jobdesc;
            $DB->insert_record('resume_company', $obj);
        }
        break;

    case 'project':
        $id = $_POST["id"];
        $relatecompany = $_POST['relatecompany'];
        $projectname = $_POST['projectname'];
        $projectstart = $_POST['projectstart'];
        $projectend = $_POST['projectend'];
        $projectdesc = $_POST['projectdesc'];
        $responsibility = $_POST['responsibility'];
        
        if($DB->record_exists('resume_project', array('id' => $id))){
            $project = $DB->get_record('resume_project',  ['id' => $id]);
            $project->relatecompany = $relatecompany;
            $project->projectname = $projectname;
            $project->projectstart = $projectstart;
            $project->projectend = $projectend;
            $project->projectdesc = $projectdesc;
            $project->responsibility = $responsibility;
            $DB->update_record('resume_project', $project);
        }else {
            $obj = new \stdClass();
            $obj->resumeid = $resume->id;
            $obj->relatecompany = $relatecompany;
            $obj->projectname = $projectname;
            $obj->projectstart = $projectstart;
            $obj->projectend = $projectend;
            $obj->projectdesc = $projectdesc;
            $obj->responsibility = $responsibility;
            $DB->insert_record('resume_project', $obj);
        }
        break;

    case 'education':
        $id = $_POST["id"];
        $schoolname = $_POST['schoolname'];
        $major = $_POST['major'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $national = $_POST['national'];
        $degree = $_POST['degree'];
        
        if($DB->record_exists('resume_education', array('id' => $id))){
            $education = $DB->get_record('resume_education',  ['id' => $id]);
            $education->schoolname = $schoolname;
            $education->major = $major;
            $education->start = $start;
            $education->end = $end;
            $education->national = $national;
            $education->degree = $degree;
            $DB->update_record('resume_education', $education);
        }else {
            $obj = new \stdClass();
            $obj->resumeid = $resume->id;
            $obj->schoolname = $schoolname;
            $obj->major = $major;
            $obj->start = $start;
            $obj->end = $end;
            $obj->national = $national;
            $obj->degree = $degree;
            $DB->insert_record('resume_education', $obj);
        }
        break;
    case 'skill':
        $id = (int)$_POST["id"];
        $skillid = (int)$_POST['skill_id'];
        $usedMonth = (int)$_POST['used_month'];
        $level = $_POST['level'];

        if($DB->record_exists('resume_skill', array('id' => $id))){
            $skill = $DB->get_record('resume_skill',  ['id' => $id]);
            $skill->skillid = $skillid;
            $skill->used_month = $usedMonth;
            $skill->level = $level;
            $DB->update_record('resume_skill', $skill);
        }else {
            $obj = new \stdClass();
            $obj->resumeid = (int)$resume->id;
            $obj->skillid = $skillid;
            $obj->used_month = $usedMonth;
            $obj->level = $level;
            $DB->insert_record('resume_skill', $obj);
        }
        break;
    case 'del_company':
        $id = $_POST["id"];

        if($DB->record_exists('resume_company', array('id' => $id))){
            $company = $DB->delete_records('resume_company',  ['id' => $id]);
        }else {
        //
        }
        break;
    case 'del_project':
        $id = $_POST["id"];

        if($DB->record_exists('resume_project', array('id' => $id))){
            $project = $DB->delete_records('resume_project',  ['id' => $id]);
        }else {
            //
        }
        break;
    case 'del_education':
        $id = $_POST["id"];

        if($DB->record_exists('resume_education', array('id' => $id))){
            $education = $DB->delete_records('resume_education',  ['id' => $id]);
        }else {
            //
        }
        break;
    case 'del_skill':
        $id = $_POST["id"];
        if($DB->record_exists('resume_skill', array('id' => $id))){
            $company = $DB->delete_records('resume_skill',  ['id' => $id]);
        }
        break;
    default:
        // do nothing
        break;
}

echo 'success';