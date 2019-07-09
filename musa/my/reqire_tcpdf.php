<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once('service.php');

$id = $_GET['id'];
$profile = (array)profile_user_record($id);
$birthdate = date("Y-m-d", $profile['birthdate']);
$profile['gender'] != "" ? $profile['gender'] : '保密';

if ($DB->record_exists('resume', array('userid' => $id))) {
    $resume = $DB->get_record('resume', ['userid' => $id]);
    $resumeid = $resume->id;
}
if (!$resumeid) {
    echo "用户未创建简历";exit;
}
if ($DB->record_exists('user', array('id' => $id))) {
    $users = $DB->get_record('user', ['id' => $id]);
}
if ($DB->record_exists('resume_basic', array('resumeid' => $resumeid))) {
    $basic = $DB->get_record('resume_basic', ['resumeid' => $resumeid]);
}
if ($DB->record_exists('resume_company', array('resumeid' => $resumeid))) {
    $companys = $DB->get_records('resume_company', ['resumeid' => $resumeid]);
}
// 获取简历对应技能列表
if ($DB->record_exists('resume_skill', array('resumeid' => $resumeid))) {
    $sql = "SELECT sk.name AS name, rs.level, rs.used_month FROM {resume_skill} AS rs INNER JOIN {skills} AS sk ON rs.skillid = sk.id WHERE rs.resumeid = :resumeid ORDER BY rs.id DESC";
    $params = [
        'resumeid' => $resumeid,
    ];
    $skills = $DB->get_records_sql($sql, $params);
}
if ($DB->record_exists('resume_project', array('resumeid' => $resumeid))) {
    $projects = $DB->get_records('resume_project', ['resumeid' => $resumeid]);
}
if ($DB->record_exists('resume_education', array('resumeid' => $resumeid))) {
    $educations = $DB->get_records('resume_education', ['resumeid' => $resumeid]);
}
include 'reqire_tcpdf_html.php';
$PAGE->set_url('/my/reqire_tcpdf.php');
$PAGE->set_title('我的简历');