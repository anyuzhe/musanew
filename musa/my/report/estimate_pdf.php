<?php

require_once(__DIR__ . '/../../config.php');
require_once('../lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once('../service.php');

const  CATEGORY1 = 1; //IT专业技能
const  CATEGORY2 = 2;//IT管理技能
const  CATEGORY3 = 3;//业务项目能力
global $DB;
$id = $_GET['id'];
$profile = (array)profile_user_record($id);
$age = getAge($profile['birthdate']);
$profile['gender'] != "" ? $profile['gender'] : '保密';

if ($DB->record_exists('user', array('id' => $id))) {
    $users = $DB->get_record('user', ['id' => $id]);
}
if ($DB->record_exists('resume', array('userid' => $id))) {
    $resume = $DB->get_record('resume', ['userid' => $id]);
    $resumeid = $resume->id;
}

if (!$resumeid) {
    echo "用户未创建简历";
    exit;
}
if ($DB->record_exists('resume_basic', array('resumeid' => $resumeid))) {
    $basic = $DB->get_record('resume_basic', ['resumeid' => $resumeid]);
}

/**
 * 目标职位
 */
if ($DB->record_exists('company_accept', array('userid' => $id))) {
    $accept = $DB->get_records('company_accept', ['userid' => $id]);
}
$arr = $jobsid = array();
$position = '';
$jobsidStr = '';
if ($accept) {
    foreach ($accept as $v) {
        $jobs = $DB->get_record('jobs', ['id' => $v->jobsid]);
        $arr[] = $jobs->position;
        $jobsid[] = $v->jobsid;
    }
    if ($arr) {
        $position = implode("/", $arr);
    }
    if ($jobsid) {
        $jobsidStr = implode(",", $jobsid);
    }
}

/**
 * 必须项 首选项 加分项
 */
//获取用户简历所有技能
if ($DB->record_exists('resume_skill', array('resumeid' => $resumeid))) {
    $resumeSkill = $DB->get_records('resume_skill', ['resumeid' => $resumeid]);
}

//获取投递岗位所需要的技能
$sql = "SELECT * FROM `mdl_job_skill` WHERE jobid IN ({$jobsidStr}) ";
$skillidArr = $DB->get_records_sql($sql);

$percentage = getPercentage($resumeSkill, $skillidArr);

/**
 *能力测验
 */
if ($DB->record_exists('competency_plan', array('userid' => $id))) { //获取此人是否绑定了学习计划
    $plan = $DB->get_records('competency_plan', ['userid' => $id]);
}
$template = $competency = $course = $quiz = $Arr = array();
if ($plan) {  //根据绑定的学习计划获取学习计划模板
    $templateStr = '';
    foreach ($plan as $p) {
        $template[] = $p->templateid;
    }
    $templateStr = implode(',', $template);
    //根据模板id获取此模板下绑定的能力
    $sql = "SELECT competencyid FROM `mdl_competency_templatecomp` WHERE templateid IN  ({$templateStr}) ";
    $competencyArr = $DB->get_records_sql($sql);
    if ($competencyArr) {
        $competencyStr = '';
        foreach ($competencyArr as $v) {
            $competency[] = $v->competencyid;
        }
        $competencyStr = implode(',', $competency);
        //根据能力id获取此能力下绑定的课程
        $sql1 = "SELECT courseid FROM `mdl_competency_coursecomp` WHERE competencyid IN ({$competencyStr}) ";
        $courseArr = $DB->get_records_sql($sql1);
        if ($courseArr) {
            //获取用户所答试卷相关信息
            $sql3 = "SELECT a.`quiz`,a.`timestart`,a.`timefinish`,b.grade,c.name,c.introformat FROM `mdl_quiz_attempts` a LEFT JOIN mdl_quiz_grades b on a.`quiz` = b.`quiz` AND a.`userid` =b.`userid` LEFT JOIN mdl_quiz c ON a.`quiz` =c.id WHERE a.`userid` =:userid  AND a.state='finished'";
            $params = [
                'userid' => $id,
            ];
            $info = $DB->get_records_sql($sql3, $params);
            if ($info) {
                foreach ($info as $k => $q) {
                    $Arr[$k]['id'] = $q->quiz; //id
                    $Arr[$k]['name'] = $q->name;//试卷名称
                    $Arr[$k]['introformat'] = $q->introformat;//限制时间
                    $Arr[$k]['grade'] = $q->grade;//测试分数
                    $Arr[$k]['time'] = ($q->timefinish - $q->timestart);
                }
            }
        }
    }
}


/**
 * 性格倾向分析
 */
$sql = 'SELECT r.questionnaireid,r.id,100*sum(value)/count(s.choice_id) AS score FROM mdl_questionnaire_response r 
JOIN mdl_questionnaire_resp_single s ON r.id=s.response_id
JOIN mdl_questionnaire_quest_choice c ON c.id=s.choice_id
WHERE r.questionnaireid IN (1,3,4,5) AND r.userid=?
GROUP BY r.questionnaireid,r.id
ORDER BY r.questionnaireid,r.id DESC';
$params = array($id);
$result = $DB->get_records_sql($sql, $params);
$MBTI = getMbti($result);
$str = str_replace(",", "", str_replace("'", "", $MBTI));

$report = $DB->get_record('mbti_results', array('result' => $str));
$desc = '------' . $report->desc;

/**
 * 柱状图技能匹配
 */
$specialityArr = getSkill($DB, CATEGORY1);//IT专业技能
$manageArr = getSkill($DB, CATEGORY2);// IT管理技能
$businessArr = getSkill($DB, CATEGORY3);// IT管理技能
if ($specialityArr) {
    $speciality = getSkillmate($specialityArr);
    $specialityStr = getLevelStr($DB, $specialityArr, $resumeid);
}
if ($manageArr) {
    $manage = getSkillmate($manageArr);
    $manageStr = getLevelStr($DB, $manageArr, $resumeid);
}
if ($businessArr) {
    $business = getSkillmate($businessArr);
    $businessStr = getLevelStr($DB, $businessArr, $resumeid);
}

function getSkill($DB, $cate)
{
    if ($DB->record_exists('skills', array('status' => 1, 'category_l1_id' => $cate))) {
        $Skills = $DB->get_records('skills', ['status' => 1, 'category_l1_id' => $cate]);
    }
    return $Skills;
}

function getLeveName($DB, $skillid, $resumeid)
{
    if ($DB->record_exists('resume_skill', array('status' => 1, 'resumeid' => $resumeid, 'skillid' => $skillid))) {
        $resumeSkill = $DB->get_record('resume_skill', ['status' => 1, 'resumeid' => $resumeid, 'skillid' => $skillid]);
    }
    if ($resumeSkill) {
        return $resumeSkill->level;
    } else {
        return "未知";
    }
}

function getLevelStr($DB, $Arr, $resumeid)
{
    $arr = array();
    $str = "";
    foreach ($Arr as $k => $s) {
        $arr[] = getLevel(getLeveName($DB, $s->id, $resumeid));
    }

    if ($arr) {
        $str = implode(",", $arr);
    }
    return $str;
}

include 'estimate_html.php';

$PAGE->set_url('/my/estimate_pdf.php');


