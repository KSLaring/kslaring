<?php
/**
 * Extra Profile Field Competence - Add Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      27/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * New js to load the company structure
 *
 */

require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once('add_competence_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// Guest can not edit.
if (isguestuser()) {
    print_error('guestnoeditprofile');
}

/* PARAMS */
$user_id        = optional_param('id',0,PARAM_INT);
$my_companies   = null;

if ($user_id) {
    $SESSION->user_id = $user_id;
}else {
    $user_id = $SESSION->user_id;
}//If_user_id

$url            = new moodle_url('/user/profile/field/competence/actions/add_competence.php',array('id' =>$user_id));
$return_url     = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$user_id));

/* Settings Page    */
$PAGE->https_required();
$PAGE->set_context(context_user::instance($user_id));
$PAGE->set_course($SITE);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('pluginname','profilefield_competence'),$return_url);
$PAGE->navbar->add(get_string('add_competence','profilefield_competence'));
$PAGE->set_url($url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Get the companies connected with the user    */
$my_companies = Competence::get_mycompanies($user_id);

/* Form */
$form = new competence_add_competence_form(null,array($user_id,$my_companies));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {

    /* Add the Competence   */
    Competence::add_competence($data);

    unset($SESSION->user_id);
    $_POST = array();
    redirect($return_url);
}//if_else

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Initialise Organization Structure    */
Competence::init_organization_structure('level_','job_roles',$user_id);

/* Print Footer */
echo $OUTPUT->footer();