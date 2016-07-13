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
 * Block for displaying logged in user's course completion status
 *
 * @package    block
 * @subpackage completion
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once("{$CFG->libdir}/completionlib.php");

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

if (! $cm = get_coursemodule_from_id('completionreport', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $completionreport = $DB->get_record("completionreport", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

// Load user.
$user = $USER;

// Check permissions.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

//if (!completion_can_view_data($user->id, $course)) {
//    print_error('cannotviewreport');
//}

// Load completion data.
$info = new completion_info($course);

$returnurl = new moodle_url('/course/view.php', array('id' => $id));

// Don't display if completion isn't enabled.
$completionenabled = true;
if (!$info->is_enabled()) {
//    print_error('completionnotenabled', 'completion', $returnurl);
    $completionenabled = false;
}

// Check this user is enroled.
$enrolled = true;
if (!$info->is_tracked_user($user->id)) {
    if ($USER->id == $user->id) {
//        print_error('notenroled', 'completion', $returnurl);
        $enrolled = false;
    } else {
//        print_error('usernotenroled', 'completion', $returnurl);
        $enrolled = false;
    }
}

// Print header.
$page = get_string('completionprogressdetails', 'mod_completionreport');
$title = format_string($course->fullname) . ': ' . $page;

//$PAGE->navbar->add($page);
//$PAGE->set_pagelayout('incourse');

$PAGE->set_url('/mod/completionreport/view.php', array('id'=>$id));
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($title);
$PAGE->set_cm($cm);

echo $OUTPUT->header();

// Display the completion information if the user is enrolled in the course
if ($enrolled && $completionenabled) {
    // Display completion status.
    echo html_writer::start_tag('table',
        array('class' => 'generalbox status-table'));
    echo html_writer::start_tag('tbody');

    // If not display logged in user, show user name.
    if ($USER->id != $user->id) {
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('td', array('colspan' => '2'));
        echo html_writer::tag('b', get_string('showinguser', 'completion'));
        $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
        echo html_writer::link($url, fullname($user));
        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
    }

    echo html_writer::start_tag('tr');
    echo html_writer::start_tag('td', array('colspan' => '2', 'class' => 'completion-status'));
    echo html_writer::tag('b', get_string('status'));

    // Is course complete?
    $coursecomplete = $info->is_course_complete($user->id);

    // Has this user completed any criteria?
    $criteriacomplete = $info->count_course_user_data($user->id);

    // Load course completion.
    $params = array(
        'userid' => $user->id,
        'course' => $course->id,
    );
    $ccompletion = new completion_completion($params);

    if ($coursecomplete) {
        echo get_string('complete');
    } else if (!$criteriacomplete && !$ccompletion->timestarted) {
        echo html_writer::tag('span', get_string('notyetstarted', 'completion'));
    } else {
        echo html_writer::tag('span', get_string('inprogress', 'completion'));
    }

    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');

    // Load criteria to display.
    $completions = $info->get_completions($user->id);

    // Check if this course has any criteria.
    if (empty($completions)) {
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('td', array('colspan' => '2'));
        echo html_writer::start_tag('br');
        echo $OUTPUT->box(get_string('err_nocriteria', 'mod_completionreport'), 'noticebox');
        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
    } else {
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('td',
            array('colspan' => '2', 'class' => 'completion-required'));
        echo html_writer::tag('b', get_string('required'));

        // Get overall aggregation method.
        $overall = $info->get_aggregation_method();

        if ($overall == COMPLETION_AGGREGATION_ALL) {
            echo html_writer::tag('span', get_string('criteriarequiredall', 'completion'));;
        } else {
            echo html_writer::tag('span', get_string('criteriarequiredany', 'completion'));
        }

        echo html_writer::end_tag('td');
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');

        // Generate markup for criteria statuses.
        echo html_writer::start_tag('table',
                array('class' => 'generalbox logtable table table-striped',
                    'id' => 'criteriastatus', 'width' => '100%'));
        echo html_writer::start_tag('tbody');
        echo html_writer::start_tag('tr', array('class' => 'ccheader'));
        echo html_writer::tag('th', get_string('criteriagroup', 'mod_completionreport'), array('class' => 'c0 header', 'scope' => 'col'));
        echo html_writer::tag('th', get_string('criteria', 'completion'), array('class' => 'c1 header', 'scope' => 'col'));
        echo html_writer::tag('th', get_string('requirement', 'mod_completionreport'), array('class' => 'c2 header', 'scope' => 'col'));
        echo html_writer::tag('th', get_string('status'), array('class' => 'c3 header', 'scope' => 'col'));
        echo html_writer::tag('th', get_string('complete'), array('class' => 'c4 header', 'scope' => 'col'));
        echo html_writer::tag('th', get_string('completiondate', 'report_completion'), array('class' => 'c5 header', 'scope' => 'col'));
        echo html_writer::end_tag('tr');

        // Save row data.
        $rows = array();

        $modinfo = get_fast_modinfo($course);
        $sec0modinstances = $modinfo->sections[0];
        if (empty($sec0modinstances)) {
            $sec0modinstances = array();
        }

        // Loop through course criteria.
        foreach ($completions as $completion) {
            $criteria = $completion->get_criteria();

            $row = array();
            $row['type']            = $criteria->criteriatype;
            $row['title']           = $criteria->get_title();
            $row['status']          = $completion->get_status();
            $row['complete']        = $completion->is_complete();
            $row['timecompleted']   = $completion->timecompleted;
            $row['details']         = $criteria->get_details($completion);

            // In details/requirement the module name is not translated.
            // Get the language module name and change the requirement string.
            /**
             * @updateDate      02/12/2015
             * @author          eFaktor     (fbv)
             *
             * Description
             * Check if there is a module name or not
             */
            if ($criteria->module) {
                $modname = get_string('modulename', $criteria->module);
            }else {
                $modname = $row['type'];
            }

            $newrequirement = get_string('viewingactivity', 'completion', $modname);
            $row['details']['requirement'] = $newrequirement;

            // If the module is placed in section 0 then add the information
            // to the link that no menu shall be shown.
            $modinstance = $criteria->moduleinstance;
            if (in_array($modinstance, $sec0modinstances)) {
                $row['details']['criteria'] = str_replace('id=' . $modinstance,
                    'id=' . $modinstance . '&nonav=1', $row['details']['criteria']);
            }

            $rows[] = $row;
        }

        // Print table.
        $last_type = '';
        $agg_type = false;
        $oddeven = 0;

        foreach ($rows as $row) {

            echo html_writer::start_tag('tr', array('class' => 'r' . $oddeven));
            // Criteria group.
            echo html_writer::start_tag('td', array('class' => 'cell c0'));
            if ($last_type !== $row['details']['type']) {
                $last_type = $row['details']['type'];
                echo $last_type;

                // Reset agg type.
                $agg_type = true;
            } else {
                // Display aggregation type.
                if ($agg_type) {
                    $agg = $info->get_aggregation_method($row['type']);
                    echo '('. html_writer::start_tag('span', array('class' => 'completion-aggregation'));
                    if ($agg == COMPLETION_AGGREGATION_ALL) {
                        echo core_text::strtolower(get_string('all', 'completion'));
                    } else {
                        echo core_text::strtolower(get_string('any', 'completion'));
                    }

                    echo html_writer::end_tag('span').' '.core_text::strtolower(get_string('required')).')';
                    $agg_type = false;
                }
            }
            echo html_writer::end_tag('td');

            // Criteria title.
            echo html_writer::start_tag('td', array('class' => 'cell c1'));
            echo $row['details']['criteria'];
            echo html_writer::end_tag('td');

            // Requirement.
            echo html_writer::start_tag('td', array('class' => 'cell c2'));
            echo $row['details']['requirement'];
            echo html_writer::end_tag('td');

            // Status.
            echo html_writer::start_tag('td', array('class' => 'cell c3'));
            echo $row['details']['status'];
            echo html_writer::end_tag('td');

            // Is complete.
            echo html_writer::start_tag('td', array('class' => 'cell c4'));
            echo $row['complete'] ? get_string('yes') : get_string('no');
            echo html_writer::end_tag('td');

            // Completion data.
            echo html_writer::start_tag('td', array('class' => 'cell c5'));
            if ($row['timecompleted']) {
                echo userdate($row['timecompleted'], get_string('strftimedate', 'langconfig'));
            } else {
                echo '-';
            }
            echo html_writer::end_tag('td');
            echo html_writer::end_tag('tr');
            // For row striping.
            $oddeven = $oddeven ? 0 : 1;
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
    }
} else if (!$completionenabled) {
    echo $OUTPUT->box(get_string('notcompletionenabled', 'mod_completionreport'));
} else if (!$enrolled) {
    echo $OUTPUT->box(get_string('notenrolled', 'mod_completionreport'));
}

// Display the "Return to course" button and the footer
$courseurl = new moodle_url("/course/view.php", array('id' => $course->id, 'start' => 1));
echo html_writer::start_tag('div', array('class' => 'buttons'));
echo $OUTPUT->single_button($courseurl, get_string('returntocourse', 'mod_completionreport'), 'get');
echo html_writer::end_tag('div');
echo $OUTPUT->footer();
