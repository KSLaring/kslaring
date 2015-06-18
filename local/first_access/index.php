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
$user_id        = required_param('id',PARAM_INT);
$context        = context_system::instance();
$url            = new moodle_url('/local/first_access/index.php',array('id'=>$user_id));
$url_profile    = new moodle_url('/local/first_access/first_access.php',array('id' => $user_id));
$user_context = context_user::instance($user_id);

$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome_title','local_first_access'));

$user = get_complete_user_data('id',$user_id);
if (($user->firstname) && ($user->lastname) && ($user->email) && ($user->city)) {
    $url_profile = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $user_id));
}
echo html_writer::start_div();
    echo "</br>";
    echo get_string('welcome_message','local_first_access');
    echo "</br></br>";

    echo html_writer::start_div('buttons');
        echo '<a href="' . $url_profile . '">';
            echo '<button>' . get_string('welcome_btn','local_first_access') . '</button>';
        echo '</a>';
    echo html_writer::end_div();//buttons
echo html_writer::end_div();

echo $OUTPUT->footer();

