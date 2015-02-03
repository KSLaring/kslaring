<?php
/**
 * Extra Profile Field Competence - Delete Competence
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

require_once('../../../../../config.php');
require_once('../competencelib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS */
$user_id            = optional_param('id',0,PARAM_INT);
/* Competence Date ID   */
$competence_data    = optional_param('icd',0,PARAM_INT);
/* Competence Data      */
$competence         = optional_param('ic',0,PARAM_INT);
$confirmed          = optional_param('confirm', false, PARAM_BOOL);

$my_competence  = null;
$my_hierarchy   = null;
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $competence_data,'ic' => $competence));
$confirm_url    = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $competence_data,'ic' => $competence,'confirm' => true));
$return_url     = new moodle_url('/user/profile/field/competence/competence.php',array('id' =>$user_id));

/* Settings Page    */
$PAGE->https_required();
$PAGE->set_context(CONTEXT_USER::instance($user_id));
$PAGE->set_course($SITE);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('pluginname','profilefield_competence'),$return_url);
$PAGE->navbar->add(get_string('delete_competence','profilefield_competence'));
$PAGE->set_url($url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Get My Competence    */
    $my_competence = Competence::Get_CompetenceData($user_id,$competence_data,$competence);
    $my_hierarchy  = $my_competence[$competence_data];


/* First Confirm    */
if (!$confirmed) {
    /* Print Header */
    echo $OUTPUT->header();

        $a = new stdClass();
        $a->company = $my_hierarchy->path;
        $a->roles   = implode(',',$my_hierarchy->roles);

        echo $OUTPUT->confirm(get_string('delete_competence_are_sure','profilefield_competence',$a),$confirm_url,$return_url);

    /* Print Footer */
    echo $OUTPUT->footer();
}else {
    Competence::DeleteCompetence($user_id,$competence_data,$competence);

    redirect($return_url);
}//if_confirm

