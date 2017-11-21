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

global $CFG,$PAGE,$USER,$OUTPUT,$SESSION;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('lib/participantslib.php');
require_once('pdf/export.php');

// Params
$courseId   = required_param('id',PARAM_INT);
$page       = optional_param('page', 0, PARAM_INT);
$per_page   = optional_param('perpage', 50, PARAM_INT);        // how many per page
$format     = optional_param('format',0,PARAM_INT);
$sort       = optional_param('sort','ASC',PARAM_TEXT);
$fieldSort  = null;

$url              = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page));
$urlxls           = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page,'format' => EXPORT_EXCEL));
$urlpdf           = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page,'format' => EXPORT_PDF));

$context            = context_course::instance($courseId);
$course             = get_course($courseId);
$participantList    = null;
$totalParticipants  = 0;
$notIn              = null;
$filtered           = false;
$filter             = null;
$location           = null;
$instructors        = null;

// Set page
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js('/local/participants/js/sort.js');

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}else {
if (!has_capability('local/participants:manage',$context)) {
    require_login();
}else {
    require_login($course);
}//if_capabilities
}


$SESSION->xls_download  = $urlxls;
$SESSION->pdf_download  = $urlpdf;

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
if ($format) {
    $participantList    = ParticipantsList::get_participant_list($courseId,$notIn,$sort,$fieldSort);
}else {
    $participantList    = ParticipantsList::get_participant_list($courseId,$notIn,$sort,$fieldSort,$page*$per_page,$per_page);
}

$totalParticipants  = ParticipantsList::get_total_participants($courseId,$notIn);

$export             = new participant_export($course,$location,$instructors,$participantList);

// Download in excel
if ($format == EXPORT_EXCEL) {
    ob_end_clean();
    ParticipantsList::download_participants_list($participantList,$course,$location,$instructors);

    die;
}else if ($format == EXPORT_PDF) {
    ob_end_clean();
    $export->export();

    die;
}else {

    // Header
    echo $OUTPUT->header();

    // Display participant list
    echo $OUTPUT->heading(get_string('pluginname','local_participants'));

    // Header course info (Participant list report)
    echo ParticipantsList::display_participant_list_info_course($course,$location,$instructors);

    // Extra links
    echo ParticipantsList::add_extra_links($page, $per_page, $url,$totalParticipants);

    // Participant list
    echo ParticipantsList::display_participant_list($participantList,$sort,$fieldSort);

    // Extra links
    echo ParticipantsList::add_extra_links($page, $per_page, $url,$totalParticipants);

    // Back button
    echo ParticipantsList::get_back_button($course->id);


    // Footer
    echo $OUTPUT->footer();
}



