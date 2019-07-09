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
$thirdPartyCompanys = $DB->get_records('company', array('is_third_party' => 1));
if(isset($GLOBALS['_GET']['id'])){
    $relationshipId = $GLOBALS['_GET']['id'];
    $relationships = $DB->get_records('company_relationship', array('id' => $relationshipId));
    $relationship = count($relationships)>0?array_pop($relationships):null;
}elseif (count($GLOBALS['_POST'])>0){
    if($GLOBALS['_POST']['id']){
        //修改
        $relationships = $DB->get_records('company_relationship', array('id' => $GLOBALS['_POST']['id']));
        $old = count($relationships)>0?array_pop($relationships):null;
        $log = new stdClass();
        $log->company_relationship_id = $old->id;
        $log->old_status = $old->status;
        $log->status = $GLOBALS['_POST']['status'];
        $log->created_at = date('Y-m-d H:i:s');
        $DB->set_field('company_relationship','status',$GLOBALS['_POST']['status'],['id'=>$GLOBALS['_POST']['id']]);
        $DB->insert_record('company_relationship_log',$log);
    }else{
        //新增
        $new = new stdClass();
        $new->company_id = $GLOBALS['_POST']['company_id'];
        $new->relationship_id = $GLOBALS['_POST']['relationship_id'];
        $new->status = $GLOBALS['_POST']['status'];
        $new->created_at = date('Y-m-d H:i:s');
        if(!$new->company_id || !$new->relationship_id || $new->status===''){
            $message = '请填写完全';
            $tokenlisturl = new moodle_url("/company/relationship/edit.php");
            redirect($tokenlisturl);
            return;
        }else{
            $DB->insert_record('company_relationship',$new);
        }
    }
    $tokenlisturl = new moodle_url("/company/relationship");
    redirect($tokenlisturl);
    return;
}
$companyKeysArr = [];
foreach ($companys as $k=>$company) {
    $companyKeysArr[$company->id] = $company;
}

$url = new moodle_url('/company/relationship/edit.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

include_once('edit_html.php');
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