<?php

/**
 * Report Competence Manager - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  10/09/2012
 * @author      eFaktor     (fbv)
 *
 * Add a new company into a specific level
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once('company_structurelib.php');
require_once('add_company_structure_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$level      = required_param('level', PARAM_INT);
$return_url = new moodle_url('/report/manager/company_structure/company_structure.php',array('level'=>$level));
$url        = new moodle_url('/report/manager/company_structure/add_company_structure.php',array('level' => $level));

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','report_manager'),new moodle_url('/report/manager/index.php'));
$PAGE->navbar->add(get_string('company_structure','report_manager'),$return_url);
$PAGE->navbar->add(get_string('add_company_level','report_manager'));

/* ADD require_capability */
require_capability('report/manager:edit', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security


/* Show Form */
$form = new manager_add_company_structure_form(null,$level);

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    $parents = $SESSION->parents;

    /* Add a new Company Level. New One or Link */
    company_structure::Add_CompanyLevel($data,$parents,$level);

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();