<?php
/**
 * Participants List  
 *
 * @package         local
 * @subpackage      participants
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('lib/participantslib.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');

// Params
$courseId   = required_param('id',PARAM_INT);
$page       = optional_param('page', 0, PARAM_INT);
$per_page   = optional_param('perpage', 50, PARAM_INT);        // how many per page
$format     = optional_param('format',null,PARAM_TEXT);
$sort       = optional_param('sort','ASC',PARAM_TEXT);
$fieldSort  = null;

$url                    = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page));
$url_download           = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page,'format' => 'csv'));

$context            = context_course::instance($courseId);
$course             = get_course($courseId);
$participantList    = null;
$totalParticipants  = 0;
$notIn              = null;
$filtered           = false;
$filter             = null;
$location           = null;
$instructors        = null;


// Check permissions
if (!has_capability('local/participants:manage',$context)) {
    require_login();
}else {
    require_login($course);
}//if_capabilities

$SESSION->url_download  = $url_download;

// Sort order
if (isset($_COOKIE['dir'])) {
    $sort = $_COOKIE['dir'];
}else {
    $sort = 'ASC';
}//if_dir

// Field by sortd
if (isset($_COOKIE['field'])) {
    $fieldSort = $_COOKIE['field'];
}else {
    $fieldSort = 'firstname';
}//if_dir

// Set page
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js('/local/participants/js/sort.js');

// Location
$location    = ParticipantsList::get_location($courseId);
// Instructors
$instructors = ParticipantsList::get_instructors($courseId);

// Member that don't have to be in the participant list
$notIn = ParticipantsList::get_not_members_participant_list($context->id);
if ($notIn) {
    $notIn = implode(',',$notIn);
}else {
    $notIn = 0;
}//if_notIn

// Participant list
$participantList    = ParticipantsList::get_participant_list($courseId,$notIn,$sort,$fieldSort,$page*$per_page,$per_page);
$totalParticipants  = ParticipantsList::get_total_participants($courseId,$notIn);

// Download in excel
if ($format) {
    $participantList    = ParticipantsList::get_participant_list($courseId,$notIn,$sort,$fieldSort);
    ParticipantsList::download_participants_list($participantList,$course,$location,$instructors);
}
/* Header   */
echo $OUTPUT->header();

// Display participant list
echo $OUTPUT->heading(get_string('pluginname','local_participants'));
$out = ParticipantsList::display_participant_list($participantList,$course,$location,$instructors,$sort,$fieldSort);
echo $out;

echo "</br>";
echo $OUTPUT->paging_bar($totalParticipants, $page, $per_page, $url);

// Back button
echo ParticipantsList::get_back_button($course->id);


/* Footer   */
echo $OUTPUT->footer();
