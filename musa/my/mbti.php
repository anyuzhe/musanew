<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');

require_login();

global $DB;

// Start setting up the page
$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('base');
$PAGE->set_pagetype('my-resume');
$PAGE->blocks->add_region('content');
$PAGE->set_title('MBTI评测结果');
$PAGE->set_heading('MBTI评测结果');
$PAGE->set_url('/my/mbti.php', array('id' => $userid));

//引入头文件
echo $OUTPUT->header();

// ----------------------------Tommy test-------------------------------------
$sql = 'SELECT r.questionnaireid,r.id,100*sum(value)/count(s.choice_id) AS score FROM mdl_questionnaire_response r 
JOIN mdl_questionnaire_resp_single s ON r.id=s.response_id
JOIN mdl_questionnaire_quest_choice c ON c.id=s.choice_id
WHERE r.questionnaireid IN (1,3,4,5) AND r.userid=?
GROUP BY r.questionnaireid,r.id
ORDER BY r.questionnaireid,r.id DESC';

// EI
$params = array($USER->id);
$result = $DB->get_records_sql($sql, $params);

$section = 0;
foreach($result as $record){
    if($record->questionnaireid > $section){
        $section = $record->questionnaireid;
// EI
        if($section == 1){
            if($record->score <= 50){
                $MBTI = 'E';
            }else{
                $MBTI = 'I';
            }
        }
// SN
        if($section == 3){
            if($record->score <= 50){
                $MBTI .= 'S';
            }else{
                $MBTI .= 'N';
            }
        }
// TF
        if($section == 4){
            if($record->score <= 50){
                $MBTI .= 'T';
            }else{
                $MBTI .= 'F';
            }
        }
// JP
        if($section == 5){
            if($record->score <= 50){
                $MBTI .= 'J';
            }else{
                $MBTI .= 'P';
            }
        }
    }
}
echo '您的MBTI评测结果为：'.'('.$MBTI.')'.'<br><br>';

$report = $DB->get_record('mbti_results',array('result'=>$MBTI));

echo '------'.$report->desc;

echo "<br><br><button onclick='javascript:location.href=\"/course/view.php?id=10\"'> 继续 </button>";

echo $OUTPUT->footer();