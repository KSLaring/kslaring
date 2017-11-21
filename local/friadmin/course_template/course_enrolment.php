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
 * Course Template - Enrolment Methods
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Enrolment Methods
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('classes/ct_enrolment_form.php');
require_once('../../../course/lib.php');

global $USER,$PAGE,$SITE,$OUTPUT;

require_login();
// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Params
$courseId       = required_param('id',PARAM_INT);
$courseTemplate = required_param('ct',PARAM_INT);
$waitinglist    = optional_param('waitinglist',0,PARAM_INT);
$contextCourse  = context_course::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_enrolment.php',array('id' => $courseId,'ct' => $courseTemplate));
$returnUrl      = new moodle_url('/local/friadmin/course_template/course_teacher.php',array('id' => $courseId,'ct' => $courseTemplate));

$course         = get_course($courseId);
$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_enrolment', 'local_friadmin');
$instance       = null;
$action         = null;

// Permissions/Capability
if (!has_capability('local/friadmin:view',context_system::instance())) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
$PAGE->navbar->add($strTitle);
$PAGE->navbar->add($strSubTitle);

/**
 * @updateDate      27/06/2016
 * @author          eFaktor     (fbv)
 * 
 * Description
 * Different enrolment method based on course format
 */
// Form
switch ($course->format) {
    case 'classroom':
    case 'classroom_frikomport':
        if ($waitinglist) {
            // Enrol instance
            $instance   = CourseTemplate::get_enrol_instance($courseId,$courseTemplate,$course->format);
            $form       = new ct_enrolment_settings_form(null,array($courseId,$waitinglist,$instance,$courseTemplate));
        }else {
            $form = new ct_enrolment_form(null,array($courseId,$courseTemplate));
        }

        break;
    case 'elearning_frikomport':
    case 'netcourse':
        // Enrol isntance
        $instance   = CourseTemplate::get_enrol_instance($courseId,$courseTemplate,$course->format);
        $form       = new ct_self_enrolment_settings_form(null,array($courseId,$instance,$courseTemplate));

        break;
}


if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {
    switch ($course->format) {
        case 'classroom':
        case 'classroom_frikomport':
            if ($waitinglist) {
                if ($data->instanceid) {
                    // Update
                    CourseTemplate::update_waiting_enrolment($data);
                }else {
                    // New
                    CourseTemplate::create_waiting_enrolment($data);
                }
                redirect($returnUrl);
            }
            break;
        case 'elearning_frikomport':
        case 'netcourse':
            if ($data->instanceid) {
                $action = 'update';
            }else {
                $action = 'add';
            }
            // Update/Create
            CourseTemplate::self_enrolment($data,$action);

            redirect($returnUrl);

            break;
    }//course_format
}

/* Header   */
echo $OUTPUT->header();

echo $OUTPUT->heading($strTitle,2);
echo $OUTPUT->heading($strSubTitle,3);

$form->display();

/* Footer   */
echo $OUTPUT->footer();


