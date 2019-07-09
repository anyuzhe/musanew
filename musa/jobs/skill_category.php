<?php
require_once(__DIR__ . '/../config.php');

$pid = $_GET['pid'] ?: 0;
$level = $_GET['level'] ?: 1;
$action = $_POST['action'] ?: '';
$categories = $DB->get_records('skills_category', array('pid' => $pid));

$pname = $pid!= 0 ? $DB->get_record('skills_category',
	array('id' => $pid), 'category_name')->category_name : '无';


$redirecturl = $_SERVER['REQUEST_URI'];
$ct = new Category;

if ($action) {
	switch ($action) {
		case 'add':
			$obj = new stdclass;
			$obj->category_name = $_POST['fieldname'];
			$obj->level = $level;
			$obj->pid = $pid;
			$ct->addCategory($obj);
			break;
		case '':
		default:
			break;
	}
	redirect($redirecturl);
}


echo $OUTPUT->header();
echo $OUTPUT->heading($title);

include_once('skill_category_html.php');

echo $OUTPUT->footer();

class Category{

	public $fieldname;
	public $pid;
	public $level;

	private function beforeAdd($obj) {
		global $DB;

		$params = json_decode(json_encode($obj),1);
		$exists = $DB->record_exists('skills_category', $params);
		if ($exists) {
			 print_error('该记录已存在', 'job'); 
		}
		return !$exists;
	}

	public function addCategory($obj) {
		global $DB;
		if(!self::beforeAdd($obj)) {
			return false;
		}
		$cid = $DB->insert_record('skills_category', $obj);
		return $cid;
	}
}

