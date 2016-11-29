<?php
/**
 * Extra Profile Field Competence - Main Page
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
 */

require_once('../../../../config.php');
require_once('competencelib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// Guest can not edit.
if (isguestuser()) {
    print_error('guestnoeditprofile');
}

/* PARAMS */
$user_id        = required_param('id',PARAM_INT);
$my_competence  = null;
$out            = '';
$url            = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$user_id));


/* Settings Page    */
$PAGE->https_required();
$PAGE->set_context(contex_user::instance($user_id));
$PAGE->set_course($SITE);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('pluginname','profilefield_competence'));
$PAGE->set_url($url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Get My Competence Data   */
$my_competence  = Competence::get_competence_data($user_id);

echo $OUTPUT->header();
    /* Display the Competence and the actions */
    echo Competence::get_competence_table($my_competence,$user_id);
echo $OUTPUT->footer();