<?php
/**
 * Created by PhpStorm.
 * User: MUSA012
 * Date: 2019/4/1
 * Time: 16:10
 */

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

$profile = (object)$USER;
$sql= "SELECT b.id,b.username,a.roleid FROM mdl_role_assignments a,mdl_user b where b.id=a.userid AND a.roleid=9";
$result = $DB->get_records_sql($sql, array('userid'=>$USER->id));
//$course = $DB->get_record('course',array('id' = $))

$id = optional_param('id', 0, PARAM_INT); // User id.


echo $OUTPUT->header();

if($DB->record_exists('resume',array('userid'=>$id))){
    $resume = $DB->get_record('resume',  ['id' => $id]);
    $resumeid = $resume->id;

    include '/my/index.php';
}else{
    echo '该用户未选课';
}

global $DB;

// Start setting up the page
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('hr-resume');
$PAGE->set_title('个人课程');
$PAGE->set_heading('个人课程');
$PAGE->set_url('/my/mycourse.php', array('id' => userid));

echo $OUTPUT->footer();