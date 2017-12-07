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
 * Extra Profile Field Competence - Approve Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    09/03/2016
 * @author          eFaktor     (fbv)
 *
 */
global $USER,$CFG,$PAGE,$SITE,$OUTPUT;

require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/user/profile/field/competence/actions/approve.php');
$competenceRequest  = null;
$info               = null;
$user               = null;

//extract relative path components
$relativePath   = get_file_argument();
$args   = explode('/', ltrim($relativePath, '/'));

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

// Header
echo $OUTPUT->header();

// Check args and content
if (count($args) != 2) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
    echo $OUTPUT->error_text('<h4>' . get_string('err_link','profilefield_competence') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    $competenceRequest = Competence::competence_request($args[0],$args[1]);

    if (!$competenceRequest) {
        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
        echo $OUTPUT->error_text('<h4>' . get_string('err_link','profilefield_competence') . '</h4>');
        echo html_writer::end_tag('div');
    }else {
        // User info
        $user = get_complete_user_data('id',$competenceRequest->userid);
        $info = new stdClass();
        $info->company  = $competenceRequest->company;
        $info->user     = fullname($user);

        if ($competenceRequest->approved) {
            echo html_writer::start_tag('div');
            echo '<h4>' . get_string('request_just_approved','profilefield_competence',$info)  . '</h4>';
            echo html_writer::end_tag('div');
        }else {
            if (Competence::approve_competence($competenceRequest)) {
                echo html_writer::start_tag('div');
                echo '<h4>' . get_string('request_approved','profilefield_competence',$info)  . '</h4>';
                echo html_writer::end_tag('div');
            }else {
                echo html_writer::start_tag('div');
                echo '<h4>' . get_string('err_process','profilefield_competence')  . '</h4>';
                echo html_writer::end_tag('div');
            }
        }//if_rejected
    }//if_competenceRequest
}//if_arg

// Footer
echo $OUTPUT->footer();