<?php
/**
 * Web Services KS - Error
 *
 * @package         local/wsks
 * @subpackage      slaves/classes
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    09/11/2016
 * @author          eFaktor     (fbv)
 */

require_once( '../../../../config.php');
require_once('../lib/slaveslib.php');

global $USER,$PAGE,$OUTPUT;

// Params
$error      = optional_param('er',0,PARAM_INT);
$url        = new moodle_url('/local/wsks/slaves/classes/result.php',array('er' => $error));
$returnUrl  = new moodle_url('/local/wsks/slaves/classes/slaves.php');
$context    = context_system::instance();
$strMessage = null;

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Capability
require_capability('local/wsks:manage',$context);

// Start page
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);

switch ($error) {
    case ERR_NONE:
        $strMessage = get_string('err_none','local_wsks');
        
        break;
    case ERR_SLAVE_SERVICE:
        $strMessage = get_string('err_no_service','local_wsks');
        
        break;
    case ERR_NO_DOMAINS:
        $strMessage = get_string('err_no_domains','local_wsks');

        break;
    default:
        $strMessage = get_string('FEIDE_ERR_PROCESS','local_wsks');
        
        break;
}//switch_error

// Header
echo $OUTPUT->header();

echo $OUTPUT->notification($strMessage, 'notifysuccess');
echo $OUTPUT->continue_button($returnUrl);

// Footer
echo $OUTPUT->footer();