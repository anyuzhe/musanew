<?php
require_once(__DIR__ . '/../../config.php');
require_once('../service.php');
require_once($CFG->dirroot . '/my/lib.php');
require_login();

//$course = $DB->get_record('course',array('userid'=>$USER->id));

$sql= "SELECT distinct u.* FROM mdl_role_assignments a 
join mdl_role r on a.roleid=r.id
join mdl_user u on u.id=a.userid
where r.shortname='applicant'" ;

$result = $DB->get_records_sql($sql, array($USER->id));


// Start setting up the page
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('my-corpration');
$PAGE->set_title('企业人才');
$PAGE->set_heading('企业人才');
$PAGE->set_url('/post/personel.php', array('id' => $userid));

echo $OUTPUT->header();

include 'personnel_html.php';

echo $OUTPUT->footer();