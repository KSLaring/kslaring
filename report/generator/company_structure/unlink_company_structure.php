<?php
/**
 * Report generator - Unlink Company structure.
 *
 * Description
 *
 * @package         report
 * @subpackage      generator/company_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('unlink_company_structure_form.php');

/* Params */
$company_id     = required_param('id',PARAM_INT);
$url            = new moodle_url('/report/generator/company_structure/unlink_company_structure.php',array('id' => $company_id));
$return_url     = new moodle_url('/report/generator/company_structure/company_structure.php');
$index_url      = new moodle_url('/report/generator/index.php');


/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','report_generator'),$index_url);
$PAGE->navbar->add(get_string('company_structure','report_generator'),$return_url);
$PAGE->navbar->add(get_string('unlink_title','report_generator'));

/* ADD require_capability */
require_capability('report/generator:edit', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_loginhttps

$PAGE->verify_https_required();

/* Form */
$form = new unlink_company_structure_form(null,array($company_id));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Unlink Company and Parent    */
    company_structure::Unlink_Company($company_id,$data->parent_sel);

    $_POST = array();
    redirect($return_url);
}//if_else_form

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();