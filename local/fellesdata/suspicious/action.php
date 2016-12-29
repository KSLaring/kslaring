<?php
/**
 * Fellesdata Suspicious Integration - Action (Approve - Reject)
 *
 * @package         local/fellesdata
 * @subpackage      suspicious
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/12/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('../lib/suspiciouslib.php');
require_once('../lib/fellesdatalib.php');

/* PARAMS */
$url            = new moodle_url('/local/fellesdata/suspicious/action.php');
$relative_path  = null;
$args           = null;
$strMessage     = null;
$error          = NONE_ERROR;

/* Guess USer -- Logout */
if (isguestuser($USER)) {
    require_logout();
}//if_guestuser

$relative_path = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relative_path, '/'));

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('suspicious_header','local_fellesdata'));

if (count($args) != 3) {
    $strMessage = get_string('err_params','local_fellesdata');
}else {
    suspicious::check_action_link($args,$error);

    switch ($error) {
        case NONE_ERROR:
            // Apply action
            suspicious::apply_action($args,$error);
            $name = suspicious::get_name($args[2]);

            switch ($error) {
                case APPROVED:
                    $strMessage = get_string('approved','local_fellesdata',$name);

                    break;

                case REJECTED:
                    $strMessage = get_string('rejected','local_fellesdata',$name);

                    break;

                default:
                    $strMessage = get_string('err_file','local_fellesdata');
                    
                    break;
            }//switch_error

            break;

        case ERR_PARAMS:
            $strMessage = get_string('err_params','local_fellesdata');

            break;

        case ERR_FILE:
            $strMessage = get_string('err_file','local_fellesdata');

            break;
    }//switch
}//if_args

// Header
echo $OUTPUT->header();

echo $OUTPUT->notification($strMessage, 'notifysuccess');
echo $OUTPUT->continue_button($CFG->wwwroot);

// Footer
echo $OUTPUT->footer();
