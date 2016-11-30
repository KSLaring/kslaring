<?php
/**
 * eMail Fake 
 *
 * Description
 *
 * @package         local/wsks
 * @subpackage      eMail
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/10/2016
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('email_form.php');
require_once('../../../user/lib.php');
require_once('../fellesdata/wsfellesdatalib.php');

require_login();

/* PARAMS */
$userId         = required_param('id',PARAM_INT);
$context        = context_system::instance();
$url            = new moodle_url('/local/wsks/email/email.php');
$user           = null;

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('standard');

/* Get user data */
$user = get_complete_user_data('id',$userId);

/* Form */
$form = new email_fake_form(null,array($userId,$USER->email));
if ($data = $form->get_data()){
    $USER->email = $data->email;
    user_update_user($USER);
    redirect($CFG->wwwroot);
}

echo $OUTPUT->header();
echo '<h4>' . get_string('invalid_email','local_wsks') . '</h4></br>';

$form->display();

echo $OUTPUT->footer();