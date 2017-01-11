<?php
/**
 * Fellesdata Suspicious Integration - Index
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
require_once('index_form.php');

require_login();

/* PARAMS */
$action         = optional_param('a',0,PARAM_INT);
$suspiciousId   = optional_param('id',0,PARAM_INT);
$url            = new moodle_url('/local/fellesdata/suspicious/index.php');
$suspicious     = null;
$error          = NONE_ERROR;
$strMessage     = null;
$name           = null;
$out            = '';

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

if (($suspiciousId) &&
    ($action == 1) || ($action == 2)) {
    $args = array();
    $args[2] = $suspiciousId;
    $args[0] = $action;
    $args[1] = 0;

    // Apply action
    suspicious::apply_action($args,$error);
    $name = suspicious::get_name($suspiciousId);

    switch ($error) {
        case APPROVED:
            $strMessage = get_string('approved','local_fellesdata',$name);

            break;

        case REJECTED:
            $strMessage = get_string('rejected','local_fellesdata',$name);

            break;

        default:
            $strMessage = get_string('err_process','local_fellesdata');

            break;
    }//switch_error

    // Header
    echo $OUTPUT->header();

    echo $OUTPUT->notification($strMessage, 'notifysuccess');
    echo $OUTPUT->continue_button($url);

    // Footer
    echo $OUTPUT->footer();
}else {
    // get suspicious data to show
    // No data --> From today until today
    $date = getdate(time());
    $from   = mktime(23, 0, 0, $date['mon'], $date['mday']-1, $date['year']);
    $suspicious = suspicious::get_suspicious_files($from,$from);

    // Form
    $form = new suspicious_form(null,null);
    if($data = $form->get_data()) {
        // get data connected with the filter
        $suspicious = suspicious::get_suspicious_files($data->date_from,$data->date_to);
    }//if_form

    // Header
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('suspicious_header','local_fellesdata'));

    $form->display();

    echo suspicious::display_suspicious_table($suspicious);

    // Footer
    echo $OUTPUT->footer();
}

