<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Report Competence Manager - Import Competence Data.
 *
 * @package         report
 * @subpackage      manager/import_competence
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    25/08/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Final step of the import process
 */

require_once('../../../config.php');
require_once('competencylib.php');
require_once('match_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$download       = optional_param('d',0,PARAM_INT);
$return         = new moodle_url('/report/manager/index.php');
$url            = new moodle_url('/report/manager/import_competence/processing.php');
$urlImport      = new moodle_url('/report/manager/import_competence/import.php');
$notImported    = null;
$tblNotImported = null;
$totalNotImport = null;
$out            = null;

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($urlImport);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* ADD require_capability */
if (!has_capability('report/manager:edit', $siteContext)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();


if (!$download) {
    /* 4.- Update user id for importable records    */
    ImportCompetence::Mark_ExistingUsers();

    /* 5.- Import/Process Competence Data   */
    $totalNotImport = ImportCompetence::ProcessCompetenceData();
    if ($totalNotImport) {
        /* Get Competence Data Not Imported */
        $tblNotImported = ImportCompetence::CompetenceData_NotImported();

        /* With errors  */
        $out  = $OUTPUT->notification(get_string('icd_not_imported','report_manager'), 'notifysuccess');
        $out .= '<br>';
        $out .= html_writer::tag('div', html_writer::table($tblNotImported), array('class'=>'flexible-wrap'));
        $out .= '<br>';
        $out .= $OUTPUT->notification(get_string('icd_total_not_imp','report_manager',format_string($totalNotImport)), 'notifysuccess');
        $out .= '<br>';
        $out .= $OUTPUT->notification(get_string('icd_not_imported_adv','report_manager'), 'notifysuccess');
        $out .= '<br>';
        $url->param('d',1);
        $out .= html_writer::start_tag('div',array('class' => 'div_button_icd'));
        $out .= html_writer::link($url,get_string('icd_download','report_manager'),array('class' => 'button_icd'));
        $out .= html_writer::end_tag('div');
    }else {
        /* Success  */
        $out  =  $OUTPUT->notification(get_string('icd_imported','report_manager'), 'notifysuccess');
        $out .= '<br>';
        $out .=  $OUTPUT->continue_button(new moodle_url('/'));
    }//if_else
}else {
    /* Download File    */
    ImportCompetence::DownloadCompetenceData_NotImported();
}


/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('header_competence_imp','report_manager'));

echo $out;

/* Footer   */
echo $OUTPUT->footer();










