<?php
/**
 * Report Competence Manager - Company structure - Reporter
 *
 * Description
 *
 * @package         report/reporter
 * @subpackage      company_structure/reporter
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once( 'reporterlib.php');
require_once( '../company_structurelib.php');
require_once('reporter_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* PARAMS */
$level          = optional_param('le',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$url            = new moodle_url('/report/manager/company_structure/reporter/reporter.php');
$returnUrl      = new moodle_url('/report/manager/company_structure/company_structure.php');
$parents        = $SESSION->parents;
$params         = array();

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($siteContext);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_reporters','report_manager'));

$PAGE->verify_https_required();

/* ADD require_capability */
require_capability('report/manager:edit', $siteContext);

/* Return Url   */
$levelZero  = 'level_' . 0;
$levelOne   = 'level_' . 1;
$levelTwo   = 'level_' . 2;
$levelThree = 'level_' . 3;
if (isset($parents[0]) && $parents[0]) {
    $params[$levelZero] = $parents[0];
}
if (isset($parents[1]) && $parents[1]) {
    $params[$levelOne] = $parents[1];
}
if (isset($parents[2]) && $parents[2]) {
    $params[$levelTwo] = $parents[2];
}
if (isset($parents[3]) && $parents[3]) {
    $params[$levelThree] = $parents[3];
}
$returnUrl      = new moodle_url('/report/manager/company_structure/company_structure.php',$params);

/* Show Form */
$form       = new report_manager_reporters_form(null,array($level,$parents,$addSearch,$removeSearch));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {
    /* Add Reporters    */
    if (!empty($data->add_sel)) {
        if (isset($data->addselect)) {
            Reporters::AddReporters($data->le,$parents,$data->addselect);
        }//if_addselect
    }//if_add

    /* Remove Reporters */
    if (!empty($data->remove_sel)) {
        if (isset($data->removeselect)) {
            Reporters::RemoveReporters($data->le,$parents,implode(',',$data->removeselect));
        }//if_removeselect
    }//if_remove
}//if_else

/* Print Header */
echo $OUTPUT->header();

/* Print Title */
echo $OUTPUT->heading(get_string('title_reporters', 'report_manager'));

$form->display();

/* Initialise Selectors */
Reporters::Init_Reporters_Selectors($addSearch,$removeSearch,$level,$parents);

/* Print Footer */
echo $OUTPUT->footer();