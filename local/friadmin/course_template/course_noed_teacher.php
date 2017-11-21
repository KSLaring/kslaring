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
 * Course Template - Non editing Teachers
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/10/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Adding non editing teachers
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('classes/ct_teacher_form.php');
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
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$contextCourse  = context_course::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_noed_teacher.php',array('id' => $courseId,'ct' => $courseTemplate));
$returnUrl      = new moodle_url('/local/friadmin/course_template/course_tags.php',array('id' => $courseId));

$course         = get_course($courseId);
$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_noed_teachers', 'local_friadmin');
$instance       = null;

// Check permissions/Capability
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

// Form
$form = new ct_enrolment_noed_teachers_form(null,array($courseId,$courseTemplate,$addSearch,$removeSearch));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {
    if (isset($data->submitbutton) && ($data->submitbutton)) {
        $_POST = array();
        redirect($returnUrl);
    }else {
        // Add teachers
        if (!empty($data->add_sel)) {
            if (isset($data->addselect)) {
                CourseTemplate::assign_teacher($courseId,$data->addselect,true);
            }//if_addselect
        }//if_add

        // Remove teachers
        if (!empty($data->remove_sel)) {
            if (isset($data->removeselect)) {
                CourseTemplate::unassign_teacher($courseId,$data->removeselect,true);
            }//if_removeselect
        }//if_remove
    }//if_continues
}//if_form

// Header
echo $OUTPUT->header();

echo $OUTPUT->heading($strSubTitle,3);

$form->display();

// Initialise selectors
CourseTemplate::init_teachers_selectors($addSearch,$removeSearch,$courseId,1);

// Footer
echo $OUTPUT->footer();
