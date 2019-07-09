<?php
require_once('../config.php');

define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

require_login();


$context = get_context_instance(CONTEXT_COMPANY, $USER->id);

$canaccessallgroups = has_capability('moodle/company:viewlist', $context);

if (!$canaccessallgroups && !$userCompany = getComponyUser()) {
    // The user is not in the group so show message and exit.

    echo $OUTPUT->notification('没有权限操作此页面');
    echo $OUTPUT->footer();
    exit;
}
if (is_siteadmin()) {
	$companys = $DB->get_records('company');
}
else {
	$companys = $DB->get_records('company', array('id' => $userCompany->companyid));
}

$url = new moodle_url('/company/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

include_once('index_html.php');
echo $OUTPUT->footer();

function getComponyUser() {
	global $DB, $USER;

	if (is_siteadmin()) {
		return true;
	}
	$uid = $USER->id;
	$companycode = $USER->profile['companycode'];
	$sql = "select * from {company_user} 
	where userid=$uid and companyid in 
	  (select id from {company} where companycode='$companycode')";
	$data  =$DB->get_record_sql($sql);
	return $data;
}