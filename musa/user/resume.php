<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir.'/filelib.php');

/**
 * 
 * 简历查看页面
 * 
 */
global $DB;

$id = optional_param('id', 0, PARAM_INT); // User id.

echo $OUTPUT->header();

if($DB->record_exists('resume',array('userid'=>$id))){
    $resume = $DB->get_record('resume',  ['userid' => $id]);
    $resumeid = $resume->id;

    if($DB->record_exists('resume_basic',array('resumeid'=>$resumeid))){
        $basic = $DB->get_record('resume_basic',  ['resumeid' => $resumeid]);
    }

    if($DB->record_exists('resume_company',array('resumeid'=>$resumeid))){
        $companys = $DB->get_records('resume_company',  ['resumeid' => $resumeid]);
    }

    if($DB->record_exists('resume_project',array('resumeid'=>$resumeid))){
        $projects = $DB->get_records('resume_project',  ['resumeid' => $resumeid]);
    }

    if($DB->record_exists('resume_education',array('resumeid'=>$resumeid))){
        $educations = $DB->get_records('resume_education',  ['resumeid' => $resumeid]);
    }
    
    if($DB->record_exists('resume_skill',array('resumeid'=>$resumeid))){
        $sql = "select rs.id as id, sk.name as name, level, skillid, used_month 
            from {resume_skill} as rs 
            inner join {skills} as sk on rs.skillid = sk.id
            where resumeid = $resumeid order by rs.id asc";
        $skills = $DB->get_records_sql($sql);
    }

    include 'resume_html.php';
  }
  else{
    echo '该应聘者暂未录入简历';
  }



// Start setting up the page
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('hr-resume');
$PAGE->set_title('人才简历');
$PAGE->set_heading('人才简历');
$PAGE->set_url('/user/resume.php', array('id' => $userid));

echo $OUTPUT->footer();