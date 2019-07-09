<?php

require_once('../config.php');
require_once('edit_form.php');

require_login();
$context = context_system::instance();
$canaccessallgroups = has_capability('moodle/site:accessallgroups', 
	$context);
if (!$canaccessallgroups) {
   redirect('/my');
}
	
$returnurl = new moodle_url('/company/');

$company = new stdClass();
$id = optional_param('id', null, PARAM_INT);
if (!empty($id)) {
    if (!$company = $DB->get_record('company', array('id' => $id))) {
        print_error('不存在该企业信息', 'companyError');
    }
    $draftitemid = file_get_submitted_draft_itemid('logo');
    file_prepare_draft_area($draftitemid, $context->id, 'company_logo', 'attachment', $id);
    $company->logo = $draftitemid;
}

$action = (empty($id)) ? 'add' : 'edit';
$strformheading = ($action == 'add') ? '新增企业' : '编辑企业信息';

$args = array(
    'company_code' => $company->company_code ?: getCompanyCode(),
);

$editform = new company_edit_form(null, $args);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} 
else if ($data = $editform->get_data()) {
    // Save stuff in db.
    switch ($action) {
        case 'add':
            $companyinfo = new \stdClass();
            $companyinfo->company_code = $data->company_code;
            $companyinfo->company_name = $data->company_name;
            $companyinfo->company_alias = $data->company_alias;
            $companyinfo->is_third_party = $data->is_third_party;
            $companyinfo->scale = (int)$data->scale;
            $companyinfo->website_url = $data->website_url;
            if($data->logo){
                $companyinfo->logo = (int)$data->logo;
                file_save_draft_area_files($data->logo, $context->id, 'company_logo', 'attachment', $company->id);
            }
            $companyinfo->created_at = date('Y-m-d H:i:s');
            $company->id = $DB->insert_record('company', $companyinfo);
            file_save_draft_area_files($data->logo, $context->id, 'company_logo', 'attachment', $company->id);
            break;

        case 'edit':
            if ($id && $DB->record_exists('company', array('id' => $id))) {
                $companyinfo = new \stdClass();
                $companyinfo->id = $id;
                $companyinfo->company_code = $data->company_code;
                $companyinfo->company_name = $data->company_name;
                $companyinfo->company_alias = $data->company_alias;
                $companyinfo->is_third_party = $data->is_third_party;
                $companyinfo->scale = (int)$data->scale;
                $companyinfo->website_url = $data->website_url;
                if($data->logo){
                    $companyinfo->logo = (int)$data->logo;
                    file_save_draft_area_files($data->logo, $context->id, 'company_logo', 'attachment', $company->id);
                }
                $DB->update_record('company', $companyinfo);
            } else {
                print_error('未知错误');
            }

            break;

        default :
            print_error('invalidaction');
    }
    
    redirect($returnurl);
}


$PAGE->set_title($strformheading);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();

$company->comapnycode = getCompanyCode();

$editform->set_data($company);
$editform->display();

echo $OUTPUT->footer();


function getCompanyCode() {
	global $DB;
    
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
	$code = $chars{rand(0, strlen($chars))};
	$code .= substr(time(), -7) . rand(10, 99);
	if ($DB->record_exists('company', array('company_code' => $code))) {
		getCompanyCode();
	}
	return $code;
}