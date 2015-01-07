<?php

/**
 * Report generator - Import Company structure.
 *
 * @package         report
 * @subpackage      generator/import_structure
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');
require_once('importlib.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once('import_form.php');

/* PARAMS   */
$level              = optional_param('level',0,PARAM_INT);
$return             = new moodle_url('/report/generator/index.php');
$return_url         = new moodle_url('/report/generator/company_structure/company_structure.php');
$return_err         = new moodle_url('/report/generator/import_structure/import.php',array('level'=>$level));
$url                = new moodle_url('/report/generator/import_structure/import.php',array('level'=>$level));
$imported           = false;
$err_import         = false;
$table_not_imported = null;
$error              = NON_ERROR;
$per_page           = 4;
$total_not_imported = 0;
/* Array of all fields for validation */
$std_fields = array('company','county','municipality','industry');

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
$PAGE->navbar->add(get_string('report_generator','local_tracker'),$return);
$PAGE->navbar->add(get_string('company_structure', 'report_generator'),$return_url);
$PAGE->navbar->add(get_string('header_import', 'report_generator'),$url);

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

if ($level <= 1) {
    $disabled = 'disabled';
}else {
    $disabled = '';
}
$form = new generator_import_structure_form(null,array($level,$disabled));
if ($form->is_cancelled()) {
    setcookie('parentImportTwo',0);
    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    setcookie('parentImportTwo',0);
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
        $imported       = true;
        $records_file   = Import_Companies::ValidateData($file_columns,$cir);
        switch ($level) {
            case 2:
                $level_parent = $data->parent_1;
                break;
            case 3:
                $level_parent = $data->parent_2;
                break;
            default:
                $level_parent = null;
                break;
        }//switch_level
        $err_import     = Import_Companies::ImportStructure($records_file,$level,$level_parent);
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
        echo $OUTPUT->notification(get_string('csv_load_error','report_generator'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case CSV_EMPTY_FILE:
        echo $OUTPUT->notification(get_string('csv_empty_file','report_generator'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case CANNOT_READ_TMP_FILE:
        echo $OUTPUT->notification(get_string('cannot_read_tmp_file','report_generator'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case CSV_FEW_COLUMNS;
        echo $OUTPUT->notification(get_string('csv_few_columns','report_generator'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case INVALID_FILE_NAME:
        echo $OUTPUT->notification(get_string('invalid_field_name','report_generator'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case DUPLICATE_FIELD_NAME:
        echo $OUTPUT->notification(get_string('duplicate_field_name','report_generator'), 'notifysuccess');
        echo '<br>';
        echo $OUTPUT->continue_button($return_err);
        break;
    case NON_ERROR:
        /* Import the companies */
        if ($imported) {
            if ($err_import) {
                if ($table_not_imported) {
                    echo $OUTPUT->notification(get_string('non_import_structure','report_generator'), 'notifysuccess');
                    echo '<br>';
                    echo html_writer::tag('div', html_writer::table($table_not_imported), array('class'=>'flexible-wrap'));
                    echo '<br>';
                    echo $OUTPUT->notification(get_string('num_errors','report_generator',format_string($total_not_imported)), 'notifysuccess');
                    echo $OUTPUT->continue_button($return_url);
                }else {
                    echo $OUTPUT->notification(get_string('imported_structure','report_generator'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return_url);
                }//if_records_file_errors
            }else {
                echo $OUTPUT->notification(get_string('error_import_structure','report_generator'), 'notifysuccess');
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