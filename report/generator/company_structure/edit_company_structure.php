<?php

/**
 * Report generator - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  11/09/2012
 * @author      eFaktor     (fbv)
 *
 * Edit a company into a specific level
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once('company_structurelib.php');
require_once('edit_company_structure_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$level  = optional_param('level', 1, PARAM_INT);
$url        = new moodle_url('/report/generator/company_structure/edit_company_structure.php',array('level'=>$level));
$return_url = new moodle_url('/report/generator/company_structure/company_structure.php',array('level'=>$level));

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','report_generator'),new moodle_url('/report/generator/index.php'));
$PAGE->navbar->add(get_string('company_structure','report_generator'),$return_url);
$PAGE->navbar->add(get_string('edit_company_level','report_generator'));

/* ADD require_capability */
require_capability('report/generator:edit', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Show Form */
$form = new generator_edit_company_structure_form(null,$level);

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    $parents    = $SESSION->parents;
    $instance   = new stdClass();

    /* Get Data */
    $instance->id               = $parents[$level];
    if ($data->name) {
        $instance->name   = $data->name;
    }else {
        $instance->name   = report_generator_get_company_name($data->other_company);
    }//if_else_data_name
    $instance->hierarchylevel   = $level;
    $instance->modified         = time();

    if ($level == 3) {
        $instance->idcounty = $data->county;
        $instance->idmuni   = $data->municipality_id;
    }//if_level_3

    company_structure::Update_CompanyLevel($instance);

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();