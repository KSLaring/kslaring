<?php
/**
 * Report Competence Manager - Move Company structure.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/company_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/04/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('company_structurelib.php');
require_once('move_company_structure_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$company        = required_param('id',PARAM_INT);
$level          = required_param('le',PARAM_INT);
$url            = new moodle_url('/report/manager/company_structure/move_company_structure.php',array('id' => $company,'le' => $level));
$index_url      = new moodle_url('/report/manager/index.php');
$returnUrl      = new moodle_url('/report/manager/company_structure/company_structure.php');
$parents        = $SESSION->parents;
$params         = array();
$siteContext    = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($siteContext);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','report_manager'),$index_url);
$PAGE->navbar->add(get_string('company_structure','report_manager'),$returnUrl);
$PAGE->navbar->add(get_string('btn_move','report_manager'));

/* ADD require_capability */
if (!CompetenceManager::IsSuperUser($USER->id)) {
    require_capability('report/manager:edit', $siteContext);
}//if_SuperUser

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_loginhttps

$PAGE->verify_https_required();

/* Return Url   */
$levelZero  = COMPANY_STRUCTURE_LEVEL . 0;
$levelOne   = COMPANY_STRUCTURE_LEVEL . 1;
$levelTwo   = COMPANY_STRUCTURE_LEVEL . 2;
$levelThree = COMPANY_STRUCTURE_LEVEL . 3;
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
$returnUrl = new moodle_url('/report/manager/company_structure/company_structure.php',$params);

/* Form */
$form = new move_company_structure_form(null,array($company,$level));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {
    /* Move Company From to    */

    /* Move Company */
    company_structure::move_from_to($data->id,$parents[($data->le -1)],$data->move_to);

    $_POST = array();
    redirect($returnUrl);
}//if_else_form

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();