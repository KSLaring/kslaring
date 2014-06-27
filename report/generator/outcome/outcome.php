<?php

/**
 * Report generator - Outcome.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../locallib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* Clean Cookies */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelTree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);
$_POST = array();

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/outcome/outcome.php');

/* ADD require_capability */
if (!has_capability('report/generator:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/generator:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'outcomes';
$show_roles = 1;
require('../tabs.php');

/* Get Outcome List */
$outcome_list = report_generator_get_outcome_list_with_rel_roles();

if (empty($outcome_list)) {
    /* Print Title */
    echo $OUTPUT->heading(get_string('available_outcomes', 'report_generator'));
    echo '<p>' . get_string('no_outcomes_available', 'report_generator') . '</p>';
}else {
    /* Print Title */
    echo $OUTPUT->heading(get_string('outcome', 'report_generator'));
    $table = report_generator_table_outcomes($outcome_list);

    echo html_writer::table($table);
}//if_else

/* Print Footer */
echo $OUTPUT->footer();