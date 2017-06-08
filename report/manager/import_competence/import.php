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
 * @creationDate    24/08/2015
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');
require_once('import_form.php');
require_once('competencylib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');

require_login();

/* PARAMS   */
$return             = new moodle_url('/report/manager/index.php');
$url                = new moodle_url('/report/manager/import_competence/import.php');
$urlImport          = new moodle_url('/report/manager/import_competence/matchwk.php');
$fileColumns        = null;
$iid                = null;
$cir                = null;
$content            = null;
$readCount          = null;
$contentFile        = null;
$error              = IMP_NON_ERROR;
$moved              = false;

/* Array of all fields for validation */
$stdFields = array('username',
                   'workplace',
                   'workplace_ic',
                   'sector',
                   'jobrole',
                   'jobrole_ic',
                   'generic',
                   'delete');

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

unset($SESSION->parents);

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

/* Clean Temporary Table   */
ImportCompetence::CleanNotImported();

/* Form */
$form = new import_competence_form(null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if ($data = $form->get_data()) {
    try {
        /* Extra Memory */
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        /* Read Content File    */
        $iid        = csv_import_reader::get_new_iid('import_competence');
        $cir        = new csv_import_reader($iid, 'import_competence');
        $content    = $form->get_file_content('import_competence');

        $readCount  = $cir->load_csv_content($content, $data->encoding,$data->delimiter_name);
        unset($content);

        /* Check Error */
        if ($readCount === false) {
            $error = IMP_LOAD_ERROR;
            ImportCompetence::Notify_ImportError($error,$url);
            die;
        } else if ($readCount == 0) {
            $error = IMP_EMPTY_FILE;
            ImportCompetence::Notify_ImportError($error,$url);
            die;
        }//if_read_count

        /* Validate Columns */
        if ($error == IMP_NON_ERROR) {
            $fileColumns    = ImportCompetence::ValidateColumns($cir, $stdFields, $error);
        }else {
            ImportCompetence::Notify_ImportError($error,$url);
            die;
        }//if_non_error

        /* Get Content File */
        if ($error == IMP_NON_ERROR) {
            $contentFile    = ImportCompetence::GetContentFile($fileColumns,$cir);
        }else {
            ImportCompetence::Notify_ImportError($error,$url);
            die;
        }//if_non_error

        /* Move the content file to a temporary table   */
        if ($contentFile) {
            $moved = ImportCompetence::MoveContent($contentFile);
        }//if_content_file

        /* Start the importation procedure  */
        if ($moved) {
            /* 1.- Non Existing Users   */
            if (ImportCompetence::Mark_NonExistingUsers()) {
                redirect($urlImport);
            }
        }//if_moved
    }catch (Exception $ex) {
        $error = IMP_LOAD_ERROR;
        ImportCompetence::Notify_ImportError($error,$url);
        die;
    }//try_catch
}//if_else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');

if (!$moved) {
    $form->display();
}//if_moved

/* Footer   */
echo $OUTPUT->footer();
