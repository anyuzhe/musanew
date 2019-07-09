<?php
require_once('../../config.php');

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
$companys = $DB->get_records('company');
$companyKeysArr = [];
foreach ($companys as $k=>$company) {
    $companyKeysArr[$company->id] = $company;
}

$companyRelationships = $DB->get_records('company_relationship');
foreach ($companyRelationships as &$v) {
    $v->company = $companyKeysArr[$v->company_id];
    $v->relationship = $companyKeysArr[$v->relationship_id];
    switch ($v->status){
        case -1:
            $v->status_str = '已解除';
            break;
        case -2:
            $v->status_str = '审核不通过';
            break;
        case 0:
            $v->status_str = '待审核';
            break;
        case 1:
            $v->status_str = '正常';
            break;
        default:
            $v->status_str = '审核不通过';
            break;

    }
}

$url = new moodle_url('/company/relationship/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

include_once('relationship_html.php');
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