<?php
/**
 * Report Competence Manager - Company Structure - Course Report
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../managerlib.php');

/* PARAMS   */
$parent         = optional_param('parent',0,PARAM_INT);
$levelZero      = optional_param('levelZero',0,PARAM_INT);
$level          = required_param('level',PARAM_INT);
$reportLevel    = required_param('rpt',PARAM_INT);
$myLevelZero    = null;
$myLevelOne     = null;
$myLevelTwo     = null;
$myLevelThree   = null;
$myCompanies    = null;

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/course_report/organization.php');

global $PAGE,$USER;

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('name' => COMPANY_STRUCTURE_LEVEL . $level, 'items' => array(),'clean' => array());
$toClean    = array();

switch ($reportLevel) {
    case 0:
        $toClean[0] = REPORT_MANAGER_JOB_ROLE_LIST;

        break;
    case 1:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[1] = REPORT_MANAGER_JOB_ROLE_LIST;

        break;
    case 2:
        switch ($level) {
            case 1:
                $toClean[0] = COMPANY_STRUCTURE_LEVEL . 1;


                break;
            case 2:
                $toClean[0] = COMPANY_STRUCTURE_LEVEL . 2;

                break;
        }

        $toClean[1] = REPORT_MANAGER_JOB_ROLE_LIST;

        break;
    case 3:
        switch ($level) {
            case 1:
                $toClean[0] = COMPANY_STRUCTURE_LEVEL . 1;
                $toClean[1] = COMPANY_STRUCTURE_LEVEL . 2;
                $toClean[2] = COMPANY_STRUCTURE_LEVEL . 3;
                $toClean[3] = REPORT_MANAGER_JOB_ROLE_LIST;

                break;
            case 2:
                $toClean[0] = COMPANY_STRUCTURE_LEVEL . 2;
                $toClean[1] = COMPANY_STRUCTURE_LEVEL . 3;
                $toClean[2] = REPORT_MANAGER_JOB_ROLE_LIST;

                break;
            case 3:
                $toClean[0] = REPORT_MANAGER_JOB_ROLE_LIST;

                break;
        }

        break;
}//switch_reportLevel
$data['clean'] = $toClean;

/* Get My Companies by Level    */
/* My Hierarchy */
$IsReporter = CompetenceManager::IsReporter($USER->id);
$myHierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$context,$IsReporter,$reportLevel);
if ($IsReporter) {
    $myLevelZero  = array_keys($myHierarchy->competence);
    $myLevelOne   = $myHierarchy->competence[$levelZero]->levelOne;
    $myLevelTwo   = $myHierarchy->competence[$levelZero]->levelTwo;
    $myLevelThree = $myHierarchy->competence[$levelZero]->levelThree;
}else {
    list($myLevelZero,$myLevelOne,$myLevelTwo,$myLevelThree) = CompetenceManager::GetMyCompanies_By_Level($myHierarchy->competence,$myHierarchy->my_level);
}//if_IsReporter

switch ($level) {
    case 0:
        if ($myLevelZero) {
            $myCompanies = implode(',',$myLevelZero);
        }//if_myLevelZero

        break;
    case 1:
        if ($myLevelOne) {
            $myCompanies = implode(',',$myLevelOne);
        }//if_myLevelZero

        break;
    case 2:
        if ($myLevelTwo) {
            $myCompanies = implode(',',$myLevelTwo);
        }//if_myLevelZero

        break;
    case 3:
        if ($myLevelThree) {
            $myCompanies = implode(',',$myLevelThree);
        }//if_myLevelZero

        break;
}//switch_level

/* Get Companies List   */
if ($parent) {
    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompanies);
}else {
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

foreach ($options as $companyId => $company) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

    /* Add Company*/
    $data['items'][$infoCompany->name] = $infoCompany;
}

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));

