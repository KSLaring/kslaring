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
 * Report Competence Manager - Import Company structure.
 *
 * @package         report
 * @subpackage      manager/import_structure
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');
require_once('importlib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once('import_form.php');

/* PARAMS   */
$level              = required_param('level',PARAM_INT);
$return             = new moodle_url('/report/manager/index.php');
$return_url         = new moodle_url('/report/manager/company_structure/company_structure.php',array('level'=>$level));
$return_err         = new moodle_url('/report/manager/import_structure/import.php',array('level'=>$level));
$url                = new moodle_url('/report/manager/import_structure/import.php',array('level'=>$level));
$imported           = false;
$err_import         = false;
$table_not_imported = null;
$error              = NON_ERROR;
$per_page           = 4;
$total_not_imported = 0;
/* Array of all fields for validation */
$std_fields = array('company','industry');

@set_time_limit(60*60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
$PAGE->navbar->add(get_string('company_structure', 'report_manager'),$return_url);
$PAGE->navbar->add(get_string('header_import', 'report_manager'),$url);

unset($SESSION->parents);

/* ADD require_capability */
if (!has_capability('report/manager:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Add form to import   */
$form = new manager_import_structure_form(null,$level);
if ($form->is_cancelled()) {
    /* Clean Cookies    */
    setcookie('parentImportLevel',0);
    setcookie('parentImportZero',0);
    setcookie('parentImportOne',0);
    setcookie('parentImportTwo',0);

    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    try {
        $file_columns = null;
        $iid = csv_import_reader::get_new_iid('import_structure');
        $cir = new csv_import_reader($iid, 'import_structure');

        $content = $form->get_file_content('import_structure');

        $read_count = $cir->load_csv_content($content, $data->encoding,$data->delimiter_name);
        unset($content);

        if ($read_count === false) {
            $error = CSV_LOAD_ERROR;
        } else if ($read_count == 0) {
            $error = CSV_EMPTY_FILE;
        }else {
            $error = NON_ERROR;
            $file_columns   = Import_Companies::ValidateColumns($cir, $std_fields, $error);
        }//if_read_count

        /* Import the new companies */
        switch ($data->level) {
            case 1:
                $level_parent = $data->import_0;
                $public = $data->public_parent;

                break;
            case 2:
                $level_parent = $data->import_1;
                $public = $data->public_parent;

                break;
            case 3:
                $level_parent = $data->import_2;
                $public = $data->public_parent;

                break;
            default:
                $level_parent = null;
                if (isset($data->public)) {
                    $public = $data->public;
                }else {
                    $public = 0;
                }//if_public

                break;
        }//switch_level
        $imported       = true;
        $records_file   = Import_Companies::ValidateData($file_columns,$cir,$level,$level_parent);

        $err_import     = Import_Companies::ImportStructure($records_file,$data->level,$level_parent,$public);
        /* Get the companies have not been imported  */
        $total_not_imported = count($records_file->errors);
        if ($total_not_imported) {
            $table_not_imported = Import_Companies::ImportNotImported($records_file,$per_page,$total_not_imported);
        }//if_records_file_errors
    }catch (Exception $ex) {
        $error = CSV_LOAD_ERROR;
    }//try_catch
}//if_else

/* Print Header */
echo $OUTPUT->header();

/* SHOW UPLOAD PREVIEW FORM - EXTRA FIELDS */
switch ($error) {
    case CSV_LOAD_ERROR:
        echo $OUTPUT->notification(get_string('csv_load_error','report_manager'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case CSV_EMPTY_FILE:
        echo $OUTPUT->notification(get_string('csv_empty_file','report_manager'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case CANNOT_READ_TMP_FILE:
        echo $OUTPUT->notification(get_string('cannot_read_tmp_file','report_manager'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case CSV_FEW_COLUMNS;
        echo $OUTPUT->notification(get_string('csv_few_columns','report_manager'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case INVALID_FILE_NAME:
        echo $OUTPUT->notification(get_string('invalid_field_name','report_manager'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case DUPLICATE_FIELD_NAME:
        echo $OUTPUT->notification(get_string('duplicate_field_name','report_manager'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case NON_ERROR:
        /* Import the companies */
        if ($imported) {
            if ($err_import) {
                if ($table_not_imported) {
                    echo $OUTPUT->notification(get_string('non_import_structure','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo html_writer::tag('div', html_writer::table($table_not_imported), array('class'=>'flexible-wrap'));
                    echo '<br>';
                    echo $OUTPUT->notification(get_string('num_errors','report_manager',format_string($total_not_imported)), 'notifysuccess');
                    echo $OUTPUT->continue_button($return_url);
                }else {
                    echo $OUTPUT->notification(get_string('imported_structure','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return_url);
                }//if_records_file_errors
            }else {
                echo $OUTPUT->notification(get_string('error_import_structure','report_manager'), 'notifysuccess');
                echo '<br>';
                echo $OUTPUT->continue_button($return_err);
            }//if_err_import_structure
        }//if_imported

        break;
    default:
        break;
}//switch

if (!$imported) {
    $form->display();
}//if_imported

/* Print Footer */
echo $OUTPUT->footer();
die;