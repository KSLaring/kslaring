<?php
/**
 * Express Login  - Index
 *
 * @package         local
 * @subpackage      express_login/login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/12/2014
 * @author          eFaktor     (fbv)
 */
require_once('../../../config.php');
require_once('loginlib.php');

global $PAGE,$SITE,$OUTPUT,$CFG,$USER;

/* PARAMS   */
$err_code       = optional_param('er',3,PARAM_INT);
$message_err    = null;

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');



echo $OUTPUT->header();
if (isloggedin()) {
    $return_url = $CFG->wwwroot;
}else {
    $return_url = new moodle_url('/login/index.php');
}//if_log_in

switch ($err_code) {
    case ERROR_EXPRESS_LINK_NOT_VALID:
        $message_err = get_string('ERROR_EXPRESS_LINK_NOT_VALID','local_express_login');

        break;
    case ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED:
        $message_err = get_string('ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED','local_express_login');

        break;
    case ERROR_EXPRESS_LINK_USER_NOT_VALID:
        $message_err = get_string('ERROR_EXPRESS_LINK_USER_NOT_VALID','local_express_login');

        break;
    default:
        break;
}//switch_er

echo $OUTPUT->notification($message_err, 'notifysuccess');
echo $OUTPUT->continue_button($return_url);

echo $OUTPUT->footer();
