<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main login page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once('lib.php');

//redirect_if_major_upgrade_required();

$token = optional_param('token', '', PARAM_RAW);
$course_id = optional_param('course_id', 15,PARAM_INT);

//拼写检查有助于定位代码、注释和文字中的拼写错误和拼写错误，并通过单击修复它们。
$resendconfirmemail = optional_param('resendconfirmemail', false, PARAM_BOOL);

//instance  检查报告异常，这些异常既没有包含在try-catch块中，也没有使用“@throws”标记进行记录。
$context = context_system::instance();
$PAGE->set_url("$CFG->wwwroot/login/index.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

$token_obj = $DB->get_record('external_tokens', ['token' => $token]);
$user = $DB->get_record('user', ['id' => $token_obj->userid]);
if ($user) {

    /// Let's get them all set up.
    complete_user_login($user);

    \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

    // sets the username cookie
    if (!empty($CFG->nolastloggedin)) {
        // do not store last logged in user in cookie
        // auth plugins can temporarily override this from loginpage_hook()
        // do not save $CFG->nolastloggedin in database!

    } else if (empty($CFG->rememberusername) or ($CFG->rememberusername == 2 and empty($frm->rememberusername))) {
        // no permanent cookies, delete old one if exists
        set_moodle_cookie('');

    } else {
        set_moodle_cookie($USER->username);
    }

    $urltogo = $CFG->wwwroot.'/course/view.php?id='.$course_id;
    $SESSION->lang = 'zh_cn';
    $SESSION->wantsurl = $urltogo;
    redirect(new moodle_url(get_login_url(), array('testsession' => $USER->id)));
}else{
    die('缺少课程id或者未登陆');
}
