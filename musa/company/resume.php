<?php
const SOURCE_USER = 1;
const SOURCE_COMPANY = 2;

require_once(__DIR__ . '/../config.php');

$resumeid = $_GET['id'];
if (!$resumeid) {
	$url = '/company/resume.php';
}
else {
	$url = '/company/resume.php?id='. $resumeid;
}

if ($resumeid) {
	if ($DB->record_exists('resume_basic', array('resumeid' => $resumeid))) {
    $basic = $DB->get_record('resume_basic', ['resumeid' => $resumeid]);
	}

	if ($DB->record_exists('resume_company', array('resumeid' => $resumeid))) {
	    $companys = $DB->get_records('resume_company', ['resumeid' => $resumeid]);
	}

	if ($DB->record_exists('resume_project', array('resumeid' => $resumeid))) {
	    $projects = $DB->get_records('resume_project', ['resumeid' => $resumeid]);
	}

	if ($DB->record_exists('resume_education', array('resumeid' => $resumeid))) {
	    $educations = $DB->get_records('resume_education', ['resumeid' => $resumeid]);
	}
}

$method = !$resumeid ? 'add' : 'edit';

if ($operation = $_POST['operation'] && $operation ='submit') {
	// 基本情况数据
    $jobstatus = $_POST['jobstatus'];
    $hjobtype = $_POST['hjobtype'];
    $workplace = $_POST['workplace'];
    $hindustry = $_POST['hindustry'];
    $career = $_POST['career'];
    $hsalary = $_POST['hsalary'];
    $intro = $_POST['intro'];
    $startwork = $_POST['startwork'];
    $topeducation = $_POST['topeducation'];
		$uname = $_POST['name'];

	switch ($method) {
		case 'add':
		case 'edit':
			if (!$resumeid) {
				$obj = new \stdClass();
		    $obj->userid = $USER->id;
		    $obj->updatedate = time();
		    $obj->createdate = time();
		    if($companyUser = $DB->get_record('company_user', array('userid' => $USER->id))) {
		        $obj->companyid = $companyUser->companyid;
		    }
		    $obj->source = SOURCE_COMPANY;
		    $resumeid = $DB->insert_record('resume', $obj, true);
			}
			
	    if ($DB->record_exists('resume_basic', array('resumeid' => $resumeid))) {
        $basic = $DB->get_record('resume_basic', ['resumeid' => $resumeid]);
        $basic->workplace = $workplace;
        $basic->jobstatus = $jobstatus;
        $basic->industry = $hindustry;
        $basic->career = $career;
        $basic->jobtype = $hjobtype;
        $basic->salary = $hsalary;
        $basic->intro = $intro;
        $basic->startwork = $startwork;
        $basic->topeducation = $topeducation;
	   		$basic->name = $uname;
        $DB->update_record('resume_basic', $basic);
	    } else {
	        $obj = new \stdClass();
	        $obj->resumeid = $resumeid;
	        $obj->workplace = $workplace;
	        $obj->jobstatus = $jobstatus;
	        $obj->industry = $hindustry;
	        $obj->career = $career;
	        $obj->jobtype = $hjobtype;
	        $obj->salary = $hsalary;
	        $obj->intro = $intro;
	        $obj->startwork = $startwork;
	        $obj->topeducation = $topeducation;
	        $obj->name = $uname;
	        $DB->insert_record('resume_basic', $obj);
	    }
			break;
		default:
			break;
	}
	if (!$resumeid) {
		$url = '/company/resume.php';
	}
	else {
		$url = '/company/resume.php?id='. $resumeid;
	}
	redirect($url);
}
$PAGE->set_url($url);

// 获取简历对应技能列表
if ($DB->record_exists('resume_skill', array('resumeid' => $resumeid))) {
    $sql = "select rs.id as id, sk.name as name, level, skillid, used_month 
    from {resume_skill} as rs 
    inner join {skills} as sk on rs.skillid = sk.id
    where resumeid = $resumeid order by rs.id asc";
    $skills = $DB->get_records_sql($sql);
}


$allSkills = $DB->get_records('skills');




echo $OUTPUT->header();

include_once('view/add_resume.html');
echo $OUTPUT->footer();