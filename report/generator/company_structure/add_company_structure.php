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
$level  = optional_param('level', 1, PARAM_INT);
$return_url = new moodle_url('/report/generator/company_structure/company_structure.php',array('level'=>$level));

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/company_structure/add_company_structure.php');

/* ADD require_capability */
require_capability('report/generator:edit', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Show Form */
$form = new generator_add_company_structure_form(null,$level);

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    $parents = $SESSION->parents;
    $instance       = new stdClass();

    /* Get Data */
    if ($data->name) {
        $instance->name   = $data->name;
    }else {
        $instance->name   = report_generator_get_company_name($data->other_company);
    }

    $instance->hierarchylevel   = $level;
    $instance->modified         = time();

    if ($level == 1) {
        report_generator_insert_company_level($instance);
    }else {
        if ($level == 3) {
            $instance->idcounty = $data->county;
            $instance->idmuni   = $data->municipality_id;
        }
        report_generator_insert_company_level($instance, $parents[$level-1]);
    }

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();