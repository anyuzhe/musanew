<?php
require_once(__DIR__ . '/../config.php');

$companyid = $_GET['id'];

if (!$companyid) exit;
$siteurl = '/company/user.php?id='.$companyid;
$PAGE->set_url($siteurl);

$data = [];

$sql = "SELECT u.*,info.*,role.name as role_name
  from {company_user} cu
    left join {user} u on cu.user_id = u.id 
    left join {user_basic_info} info on cu.user_id = info.user_id
    left join {company_role} role on cu.company_role_id = role.id
    where cu.company_id=$companyid";
    $data = $DB->get_records_sql($sql);

//var_dump($data);die;
if (!$data) {
    $match = array();
    echo $OUTPUT->heading('没有相关内容');

    $table = NULL;
} else {
  $table = new html_table();
  $table->id = "teamer";
  $table->head = array ();
  $table->head[] = '姓名';
  $table->head[] = '邮箱';
  $table->head[] = '手机号';
  $table->head[] = '角色';

  foreach ($data as $id => $user) {
    $row = array ();
    $row[] = $user->realname;
    $row[] = $user->email;
    $row[] = $user->phone;
    $row[] = $user->role_name;
//    $row[] = $_GET['type'] == 'agent'? '-' :
//      html_writer::link(new moodle_url('/user/profile.php',
//          array('id'=> $user->id)
//        ),  '查看');
//    if ($_GET['type'] == 'agent') {
//      $report = '-';
//       $resume = html_writer::link(new moodle_url('/company/resume.php',
//            array('id'=> $user->resumeid)
//          ),  '查看編輯');
//    }
//    else {
//      if (!$user->resumeid) {
//        $report = '-';
//        $resume = '-';
//      } else {
//          $resume = html_writer::link(new moodle_url('/user/resume.php',
//            array('id'=> $user->id)
//          ),  '查看');
//          $report = html_writer::link(new moodle_url('/my/report/estimate_pdf.php',
//            array('id'=> $user->id)
//          ),  '查看');
//      }
//    }
//
//    $row[] = $resume;
//    $row[] = $report;
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
//	echo html_writer::select(array('team' => '我的员工', 'applicant'=>'已投递的', 'agent'=>'已添加的'),
//    'type', $type);
  echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => '查看'));
  echo html_writer::end_tag('form');

  echo html_writer::table($table);
  echo html_writer::end_tag('div');
 }

// Print footer.
echo $OUTPUT->footer();