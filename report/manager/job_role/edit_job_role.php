<?php
/**
 * Report Competence Manager - Job Role.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/job_role
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate      06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Edit Job Role
 *
 * @updateDate      26/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update to level Zero, One, Two and Three.
 * Add Industry Code
 *
 * @updateDate      26/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update to new java script to load the companies
 *
 */

require_once('../../../config.php');
require_once( 'jobrolelib.php');
require_once('../managerlib.php');
require_once('edit_job_role_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$job_role_id    = required_param('id',PARAM_INT);
$return_url     = new moodle_url('/report/manager/job_role/job_role.php');
$url            = new moodle_url('/report/manager/job_role/edit_job_role.php',array('id' => $job_role_id));
$return         = new moodle_url('/report/manager/index.php');
$superUser      = false;
$myAccess       = null;

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('job_roles', 'report_manager'),$return_url);
$PAGE->navbar->add(get_string('edit_job_roles', 'report_manager'));

/* Info Super Users */
$superUser  = CompetenceManager::IsSuperUser($USER->id);
$myAccess   = CompetenceManager::Get_MyAccess($USER->id);

/* Check permissions */
if (!$superUser) {
    require_capability('report/manager:edit', $site_context);
    $PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
}else {
    /* Check job role is valid  */
    if (!job_role::CheckJobRoleAccess($myAccess,$job_role_id)){
        print_error('nopermissions', 'error', '', 'report/manager:edit');
    }
}//if_SuperUser

/* Security */
if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Job Role Info    */
$jr_info = job_role::JobRole_Info($job_role_id);
/* Form     */
$form = new manager_edit_job_role_form(null,array($jr_info,$myAccess));
if ($form->is_cancelled()) {

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Update New Job Role */
    job_role::Update_JobRole($data);

    $_POST = array();
    redirect($return_url);
}//if_else

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Initialise Organization Structure    */
CompetenceManager::Init_Organization_Structure(COMPANY_STRUCTURE_LEVEL,null,null,$superUser,$myAccess,false);

/* Print Footer */
echo $OUTPUT->footer();