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
 * Course Template - Course Template
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('../../../course/lib.php');

require_login();

/* PARAMS   */
$courseId       = required_param('id',PARAM_INT);
$course         = get_course($courseId);
$contextCourse  = context_course::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_template.php',array('id' => $courseId));

$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_enrolment', 'local_friadmin');

$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));

/* Check Permissions/Capability */
if (!has_capability('local/friadmin:view',context_system::instance())) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

/* Header   */
echo $OUTPUT->header();
$info = array('id'          => $course->id,
              'shortname'   => $course->shortname,
              'fullname'    => $course->fullname
             );

$result = '<p class="result">';
$result .=  get_string('coursetemplate_result', 'local_friadmin', $info);
$result .= '</p>';

echo $OUTPUT->heading($strTitle,2);
echo $OUTPUT->heading($strSubTitle,3);

$linkLst = new local_friadmin_coursetemplate_linklist();

$linkLst->create_linklist($courseId);

echo $result;
echo $linkLst->getContentListLink();


/* Footer   */
echo $OUTPUT->footer();

