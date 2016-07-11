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
require_once('forms/filter_form.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');

/* PARAMS */
$courseId   = required_param('id',PARAM_INT);
$page       = optional_param('page', 0, PARAM_INT);
$per_page   = optional_param('perpage', 50, PARAM_INT);        // how many per page
$format     = optional_param('format',null,PARAM_TEXT);

$url                    = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page));
$url_download           = new moodle_url('/local/participants/participants.php',array('id' => $courseId,'page' => $page,'perpage' => $per_page,'format' => 'csv'));

$context            = context_course::instance($courseId);
$course             = get_course($courseId);
$participantList    = null;
$totalParticipants  = 0;
$notIn              = null;
$filtered           = false;
$filter             = null;

require_login($course);

/* Check permissions */
if (!has_capability('local/participants:manage',$context)) {
    print_error('nopermissions', 'error', '', 'local/participants:manage');
}//if_capabilities

if (!isset($SESSION->url_download)) {
    $SESSION->url_download  = $url_download;
}

if (!isset($SESSION->filter)) {
    $SESSION->filter = null;
}
/* Set page */
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

/* First get members that don't have to appear in participant list */
$notIn = ParticipantsList::GetNotMembersParticipantList($context->id);
if ($notIn) {
    $notIn = implode(',',$notIn);
}else {
    $notIn = 0;
}//if_notIn

/* Form Body    */
$form           = new filter_participants_form(null,array($courseId,$page,$per_page));
if ($data = $form->get_data()) {
    /* Apply filter */
    if ((isset($data->submitbutton) && ($data->submitbutton))) {
        $filtered = true;
        
        /* Extract Data Filter  */
        $filter = new stdClass();
        $filter->from       = $data->date_from;
        $filter->to         = $data->date_to;
        
        /* Save Filter */
        $SESSION->filter = $filter;
        
        /* Get Participants List    - With Filter */
        $participantList    = ParticipantsList::GetParticipantList($courseId,$notIn,$filter,$page*$per_page,$per_page);
        $totalParticipants  = ParticipantsList::GetTotalParticipants($courseId,$notIn,$filter);
    }else if ((isset($data->submitbutton2) && ($data->submitbutton2))) {
        /* Clean Data */
        $filtered        = false;
        $_POST           = array();
        $SESSION->filter = null;
        
        /* Remove Filter */
        $form           = new filter_participants_form(null,array($courseId,$page,$per_page));

        /* Get Participants List    - No Filter */
        $participantList    = ParticipantsList::GetParticipantList($courseId,$notIn,null,$page*$per_page,$per_page);
        $totalParticipants  = ParticipantsList::GetTotalParticipants($courseId,$notIn);
    }
}else {
    /* Get Participants List    - No Filter */
    if ($SESSION->filter) {
        $filter = $SESSION->filter;
    }//filter
    $participantList    = ParticipantsList::GetParticipantList($courseId,$notIn,$filter,$page*$per_page,$per_page);
    $totalParticipants  = ParticipantsList::GetTotalParticipants($courseId,$notIn);
}

if ($format) {
    if ($SESSION->filter) {
        $filter = $SESSION->filter;
    }//filter
    $participantList    = ParticipantsList::GetParticipantList($courseId,$notIn,$filter);
    ParticipantsList::Download_ParticipantsList($participantList,$course->fullname);
}

/* Header   */
echo $OUTPUT->header();

$form->display();

/* Display Participants     */
echo $OUTPUT->heading(get_string('pluginname','local_participants'));
$out = ParticipantsList::DisplayParticipantList($participantList,$course,$filtered);
echo $out;

echo "</br>";
echo $OUTPUT->paging_bar($totalParticipants, $page, $per_page, $url);

echo ParticipantsList::GetBackAndTickButton($course->id);

/* Initialize   */
ParticipantsList::InitParticipants($course->id);

/* Footer   */
echo $OUTPUT->footer();
