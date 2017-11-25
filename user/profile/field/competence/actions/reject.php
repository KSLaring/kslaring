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
 * Extra Profile Field Competence - Reject Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/02/2016
 * @author          eFaktor     (fbv)
 *
 */
global $CFG, $PAGE, $SESSION, $SITE, $OUTPUT,$USER;

require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once($CFG->libdir . '/adminlib.php');

// PARAMS
$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/user/profile/field/competence/actions/reject.php');
$competencerequest  = null;
$user               = null;
$infolog            = null;
$confirmed          = null;
$infomssg           = null;
$strconfirm         = null;
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
require_login();
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
if (count($args) != 3) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
        echo $OUTPUT->error_text('<h4>' . get_string('err_link','profilefield_competence') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    // Get request
    $competencerequest = Competence::competence_request($args[1],$args[2]);
    // Get confirm parameter
    $confirmed = $args[0];

    // Message
    $infomssg = new stdClass();
    $infomssg->user     = $competencerequest->user;
    $infomssg->company  = $competencerequest->company;

    if (!$competencerequest) {
        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
        echo $OUTPUT->error_text('<h4>' . get_string('comp_delete','profilefield_competence') . '</h4>');
        echo html_writer::end_tag('div');
    }else {
        if ($confirmed == 1) {
            if (Competence::reject_competence($competencerequest)) {
                echo html_writer::start_tag('div');
                echo '<h4>' . get_string('request_rejected','profilefield_competence',$infomssg)  . '</h4>';
                echo html_writer::end_tag('div');

                // Write log
                Competence::write_competence_log($competencerequest,REQUEST_REJECTED,true);
            }else {
                echo html_writer::start_tag('div');
                echo '<h4>' . get_string('err_process','profilefield_competence')  . '</h4>';
                echo html_writer::end_tag('div');
            }
        }else {
            // Write log
            Competence::write_competence_log($competencerequest,REQUEST_REJECTED,false);

            // Ask for confirmation
            $strconfirm   = get_string('confirm_reject','profilefield_competence',$infomssg);
            $relativePath = new moodle_url('/user/profile/field/competence/actions/applyreject.php',array('t' => $args[1],'m' => $args[2]));

            echo $OUTPUT->confirm($strconfirm,$relativePath,$returnUrl);
        }//if_confirmed
    }//$competencerequest
}//if_arg

// Footer
echo $OUTPUT->footer();