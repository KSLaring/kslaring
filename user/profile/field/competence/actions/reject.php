<?php
/**
 * Extra Profile Field Competence - Reject Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2016
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once($CFG->libdir . '/adminlib.php');

/* PARAMS */
$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/user/profile/field/competence/actions/reject.php');
$competenceRequest  = null;
$info               = null;
$user               = null;

$relativePath   = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relativePath, '/'));

$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* Print Header */
echo $OUTPUT->header();

if (count($args) != 2) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
        echo $OUTPUT->error_text('<h4>' . get_string('err_link','profilefield_competence') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    $competenceRequest = Competence::competence_request($args[0]);

    if (!$competenceRequest) {
        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
            echo $OUTPUT->error_text('<h4>' . get_string('comp_delete','profilefield_competence') . '</h4>');
        echo html_writer::end_tag('div');
    }else {
        /* User Info    */
        $user = get_complete_user_data('id',$competenceRequest->userid);
        $info = new stdClass();
        $info->company  = $competenceRequest->company;
        $info->user     = fullname($user);

        if (Competence::reject_competence($competenceRequest,$args[1])) {
            echo html_writer::start_tag('div');
            echo '<h4>' . get_string('request_rejected','profilefield_competence',$info)  . '</h4>';
            echo html_writer::end_tag('div');
        }else {
            echo html_writer::start_tag('div');
            echo '<h4>' . get_string('err_process','profilefield_competence')  . '</h4>';
            echo html_writer::end_tag('div');
        }
    }//if_competenceRequest
}//if_arg

/* Print Footer */
echo $OUTPUT->footer();