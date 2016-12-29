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
$action         = required_param('a',PARAM_INT);
$suspuciousId   = required_param('id',PARAM_INT);
$url            = new moodle_url('/local/fellesdata/suspicious/result.php');
$return         = new moodle_url('/local/fellesdata/suspicious/index.php');
$args           = null;
$strMessage     = null;
$error          = NONE_ERROR;

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

// Header
echo $OUTPUT->header();

echo $OUTPUT->notification($strMessage, 'notifysuccess');
echo $OUTPUT->continue_button($return);

// Footer
echo $OUTPUT->footer();