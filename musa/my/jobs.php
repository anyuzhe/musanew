<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_login();
//引入头文件
echo $OUTPUT->header();
$id = $_GET['id'];

$action = $_GET['action'];

$returnurl = new moodle_url('/my/career.php');
$strformheading = ($action == 'add') ? '新增职位' : '编辑职位';
$company = null;
$companycode = $USER->profile['companycode'];

if ($companycode) {
    $company = $DB->get_record('company', array('companycode' => $companycode));
}
if (!empty($id)) {
    if (!$job = $DB->get_record('jobs', array('id' => $id, 'companyid' => $company->id))) {
        print_error('wrongexternalid', 'job');
    }
}
if ($DB->record_exists('area', array('pid' => 0))) {
    $provinceArr = $DB->get_records('area', ['pid' => 0]);
}

if ($DB->record_exists('area', array('id' => $job->city))) {
    $cityArr = $DB->get_record('area', ['id' => $job->city]);
}

$job->skills = array_values($DB->get_records('job_skill', ['jobid' => $id]));

//获取学习计划模板
$testnames = $testcourses = array();
if ($DB->record_exists('competency_template', [])) {
    $templates = $DB->get_records('competency_template');
    foreach ($templates as $template) {
        if (strstr($template->shortname, '测试')) {
            $testnames[$template->id] = $template->shortname;
        } else {
            $testcourses[$template->id] = $template->shortname;
        }
    }
}
//技能三级结构
$allSkills = $DB->get_records('skills_category', ['pid' => 0], '', 'id,category_name');

if ($allSkills) {
    foreach ($allSkills as $k => $akill) {
        $kills = $DB->get_records('skills_category', ['pid' => $akill->id], '', 'id,pid,category_name');
        $allSkills[$k]->child = $kills;
    }
    foreach ($allSkills as $k => $kill) {
        foreach ($kill->child as $ks => $killv) {
            $kills = $DB->get_records('skills', ['category_l2_id' => $killv->id], '', 'id,category_l2_id,name');
            $kill->child[$ks]->childen = $kills;
        }
    }
}

$actions = $_POST['actions'];
if ($actions == "edit") {
    if ($_POST) {
        $job = new stdClass;
        $job->id = $_POST['id'];
        $job->companyid = $company->id;
        $job->company_name = $_POST['company_name'];
        $job->department = $_POST['department'];
        $job->position = $_POST['position'];
        $job->position_code = $_POST['position_code'];
        $job->occupationid = $_POST['occupationid'];
        $job->occupation_rank = $_POST['occupation_rank'];
        $job->job_function = $_POST['job_function'];
        $job->province = $_POST['province'];
        $job->city = $_POST['city'];
        $job->description = $_POST['description'];
        $job->status = $_POST['status'];
        $job->education = $_POST['education'];
        $job->major = $_POST['major'];
        $job->testname = $_POST['testname'];
        $job->testcourse = $_POST['testcourse'];
        $job->vacancy_number = (int)$_POST['vacancy_number'];
        $job->create_time = time();

        $DB->update_record('jobs', $job);
        //   $DB->delete_records('job_skill', ['jobid' => $_POST['id']]);
        for ($i = 1; $i <= 5; $i++) {
            if ($_POST["skill$i"] && !empty($_POST["skill$i"]['used_month'])) {
                if ($_POST["skill$i"]['skillid']) {
                    $arr = $DB->get_record('job_skill', ['jobid' => $_POST['id'], 'skillid' => $_POST["skill$i"]['skillid']]);

                    if ($arr) {
                        $jka = new stdClass;
                        $jka->id = $arr->id;
                        $jka->jobid = $_POST['id'];
                        $jka->skillid = $_POST["skill$i"]['skillid'][0];
                        $jka->used_month = $_POST["skill$i"]['used_month'] != "" ? $_POST["skill$i"]['used_month'] : '12';
                        $jka->level = $_POST["skill$i"]['level'] != "" ? $_POST["skill$i"]['level'] : '1';
                        $jka->opt = $_POST["skill$i"]['opt'] != "" ? $_POST["skill$i"]['opt'] : '可选项';
                        $DB->update_record('job_skill', $jka);
                    } else {
                        $jk = new stdClass;
                        $jk->jobid = $_POST['id'];
                        $jk->skillid = $_POST["skill$i"]['skillid'][0];
                        $jk->used_month = $_POST["skill$i"]['used_month'] != "" ? $_POST["skill$i"]['used_month'] : '12';
                        $jk->level = $_POST["skill$i"]['level'] != "" ? $_POST["skill$i"]['level'] : '1';
                        $jk->opt = $_POST["skill$i"]['opt'] != "" ? $_POST["skill$i"]['opt'] : '可选项';
                        $DB->insert_record('job_skill', $jk);
                    }

                }
            }
        }
        redirect($returnurl);
    }

} else {
    if ($_POST) {
        $job = new stdClass;
        $job->companyid = $company->id;
        $job->company_name = $_POST['company_name'];
        $job->department = $_POST['department'];
        $job->position = $_POST['position'];
        $job->position_code = $_POST['position_code'];
        $job->occupationid = $_POST['occupationid'];
        $job->occupation_rank = $_POST['occupation_rank'];
        $job->job_function = $_POST['job_function'];
        $job->province = $_POST['province'];
        $job->city = $_POST['city'];
        $job->description = $_POST['description'];
        $job->status = $_POST['status'];
        $job->education = $_POST['education'];
        $job->major = $_POST['major'];
        $job->testname = $_POST['testname'];
        $job->testcourse = $_POST['testcourse'];
        $job->vacancy_number = (int)$_POST['vacancy_number'];
        $job->create_time = time();
        $jobId = $DB->insert_record('jobs', $job);

        $skillids = array();
        // 添加技能
        for ($i = 1; $i <= 5; $i++) {
            if ($_POST["skill$i"]) {
                if ($_POST["skill$i"]['skillid']) {
                    $jk = new stdClass;
                    $jk->jobid = $jobId;
                    $jk->skillid = $_POST["skill$i"]['skillid'];
                    $jk->used_month = $_POST["skill$i"]['used_month'] != "" ? $_POST["skill$i"]['used_month'] : '12';
                    $jk->level = $_POST["skill$i"]['level'] != "" ? $_POST["skill$i"]['level'] : '1';
                    $jk->opt = $_POST["skill$i"]['opt'] != "" ? $_POST["skill$i"]['opt'] : '可选项';
                    $DB->insert_record('job_skill', $jk);
                }
            }
        }
        redirect($returnurl);
    }

}


$PAGE->set_title($strformheading);

include 'jobs_html.php';

$PAGE->set_url('/my/jobs.php');

echo $OUTPUT->footer();

