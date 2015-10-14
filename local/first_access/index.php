<?php
/**
 * First Access - Force PRofile
 *
 * Description
 *
 * @package         local
 * @subpackage      first_access
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      10/11/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once('locallib.php');

/* PARAMS */
$userId         = required_param('id',PARAM_INT);
$context        = context_system::instance();
$url            = new moodle_url('/local/first_access/index.php',array('id'=>$userId));
$urlCompetence  = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $userId));
$urlUserProfile = new moodle_url('/local/first_access/first_access.php',array('id' => $userId));
$urlProfile     = $urlUserProfile;

$user_context = context_user::instance($userId);

$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome_title','local_first_access'));

/* Check if it only remains to update the competence profile */
if (FirstAccess::HasCompleted_AllUserProfile($userId) && FirstAccess::HasCompleted_AllExtraProfile($userId)) {
    if (!FirstAccess::HasCompleted_CompetenceProfile($userId)) {
        $urlProfile = $urlCompetence;
    }//if_CompletedCompetenceProfile

}

echo html_writer::start_div();
    echo "</br>";
    echo get_string('welcome_message','local_first_access');
    echo "</br></br>";

    echo html_writer::start_div('buttons');
        echo '<a href="' . $urlProfile . '">';
            echo '<button>' . get_string('welcome_btn','local_first_access') . '</button>';
        echo '</a>';
    echo html_writer::end_div();//buttons
echo html_writer::end_div();

echo $OUTPUT->footer();

