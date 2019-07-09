<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Select site administrators.
 *
 * @package    core_role
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');



admin_externalpage_setup('admins');
if (!is_siteadmin()) {
    die;
}

$companyid = $_GET['id'];
if (!$companyid) {
    print_error('不存在该企业');
}
$pageurl ='/company/roles/admins.php?id='.$companyid;
$PAGE->set_url($pageurl);

$admisselector = new core_role_admins_existing_selector();
$admisselector->set_extra_fields(array('username', 'email'));

$potentialadmisselector = new core_role_admins_potential_selector();
$potentialadmisselector->set_extra_fields(array('username', 'email'));

if (optional_param('add', false, PARAM_BOOL)) {
    if ($userstoadd = $potentialadmisselector->get_selected_users()) {
        $user = reset($userstoadd);
    	$cuid = $DB->get_record('company_user', 
    		array('company_id' => $companyid,'user_id' => $user->id), 'id');
        $companymanagerid = $DB->get_record('company_role', array('alias' => 'manager'), 'id');
        $obj = new stdClass;
        $obj->id = $cuid->id;
        $obj->company_role_id = $companymanagerid->id;
        $DB->update_record('company_user', $obj);
    }

} else if (optional_param('remove', false, PARAM_BOOL)) {
        $cuid = $DB->get_record('company_user', 
    		array('company_id' => $companyid,'user_id' => $_POST['removeselect']), 'id');
        $obj = new stdClass;
        $obj->id = $cuid->id;
        $obj->company_role_id = null;
        $DB->update_record('company_user', $obj);

}


$company = $DB->get_record('company', array('id' => $companyid));
$sql = "select id,username,email from {user} where id in
		(select user_id from {company_user} as cu join {company_role} as cr
 		on cu.company_role_id = cr.id where cu.company_id = $companyid 
            and cr.alias = 'manager')";
$companyadmins = $DB->get_records_sql($sql);

$adminids = array_column($companyadmins, 'id');

$sql1 = "select u.id as id,username,email from {company_user} cu 
	left join {user} u
	on cu.user_id = u.id where cu.company_id=$companyid" ;
$companyusers = $DB->get_records_sql($sql1);

foreach ($companyusers as $id => $user) {
	if(in_array($user->id, $adminids)) {
		unset($companyusers[$id]);
	}
}
// Print header.
echo $OUTPUT->header();

include_once('admins_html.php');

echo $OUTPUT->footer();
