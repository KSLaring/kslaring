<?php
/**
 * Report Competence Manager - Job Role
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('managerlib.php');
require_once( 'outcome_report/outcomerptlib.php');

/* PARAMS   */
$level          = required_param('level',PARAM_INT);
$levelZero      = required_param('levelZero',PARAM_INT);
$levelOne       = optional_param('levelOne',0,PARAM_INT);
$levelTwo       = optional_param('levelTwo',0,PARAM_INT);
$levelThree     = optional_param('levelThree',0,PARAM_TEXT);
$outcome        = optional_param('outcome',0,PARAM_INT);

$json           = array();
$data           = array();
$options        = array();
$jobRoles       = array();
$infoJR         = null;
$jrOutcomes     = array();

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/jobrole.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('jr' => array());

/* Get Job Roles    */
$options[0] = get_string('select_level_list','report_manager');
switch ($level) {
    case 0:
        /* Job Roles connected with level   */
        if ($levelZero) {
            /* Add Generics --> Only Public Job Roles   */
            if (CompetenceManager::IsPublic($levelZero)) {
                CompetenceManager::GetJobRoles_Generics($options);
            }//if_isPublic

            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero);
        }//if_level_Zero

        break;
    case 1:
        /* Add Generics --> Only Public Job Roles   */
        if (CompetenceManager::IsPublic($levelZero)) {
            CompetenceManager::GetJobRoles_Generics($options);
        }//if_isPublic

        /* Job Roles connected with level   */
        if ($levelOne) {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero);
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne);
        }//if_level_One

        break;
    case 2:
        /* Add Generics --> Only Public Job Roles   */
        if (CompetenceManager::IsPublic($levelOne)) {
            CompetenceManager::GetJobRoles_Generics($options);
        }//if_isPublic

        /* Job Roles connected with level   */
        if ($levelTwo) {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero);
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne);
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo);
        }//if_level_Two

        break;
    case 3:
        /* Add Generics --> Only Public Job Roles   */
        if (CompetenceManager::IsPublic($levelTwo)) {
            CompetenceManager::GetJobRoles_Generics($options);
        }//if_isPublic

        /* Job Roles connected with level   */
        if ($levelThree) {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo,$levelThree);
        }else {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-3,$levelZero);
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero,$levelOne);
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
        }//if_level_Three

        break;
}//switch_level

/* Only the Job Roles connected to the outcome and level    */
if ($outcome) {
    $jrOutcomes = outcome_report::Outcome_JobRole_List($outcome);
    if ($jrOutcomes) {
        $jrOutcomes[0] = 0;
        $options = array_intersect_key($options,$jrOutcomes);
    }//if_jr_outcomes
}//if_outcome_selected

foreach ($options as $id => $jr) {
    /* Info Company */
    $infoJR            = new stdClass;
    $infoJR->id        = $id;
    $infoJR->name      = $jr;

    /* Add Company*/
    $jobRoles[$infoJR->name] = $infoJR;
}

$data['jr'] = $jobRoles;
$json[]     = $data;
echo json_encode(array('results' => $json));
