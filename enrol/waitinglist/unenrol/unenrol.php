<?php
/**
 * Unenrol Action
 *
 * @package         enrol/waitinglist
 * @subpackage      unenrol
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
require('../../../config.php');
require_once('unenrollib.php');

/* PARAMS */
$contextSystem     = context_system::instance();
$returnUrl         = $CFG->wwwroot . '/index.php';
$url               = new moodle_url('/enrol/waitinglist/unenrol/unenrol.php');
$unenrol           = false;
$relativePath      = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relativePath, '/'));

$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* Print Header */
echo $OUTPUT->header();

if (count($args) != 4) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
    echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    
    /* Check if the user has already been unenrolled    */
    if (Unenrol_Waiting::IsUnenrolled($args)) {
        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
        echo $OUTPUT->error_text('<h4>' . get_string('user_not_enrolled','enrol_waitinglist') . '</h4>');
        echo html_writer::end_tag('div');
    }else {
        /* Check Arguments for unenrol action   */
        $unenrol = Unenrol_Waiting::Check_UnenrolLink($args);
        if ($unenrol) {
            /* Right --> Unenrol user   */
            if (Unenrol_Waiting::UnenrolUser($args)) {
                echo html_writer::start_tag('div',array('class' => 'loginerrors'));
                echo $OUTPUT->error_text('<h4>' . get_string('user_unenrolled','enrol_waitinglist') . '</h4>');
                echo html_writer::end_tag('div');
            }else {
                echo html_writer::start_tag('div',array('class' => 'loginerrors'));
                echo $OUTPUT->error_text('<h4>' . get_string('err_process','enrol_waitinglist') . '</h4>');
                echo html_writer::end_tag('div');
            }
        }else {
            /* Wrong --> Error Missatge */
            echo html_writer::start_tag('div',array('class' => 'loginerrors'));
            echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
            echo html_writer::end_tag('div');
        }        
    }

}

/* Print Footer */
echo $OUTPUT->footer();