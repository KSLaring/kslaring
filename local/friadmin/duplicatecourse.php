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
 * Friadmin Plugin - duplicate course
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$courseid = required_param('id', PARAM_INT);

require_login($courseid);

$course = $DB->get_record('course', array('id' => $courseid));
$str_defaultending = get_string('dupcoursenamedefault', 'local_friadmin');
$coursecat = $course->category;
$dupcoursenamefull = $course->fullname . $str_defaultending;
$dupcoursenameshort = $course->shortname . $str_defaultending;
$url = new moodle_url('/local/friadmin/duplicatecourse.php');
$returnurl = new moodle_url('/course/view.php', array('id' => $courseid));
$context = context_system::instance();
$category = null;
$urledit = null;
$errormsg = false;

// Set page.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
$PAGE->navbar->add(get_string('naddcourse', 'local_friadmin'), $url);


// Setup or process form.
$customdata = array(
    'id' => $courseid,
    'coursecat' => $coursecat,
    'selfullname' => $dupcoursenamefull,
    'selshortname' => $dupcoursenameshort,
);
$form = new local_friadmin_duplicatecourse_form(null, $customdata);

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    // Duplicate the course.
    list($newcourse, $result) = local_friadmin_helper::duplicate_course($data);

    if (!is_null($newcourse)) {
        $urlnewcourse = new moodle_url('/course/view.php', array('id' => $newcourse['id']));
        redirect($urlnewcourse);
    } else {
        $errormsg = '<div class="alert alert-error">' . $result . '</div>';
    }
}//if_else

// Display page.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('duplicatecourse', 'local_friadmin'));

if ($errormsg) {
    echo $errormsg;
}

$form->display();

echo $OUTPUT->footer();
