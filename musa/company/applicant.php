<?php
require_once(__DIR__ . '/../config.php');

$companyid = $_GET['id'];

if (!$companyid) exit;
$siteurl = '/company/applicant.php?id='.$companyid;
$PAGE->set_url($siteurl);

$data = [];
$type = $_GET['type'] ?: 'applicant';

switch ($type) {
  case 'applicant':
    $sql = "select ca.userid as id,r.id as resumeid, rb.startwork,rb.career,u.firstname,u.lastname
    from {company_accept} ca
    left join {user} u on ca.userid = u.id
    left join {resume} r on ca.userid = r.userid
    left join {resume} rb on r.id = rb.resumeid
    where company_accept_id = $companyid
    order by accept_time desc";
    $data = $DB->get_records_sql($sql);
    break;
  case 'agent':
    $sql_ = "SELECT * from {resume} as r
    left join {resume} rb on r.id = rb.resumeid
    where r.source = 2 and r.companyid =".$companyid;
    $data = $DB->get_records_sql($sql_);
    break;
  case 'team' :
    $sql = "SELECT cu.userid as id,r.id as resumeid, rb.startwork,rb.career,u.firstname,u.lastname
      from {company_user} cu
        left join {user} u on cu.userid = u.id 
        left join {resume} r on cu.userid = r.userid
        left join {resume} rb on r.id = rb.resumeid
        where cu.companyid=$companyid";
        $data = $DB->get_records_sql($sql);
  default:
    break;
}
  

if (!$data) {
    $match = array();
    echo $OUTPUT->heading('没有相关内容');

    $table = NULL;
} else {
  $table = new html_table();
  $table->id = "teamer";
  $table->head = array ();
  $table->head[] = '姓名';
  $table->head[] = '工作年限';
  $table->head[] = '职业类别';
  $table->head[] = '个人档案';
  $table->head[] = '简历';
  $table->head[] = '评估报告';

  foreach ($data as $id => $user) {
    $row = array ();
    $row[] = $_GET['type'] == 'agent'? $user->name : fullname($user);
    $row[] = !empty($user->startwork) ? date('Y') - $user->startwork : '-';
    $row[] = $user->career ?: '-';
    $row[] = $_GET['type'] == 'agent'? '-' :
      html_writer::link(new moodle_url('/user/profile.php',
          array('id'=> $user->id)
        ),  '查看');
    if ($_GET['type'] == 'agent') {
      $report = '-';
       $resume = html_writer::link(new moodle_url('/company/resume.php', 
            array('id'=> $user->resumeid)
          ),  '查看編輯');
    }
    else {
      if (!$user->resumeid) {
        $report = '-';
        $resume = '-';
      } else {
          $resume = html_writer::link(new moodle_url('/user/resume.php', 
            array('id'=> $user->id)
          ),  '查看');
          $report = html_writer::link(new moodle_url('/my/report/estimate_pdf.php',
            array('id'=> $user->id)
          ),  '查看');
      }
    }
    
    $row[] = $resume;
    $row[] = $report;
    $table->data[] = $row;
  }

}
// Print header.
echo $OUTPUT->header();

if ($table) {

	echo $OUTPUT->heading("人员(".count($data).')');

  echo html_writer::start_tag('div');
  echo html_writer::start_tag('form', array('action' => $siteurl, 'method' => 'get'));
  echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id',
     'value' => $companyid));
	echo html_writer::select(array('team' => '我的员工', 'applicant'=>'已投递的', 'agent'=>'已添加的'), 
    'type', $type);
  echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => '查看'));
  echo html_writer::end_tag('form');

  echo html_writer::table($table);
  echo html_writer::end_tag('div');
 }

// Print footer.
echo $OUTPUT->footer();