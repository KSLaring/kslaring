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
 * Matching workplaces
 */

global $CFG,$PAGE,$USER,$OUTPUT,$SITE;

require_once('../../../config.php');
require_once('competencylib.php');
require_once('match_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// PARAMS
$return         = new moodle_url('/report/manager/index.php');
$url            = new moodle_url('/report/manager/import_competence/matchwk.php');
$urlImport      = new moodle_url('/report/manager/import_competence/import.php');
$nonExisting    = null;
$start          = 0;
$step           = 2;

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($urlImport);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// ADD require_capability
if (!has_capability('report/manager:edit', $siteContext)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// 2.- Check Workplaces
$nonExisting = ImportCompetence::CheckWorkplaces($start,$step);
$total       = count($nonExisting);

if ($nonExisting) {
    $form = new match_form(null,array($nonExisting,$start,$step,'wk'));
    if ($form->is_cancelled()) {
        $_POST = array();
        redirect($return);
    }else if ($data = $form->get_data()) {
        // First matching
        $matched = ImportCompetence::MatchingWorkplaces($nonExisting,$data);

        // Redirect
        if ($matched) {
            redirect($url);
        }//matched
    }//if_Else
}else {
    // Process job roles
    $url            = new moodle_url('/report/manager/import_competence/matchjr.php');
    redirect($url);
}//if_nonExisting

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('match_wk', 'report_manager'), 'match_wk','report_manager');

$form->display();

// Footer
echo $OUTPUT->footer();