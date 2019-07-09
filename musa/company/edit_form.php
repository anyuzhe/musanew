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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir.'/formslib.php');

class company_edit_form extends moodleform {
    public $modnames = array();

    /**
     * Blog form definition.
     */
    public function definition() {

        $mform =& $this->_form;
        $mform->addElement('header', 'general', '公司基本信息');

        $mform->addElement('text', 'company_code', '公司代码');
        $mform->setDefault('company_code', $this->_customdata['company_code']);
        $mform->disabledIf('company_code', '');

        $mform->addElement('filemanager', 'logo', '公司LOGO', null,
            array('subdirs' => 0, 'maxbytes' => 1, 'areamaxbytes' => 10485760,
              'maxfiles' => 1, 'accepted_types' => array('png', 'jpeg', 'jpg', 'gif')));
        //477966309
        $mform->addElement('text', 'company_alias', '公司简称');
        $mform->addRule('company_alias', '请填写该项', 'required', null, 'client');
        $mform->setType('company_alias', PARAM_TEXT);

        
        $mform->addElement('text', 'company_name', '公司全称');
        $mform->addRule('company_name', '请填写该项', 'required', null, 'client');
        $mform->setType('company_name', PARAM_TEXT);

        $mform->addElement('select', 'scale', '公司规模' , 
        	array('' => '请选择...', 1 => '20人以下', 2 => '20~99人',
        	  3 => '100~500人', 4 => '500人以上'));
        $mform->addRule('scale', '请选择', 'required', null, 'client');

        $mform->addElement('select', 'is_third_party', '是否第三方企业' ,
        	array('' => '请选择...', 1 => '是', 0 => '不是'));
        $mform->addRule('is_third_party', '请选择', 'required', null, 'client');

        $mform->addElement('text', 'website_url', '公司官网' , array('size' => 30));
        $mform->setType('website_url', PARAM_URL);

        $mform->addElement('hidden', 'id');
        $this->add_action_buttons();
    }

    /**
     * Validate the blog form data.
     * @param array $data Data to be validated
     * @param array $files unused
     * @return array|bool
     */
    // public function validation($data, $files) {
    //     global $CFG, $DB, $USER;

    //     $errors = parent::validation($data, $files);

    //     // Validate course association.
    //     if (!empty($data['courseassoc'])) {
    //         $coursecontext = context::instance_by_id($data['courseassoc']);

    //         if ($coursecontext->contextlevel != CONTEXT_COURSE) {
    //             $errors['courseassoc'] = get_string('error');
    //         }
    //     }

    //     // Validate mod association.
    //     if (!empty($data['modassoc'])) {
    //         $modcontextid = $data['modassoc'];
    //         $modcontext = context::instance_by_id($modcontextid);

    //         if ($modcontext->contextlevel == CONTEXT_MODULE) {
    //             // Get context of the mod's course.
    //             $coursecontext = $modcontext->get_course_context(true);

    //             // Ensure only one course is associated.
    //             if (!empty($data['courseassoc'])) {
    //                 if ($data['courseassoc'] != $coursecontext->id) {
    //                     $errors['modassoc'] = get_string('onlyassociateonecourse', 'blog');
    //                 }
    //             } else {
    //                 $data['courseassoc'] = $coursecontext->id;
    //             }
    //         } else {
    //             $errors['modassoc'] = get_string('error');
    //         }
    //     }

    //     if ($errors) {
    //         return $errors;
    //     }
    //     return true;
    // }
}
