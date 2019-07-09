<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

//require_login();

global $DB;

$userid = isguestuser() ? null : $USER->id;
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}
// Start setting up the page
$PAGE->set_pagelayout('base');	
$PAGE->set_pagetype('my-career');
$PAGE->set_title('职位需求');
$PAGE->set_heading('职位需求');
$PAGE->set_url('/my/career.php', array('id' => $userid));
$PAGE->set_subpage($currentpage->id);

//引入头文件
echo $OUTPUT->header();

// $sql = "SELECT r.id,r.questionnaireid,c.content FROM mdl_questionnaire_response r
// join mdl_questionnaire_resp_single s on r.id=s.response_id
// JOIN mdl_questionnaire_quest_choice c ON c.id=s.choice_id
// JOIN mdl_questionnaire_question q on s.question_id=q.id
// where r.questionnaireid=18 and q.name='岗位:' and r.userid=? order by r.id";

// $result = $DB->get_records_sql($sql, array($USER->id));

$jobs = array();
// 所有技能
$company = $DB->get_record('company', array('companycode' => $USER->profile['companycode']));
if ($DB->record_exists('jobs', ['companyid' => $company->id])) {
    $jobs = $DB->get_records('jobs', null, 'id desc',
    	'id, company_name, department, position, create_time, status');
}
include 'career_html.php';

function jobStatus($status) {
	switch ($status) {
		case 0:
			$rt = '所有人可见';
			break;
		case 1:
			$rt = '仅自己可见';
			break;
		default:
			$rt = '已关闭';
			break;
	}
	return $rt;
}
echo $OUTPUT->footer();

