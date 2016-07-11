<?php
/**
 * Participants List - Javascript - Tick attendance of users
 *
 * @package         local
 * @subpackage      participants
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */
define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib/participantslib.php');

/* PARAMS   */
$toTick          = optional_param('totick',null,PARAM_TEXT);
$course          = required_param('course',PARAM_INT);

$json   = array();
$data   = array();
$info   = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/participants/attend.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();
$ticked = null;
$data = array('ticks' => array());
if ($toTick) {
    $ticked = ParticipantsList::TickParticipants($toTick,$course);
    
    if ($ticked) {
        foreach ($ticked as $user=>$date) {
            $info = new stdClass();
            $info->id = $user;
            $info->attendDate = $date;

            $data['ticks'][] = $info;
        }
    }
}
/* Encode and Send */


$json[] = $data;
echo json_encode(array('results' => $json));

