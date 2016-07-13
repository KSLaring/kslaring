<?php
/**
 * Extra Profile Field Competence - Edit Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/01/2015
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once('edit_competence_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// Guest can not edit.
if (isguestuser()) {
    print_error('guestnoeditprofile');
}

/* PARAMS */
$user_id            = optional_param('id',0,PARAM_INT);
/* Competence Date ID   */
$competence_data    = optional_param('icd',0,PARAM_INT);
/* Competence       */
$competence         = optional_param('ic',0,PARAM_INT);

$url            = new moodle_url('/user/profile/field/competence/actions/edit_competence.php',array('id' =>$user_id,'icd' => $competence_data,'ic' => $competence));
$return_url     = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$user_id));

/* Settings Page    */
$PAGE->https_required();
$PAGE->set_context(CONTEXT_USER::instance($user_id));
$PAGE->set_course($SITE);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('pluginname','profilefield_competence'),$return_url);
$PAGE->navbar->add(get_string('edit_competence','profilefield_competence'));
$PAGE->set_url($url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Form */
$form = new competence_edit_competence_form(null,array($user_id,$competence_data,$competence));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Update Competence Info   */
    Competence::EditCompetence($data);

    $_POST = array();
    redirect($return_url);
}//if_else

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();