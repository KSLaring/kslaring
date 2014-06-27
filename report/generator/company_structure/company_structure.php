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
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('company_structure_form.php');

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/index.php');

$return_url = new moodle_url('/report/generator/company_structure/company_structure.php');

/* ADD require_capability */
require_capability('report/generator:edit', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_loginhttps

/* Show Form */
$form = new generator_company_structure_form(null);
$url = null;

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);
    $_POST = array();
    $url = new moodle_url('/report/generator/index.php');
    redirect($url);
}else {
    if ($data = $form->get_data()) {
        list($action, $level) = report_generator_get_action_and_level($data);

        switch ($action) {
            case REPORT_GENERATOR_GET_LEVEL:
                break;
            case REPORT_GENERATOR_COMPANY_CANCEL:
                setcookie('parentLevelOne',0);
                setcookie('parentLevelTwo',0);
                setcookie('parentLevelTree',0);
                setcookie('courseReport',0);
                setcookie('outcomeReport',0);
                $_POST = array();
                $url = new moodle_url('/report/generator/index.php');
                redirect($url);

                break;
            case REPORT_GENERATOR_ADD_ITEM:
                $parent = array();

                for ($i = 1; $i < $level; $i++) {
                    $select = REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . $i;
                    $parent[$i] = $data->$select;
                }//for

                $SESSION->parents = $parent;
                $url    = new moodle_url('/report/generator/company_structure/add_company_structure.php',array('level'=>$level));
                break;
            case REPORT_GENERATOR_RENAME_SELECTED:
                $parent = array();

                for ($i = 1; $i <= $level; $i++) {
                    $select = REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . $i;
                    $parent[$i] = $data->$select;
                }//for

                $SESSION->parents = $parent;
                $url    = new moodle_url('/report/generator/company_structure/edit_company_structure.php',array('level'=>$level));
                break;
            case REPORT_GENERATOR_DELETE_SELECTED:
                $select     = REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . $level;
                $company_id = $data->$select;

                $url    = new moodle_url('/report/generator/company_structure/delete_company_structure.php',array('id'=>$company_id, 'level'=>$level));
                break;
            default:
                break;
        }//$action

        if (!is_null($url)) {
            redirect($url);
        }
    }//form
}//form_cancelled

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'company_structure';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('company_structure', 'report_generator'));

$form->display();

/* Print Footer */
echo $OUTPUT->footer();