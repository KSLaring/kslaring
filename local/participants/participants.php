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

/* PARAMS */
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



/* Check permissions */
if (!has_capability('local/participants:manage',$context)) {
    require_login();
}else {
    require_login($course);
}//if_capabilities

if (!isset($SESSION->url_download)) {
    $SESSION->url_download  = $url_download;
}


/* Get the Sort Order       */
if (isset($_COOKIE['dir'])) {
    $sort = $_COOKIE['dir'];
}else {
    $sort = 'ASC';
}//if_dir

/* Get the Field by Sort    */
if (isset($_COOKIE['field'])) {
    $fieldSort = $_COOKIE['field'];
}else {
    $fieldSort = 'firstname';
}//if_dir

/* Set page */
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js('/local/participants/js/sort.js');

/* First get members that don't have to appear in participant list */
$notIn = ParticipantsList::GetNotMembersParticipantList($context->id);
if ($notIn) {
    $notIn = implode(',',$notIn);
}else {
    $notIn = 0;
}//if_notIn

$participantList    = ParticipantsList::GetParticipantList($courseId,$notIn,$sort,$fieldSort,$page*$per_page,$per_page);
$totalParticipants  = ParticipantsList::GetTotalParticipants($courseId,$notIn);

if ($format) {
    $participantList    = ParticipantsList::GetParticipantList($courseId,$notIn,$sort,$fieldSort);
    ParticipantsList::Download_ParticipantsList($participantList,$course);
}
/* Header   */
echo $OUTPUT->header();

/* Display Participants     */
echo $OUTPUT->heading(get_string('pluginname','local_participants'));
$out = ParticipantsList::DisplayParticipantList($participantList,$course,$sort,$fieldSort);
echo $out;

echo "</br>";
echo $OUTPUT->paging_bar($totalParticipants, $page, $per_page, $url);

echo ParticipantsList::GetBackButton($course->id);


/* Footer   */
echo $OUTPUT->footer();
