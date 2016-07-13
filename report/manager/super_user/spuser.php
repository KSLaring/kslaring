<?php
/**
 * Report Competence Manager - Super Users.
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    14/10/2015
 * @author          eFaktor     (fbv)
 */
require_once('../../../config.php');
require_once('spuser_form.php');
require_once('spuserlib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$url            = new moodle_url('/report/manager/super_user/spuser.php');
$indexUrl       = new moodle_url('/report/manager/index.php');
$returnUrl      = new moodle_url('/report/manager/company_structure/company_structure.php');
$site_context   = context_system::instance();
$levelZero      = null;
$levelOne       = null;
$levelTwo       = null;
$levelThree     = null;

/* Start the page */
$PAGE->https_required();

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('company_structure','report_manager'),$returnUrl);
$PAGE->navbar->add(get_string('spuser','report_manager'));

unset($SESSION->parents);

$PAGE->verify_https_required();

/* ADD require_capability */
require_capability('report/manager:edit', $site_context);


/* Show Form */
$form = new manager_spuser_form(null,array($addSearch,$removeSearch,$removeSelected));

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {

    /* Get Levels Super User    */
    list($levelZero,$levelOne,$levelTwo,$levelThree) = SuperUser::GetLevels_SuperUser($data);

    if (!empty($data->add_sel)) {
        if (isset($data->addselect)) {
            /* Create Super Users   */
            SuperUser::AddSuperUsers($data->addselect,$levelZero,$levelOne,$levelTwo,$levelThree);
        }//if_addselect
    }

    if (!empty($data->remove_sel)) {
        if ($removeSelected) {
            /* Remove Super Users   */
            SuperUser::RemoveSuperUsers($removeSelected,$levelZero,$levelOne,$levelTwo,$levelThree);
        }//if_addselect
    }

    $_POST = array();
    //redirect($returnUrl);
}//if_else



/* Print Header */
echo $OUTPUT->header();

/* Print tabs at the top */
$current_tab = 'spuser';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('spuser', 'report_manager'));

$form->display();

/* Initialise Selectors */
SuperUser::Init_SuperUsers_Selectors($addSearch,$removeSearch,$removeSelected);
/* Initialise Organization Structure    */
SuperUser::Init_Organization_Structure();

/* Print Footer */
echo $OUTPUT->footer();