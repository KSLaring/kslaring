<?php
/**
 * Report Competence Manager - Company structure - Manager
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      company_structure/manager
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once( '../company_structurelib.php');
require_once( 'managerslib.php');
require_once('manager_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* PARAMS */
$level          = optional_param('le',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$url            = new moodle_url('/report/manager/company_structure/company_structure.php');
$returnUrl      = new moodle_url('/report/manager/index.php');


/* Levels   */

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($siteContext);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_managers','report_manager'));

$PAGE->verify_https_required();

/* ADD require_capability */
require_capability('report/manager:edit', $siteContext);

/* Show Form */
$parents    = $SESSION->parents;
$form       = new report_manager_managers_form(null,array($level,$parents,$addSearch,$removeSearch));

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($url);
}else if($data = $form->get_data()) {

    /* Add Managers     */
    if (!empty($data->add_sel)) {
        if (isset($data->addselect)) {
            Managers::AddManagers($data->le,$parents,$data->addselect);
        }//if_addselect
    }//if_add

    /* Remove Managers  */
    if (!empty($data->remove_sel)) {
        if (isset($data->removeselect)) {
            Managers::RemoveManagers($data->le,$parents,implode(',',$data->removeselect));
        }//if_removeselect
    }//if_remove

    $_POST = array();
    //redirect($url);
}//if_else

/* Print Header */
echo $OUTPUT->header();

/* Print Title */
echo $OUTPUT->heading(get_string('title_managers', 'report_manager'));

$form->display();

/* Initialise Selectors */
Managers::Init_Managers_Selectors($addSearch,$removeSearch,$level,$parents);

/* Print Footer */
echo $OUTPUT->footer();