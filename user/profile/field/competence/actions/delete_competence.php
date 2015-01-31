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
$user_id        = required_param('id',PARAM_INT);
$competence_id  = required_param('uc',PARAM_INT);
$levelThree     = required_param('co',PARAM_INT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);

$my_competence  = null;
$to_delete      = null;
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'uc' => $competence_id, 'co' => $levelThree));
$confirm_url    = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'uc' => $competence_id, 'co' => $levelThree, 'confirm' => true));
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
$my_competence = Competence::Get_CompetenceData($user_id);
if ($levelThree) {
    $to_delete     = $my_competence->companies[$levelThree];

}else {
    $to_delete              = new stdClass();
    $to_delete->levelThree  = 0;
    $to_delete->path        = '';
    $to_delete->roles       = $my_competence->generics;
}//if_levelThree


/* First Confirm    */
if (!$confirmed) {
    /* Print Header */
    echo $OUTPUT->header();

    if ($levelThree) {
        $a = new stdClass();
        $a->company = $to_delete->path;
        $a->roles   = implode(',',$to_delete->roles);
        echo $OUTPUT->confirm(get_string('delete_competence_are_sure','profilefield_competence',$a),$confirm_url,$return_url);
    }else {
        echo $OUTPUT->confirm(get_string('delete_generics_are_sure','profilefield_competence',implode(',',$my_competence->generics)),$confirm_url,$return_url);
    }//if_levelThree

    /* Print Footer */
    echo $OUTPUT->footer();
}else {
    Competence::DeleteCompetence($user_id,$competence_id,$to_delete);
    redirect($return_url);
}//if_confirm

