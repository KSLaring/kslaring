<?php
/**
 * KS Læring Integration - Login via Feide
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

include_once('../../../config.php');
require_once('feidelib.php');

/* PARAMS   */
$errCode    = optional_param('er',0,PARAM_INT);
$errMsg     = null;
$returnURL  = $CFG->wwwroot;

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

echo $OUTPUT->header();

switch ($errCode) {
    case FEIDE_NOT_VALID:
        $errMsg = get_string('FEIDE_ERR_NOT_VALID','local_wsks');

        break;
    case FEIDE_ERR_PROCESS:
        $errMsg = get_string('FEIDE_ERR_PROCESS','local_wsks');

        break;
}//switch_err_code

echo $OUTPUT->notification($errMsg, 'notifysuccess');
echo $OUTPUT->continue_button($returnURL);

echo $OUTPUT->footer();