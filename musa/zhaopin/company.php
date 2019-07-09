<?php

/**
 * 弃用
 * 将转为api接口
 */
require_once(__DIR__ . '/../config.php');
require_once('../my/service.php');
global $DB;
require_login();
echo '<script type="text/javascript" src="../public/js/jquery-3.4.1.min.js"></script>';
$action = $_POST['action'];
$userid = $USER->id;
if ($action && $action == 'add') {
    $plan = $_POST['plan'];
    $data = array();
    if ($DB->record_exists('company_user', array('userid' => $userid))) {
        $company = $DB->get_record('company_user', ['userid' => $userid]);
    }
    $data['userid'] = (int)$_POST['userid'];
    $data['companyid'] = (int)$company->companyid;
    $data['company_accept_id'] = (int)$_POST['companyid'];
    $data['jobsid'] = (int)$_POST['jobsid'];
    $data['accept_time'] = time();
    $ret = $DB->insert_record('company_accept', $data);
    if ($ret > 0) {
        if ($plan) { //
            $result = \core_competency\api::create_plan_from_template($plan, $userid); //创建学习计划
            //跳转到学习计划
            echo "<script> alert('应聘成功!');</script>";
            redirect("/admin/tool/lp/plan.php?id= $plan");
            exit;
        } else {
            //打印评估报告
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/estimate_pdf'; //放置pdf文件夹
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true); //创建和写入权限
            }
            if (!$USER) {  //登录超时重新登录
                redirect("/login/index.php");
                exit;
            }
            $html = $CFG->wwwroot . '/my/report/estimate_pdf.php?id=' . $userid; //需要导出pdf地址
            $pdfName = date("Y-m-d_His") . '.pdf';
            $path = $dir . '/' . $pdfName;
            if (strstr(php_uname('s'), "Windows ")) {
                shell_exec(" wkhtmltopdf $html $path"); //windows  wkhtmltopdf 调用
            } else {
                shell_exec("/usr/local/bin/ wkhtmltopdf $html $path"); // linux  wkhtmltopdf 调用
            }
            upload($path, $pdfName);
        }

    } else {
        echo "<script>alert('应聘失败!');</script>";
    }


}
echo $OUTPUT->header();
$companyid = $_GET['companyid'];
$jobid = $_GET['jobid'];

if ($DB->record_exists('company', array('id' => $companyid))) {
    $company = $DB->get_record('company', ['id' => $companyid]);
}

if ($DB->record_exists('jobs', array('id' => $jobid))) {
    $jobs = $DB->get_record('jobs', ['id' => $jobid]);
}
if ($jobs->occupation_rank == 1) {
    $occupation_rank = '初级';
} else if ($jobs->occupation_rank == 2) {
    $occupation_rank = '中级';
} else {
    $occupation_rank = '高级';
}
if ($jobs->job_function == 0) {
    $job_function = '全职';
} else if ($jobs->job_function == 1) {
    $job_function = '实习';
} else {
    $job_function = '兼职';
}
if ($jobs->job_function == 1) {
    $education = '专科及以上';
} else if ($jobs->job_function == 2) {
    $education = '实习';
} else if ($jobs->job_function == 3) {
    $education = '硕士及以上';
} else if ($jobs->job_function == 4) {
    $education = '博士';
} else {
    $education = '不限';
}

if ($DB->record_exists('job_occupations', array('id' => $jobs->occupationid))) {
    $occupationArr = $DB->get_record('job_occupations', ['id' => $jobs->occupationid]);
}

if ($DB->record_exists('job_skill', array('jobid' => $jobs->id))) {
    $job_skill = $DB->get_records('job_skill', ['jobid' => $jobs->id]);
}
if ($job_skill) {
    foreach ($job_skill as $k => $v) {
        $skill = $DB->get_record('skills', ['id' => $v->skillid]);
        $job_skill[$k]->name = $skill->name;
    }
}
$occupation = $occupationArr->name;
if ($DB->record_exists('area', array('id' => $jobs->province))) {
    $provinces = $DB->get_record('area', ['id' => $jobs->province]);
}
$province = $provinces->cname;
if ($DB->record_exists('area', array('id' => $jobs->city))) {
    $citys = $DB->get_record('area', ['id' => $jobs->city]);
}
$city = $citys->cname;
//判断用户是否应聘过该企业
$sql = "SELECT * FROM {company_accept} WHERE company_accept_id = :company_accept_id AND userid= :userid AND jobsid =:jobsid";

$params = [
    'company_accept_id' => $companyid,
    'userid' => $userid,
    'jobsid' => $jobid
];

$companyUser = $DB->get_records_sql($sql, $params);
include 'company_html.php';
$PAGE->set_title('公司简介');
$PAGE->set_url('/zhaopin/company.php');
echo $OUTPUT->footer();



