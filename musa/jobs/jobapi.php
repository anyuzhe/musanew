<?php
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

$params = $_GET;
if (!$params['method']) {
  print_error('无效参数');
}

if(function_exists($params['method'])) {
  $params['method']($params['id']);
}

 function closejob($id) {
 	global $DB;

 	$obj = new stdClass;
 	$obj->id = $id;
 	$obj->status = -1;
 	if($DB->update_record('jobs', $obj)) {
 		redirect('/my/career.php');
 	}
 }
 
//获取地区列表
 function arealist($pid) {
 	global $DB;

 	$area = $DB->get_records('area', array('pid' => $pid));
 	echo json_encode($area);exit;
 }