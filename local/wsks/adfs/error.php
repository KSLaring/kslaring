<?php
/**
 * Kommit ADFS Integration WebService - Error Login Page
 *
 * @package         local
 * @subpackage      wsks/adfs
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    30/10/2015
 * @author          eFaktor     (fbv)
 *
 */

require( '../../../config.php' );

/* PARAMS   */
$returnURL  = $CFG->wwwroot . '/index.php';

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

echo $OUTPUT->header();
if (isloggedin()) {
    require_logout();
}//if_log_in


echo $OUTPUT->notification(get_string('ADFS_ERR_PROCESS','local_wsks'), 'notifysuccess');
echo $OUTPUT->continue_button($returnURL);

echo $OUTPUT->footer();