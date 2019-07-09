<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_login();
//引入头文件
echo $OUTPUT->header();

$operation = $_POST['operation'];

if ($operation != null && $operation == 'submit') {
    // 提交
    $resume = $DB->get_record('resume', ['userid' => $USER->id]);
    if (!$resume->companyid && isset($USER->profile['companycode'])) {
        $companyUser = $DB->get_record('company_user',
            array('userid' => $USER->id), 'companyid');
        $resume->companyid = $companyUser->companyid;
    }
    $resume->updatedate = time();
    $DB->update_record('resume', $resume);

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

    if ($DB->record_exists('resume_basic', array('resumeid' => $resume->id))) {
        $basic = $DB->get_record('resume_basic', ['resumeid' => $resume->id]);
        $basic->workplace = $workplace;
        $basic->jobstatus = $jobstatus;
        $basic->industry = $hindustry;
        $basic->career = $career;
        $basic->jobtype = $hjobtype;
        $basic->salary = $hsalary;
        $basic->intro = $intro;
        $basic->startwork = $startwork;
        $basic->topeducation = $topeducation;
        $DB->update_record('resume_basic', $basic);
    } else {
        $obj = new \stdClass();
        $obj->resumeid = $resume->id;
        $obj->workplace = $workplace;
        $obj->jobstatus = $jobstatus;
        $obj->industry = $hindustry;
        $obj->career = $career;
        $obj->jobtype = $hjobtype;
        $obj->salary = $hsalary;
        $obj->intro = $intro;
        $obj->startwork = $startwork;
        $obj->topeducation = $topeducation;
        $DB->insert_record('resume_basic', $obj);
    }
}

// 页面加载
if ($DB->record_exists('resume', array('userid' => $USER->id))) {
    $resume = $DB->get_record('resume', ['userid' => $USER->id]);
    $resumeid = $resume->id;
} else {
    //查找用户所在公司
    $companyUser = null;
    if (isset($USER->profile['companycode'])) {
        $companyUser = $DB->get_record('company_user',
            array('userid' => $USER->id), 'companyid');
    }
    
    $obj = new \stdClass();
    $obj->userid = $USER->id;
    $obj->updatedate = time();
    $obj->createdate = time();
    if($companyUser) {
        $obj->companyid = $companyUser->companyid;
    }
    $resumeid = $DB->insert_record('resume', $obj, true);
}

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
// 获取简历对应技能列表
if ($DB->record_exists('resume_skill', array('resumeid' => $resumeid))) {
    $sql = "select rs.id as id, sk.name as name, level, skillid, used_month 
    from {resume_skill} as rs 
    inner join {skills} as sk on rs.skillid = sk.id
    where resumeid = $resumeid order by rs.id asc";
    $skills = $DB->get_records_sql($sql);
}


$allSkills = $DB->get_records('skills');

include 'resume_html.php';

// Start setting up the page
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('my-resume');
$PAGE->set_title('我的简历');
$PAGE->set_heading('我的简历');
$PAGE->set_url('/my/resume.php', array('id' => $userid));

echo $OUTPUT->footer();