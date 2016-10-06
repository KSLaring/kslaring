<?php
/**
 * First Access
 *
 * Description
 *
 * @package             local
 * @subpackage          first_access
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        18/06/2015
 * @author              eFaktor     (fbv)
 *
 */
require_once('../../config.php');
require_once('locallib.php');
require_once('first_access_form.php');

require_login();

/* PARAMS */
$userId         = $USER->id;
$context        = context_system::instance();
$url            = new moodle_url('/local/first_access/first_access.php');
$user_context   = context_user::instance($userId);
$redirect       = new moodle_url('/user/profile.php',array('id'=>$userId));
$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('admin');

/* SHOW FORM */
$form = new first_access_form(null,$userId);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($CFG->wwwroot);
}else if ($data = $form->get_data()){
    /* Save generic data    */
    FirstAccess::Update_UserProfile($data);
    // Save custom profile fields data.
    profile_save_data($data);

    /* Check if it still remains to update competence profile */
    if (!FirstAccess::HasCompleted_CompetenceProfile($data->id)) {
        $redirect = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $data->id));
    }//if_CompletedCompetenceProfile

    $user = get_complete_user_data('id',$data->id);
    complete_user_login($user);

    //$_POST = array();
    redirect($redirect);
}//if_else

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome_title','local_first_access'));

echo html_writer::start_div();
    echo "<h5>" . get_string('welcome_message','local_first_access') . "</h5></br>";
echo html_writer::end_div();

$form->display();

echo $OUTPUT->footer();