<?php
/**
 * 弃用
 * 将转为api接口
 */
require_once(__DIR__ . '/../config.php');
global $DB;
require_login();
echo $OUTPUT->header();

//获取所有职位
$position = $_GET['position'];
$education = $_GET['education'];
$occupation = $_GET['occupation'];
$occupation_rank = $_GET['occupation_rank'];
$job_function = $_GET['job_function'];
$province = $_GET['province'];
$city = $_GET['city'];

$where = 'j.status =0';
if ($position && isset($position)) {
    $where .= " and j.position  LIKE  " . "'%$position%'";
}
if ($education && isset($education)) {
    $where .= " and j.education >= " . $education;
}
if ($occupation && isset($occupation)) {
    $where .= " and j.occupationid = " . $occupation;
}
if ($occupation_rank && isset($occupation_rank)) {
    $where .= " and j.occupation_rank = " . $occupation_rank;
}
if ($job_function && isset($job_function)) {
    $where .= " and j.job_function = " . $job_function;
}
if ($province >= 0 && isset($province)) {
    $where .= " and j.province = " . $province;
}
if ($city && isset($city)) {
    $where .= " and j.city = " . $city;
}

if ($DB->record_exists('job_occupations', array())) {
    $occupationArr = $DB->get_records('job_occupations', []);
}
$sql = "SELECT  * FROM `mdl_jobs` j WHERE " . $where;

$jobs = $DB->get_records_sql($sql);
foreach ($jobs as $k => $v) {
    $company = $DB->get_record('company', ['id' => $v->companyid]);
    $jobs[$k]->companyname = $company->companyname;
}

if ($DB->record_exists('area', array('pid' => 0))) {
    $provinceArr = $DB->get_records('area', ['pid' => 0]);
}

if ($DB->record_exists('area', array('id' => $city))) {
    $cityArr = $DB->get_record('area', ['id' => $city]);
}
include 'zhaopin_html.php';
$PAGE->set_title('招聘求职信息');
$PAGE->set_url('/zhaopin/zhaopin.php');
echo $OUTPUT->footer();

