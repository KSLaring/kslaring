<?php
/**
 * Report Competence Manager - Company Structure
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

require_once('../../config.php');
require_once('managerlib.php');

/* PARAMS   */
$parent         = optional_param('parent',0,PARAM_INT);
$levelZero      = optional_param('levelZero',0,PARAM_INT);
$level          = required_param('level',PARAM_INT);
$superUser      = required_param('sp',PARAM_INT);
$myHierarchy    = null;
$myLevelZero    = null;
$myLevelOne     = null;
$myLevelTwo     = null;
$myLevelThree   = null;

$myAccess       = null;
$myLevelAccess  = null;
$IsReporter     = null;

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/organization.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Companies connected with super user  */
$IsReporter = CompetenceManager::IsReporter($USER->id);
if ($superUser) {
    $myAccess   = CompetenceManager::Get_MyAccess($USER->id);
}else {
    /* My Hierarchy */
    $myHierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$context,$IsReporter,0);
    if (($IsReporter) && (!is_siteadmin($user_id))) {
        $myLevelZero  = array_keys($myHierarchy->competence);
        $myLevelOne   = $myHierarchy->competence[$levelZero]->levelOne;
        $myLevelTwo   = $myHierarchy->competence[$levelZero]->levelTwo;
        $myLevelThree = $myHierarchy->competence[$levelZero]->levelThree;
    }else {
        list($myLevelZero,$myLevelOne,$myLevelTwo,$myLevelThree) = CompetenceManager::GetMyCompanies_By_Level($myHierarchy->competence,$myHierarchy->my_level);
    }//if_IsReporter
}//if_superUser

/* Get Data */
$data       = array('name' => COMPANY_STRUCTURE_LEVEL . $level, 'items' => array(),'clean' => array());
$toClean    = array();

switch ($level) {
    case 0:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 0;
        $toClean[1] = COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[2] = COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[3] = COMPANY_STRUCTURE_LEVEL . 3;
        $toClean[4] = REPORT_MANAGER_EMPLOYEE_LIST;

        /* Companies Connected with */
        if ($superUser) {
            if ($myAccess) {
                $myLevelAccess = implode(',',array_keys($myAccess));
            }//if_myAccess
        }else {
            if ($myLevelZero) {
                $myLevelAccess = implode(',',$myLevelZero);
            }//if_myLevelZero
        }

        break;
    case 1:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[1] = COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[2] = COMPANY_STRUCTURE_LEVEL . 3;
        $toClean[3] = REPORT_MANAGER_EMPLOYEE_LIST;

        /* Companies Connected with */
        if ($superUser) {
            if (($levelZero) && ($myAccess)) {
                $myLevelAccess = $myAccess[$levelZero]->levelOne;
            }//if_parent
        }else {
            if ($myLevelOne) {
                $myLevelAccess = implode(',',$myLevelOne);
            }//if_myLevelZero
        }

        break;
    case 2:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[1] = COMPANY_STRUCTURE_LEVEL . 3;
        $toClean[2] = REPORT_MANAGER_EMPLOYEE_LIST;

        /* Companies Connected with */
        if ($superUser) {
            if (($levelZero) && ($myAccess)) {
                $myLevelAccess = $myAccess[$levelZero]->levelTwo;
            }//if_parent
        }else {
            if ($myLevelTwo) {
                $myLevelAccess = implode(',',$myLevelTwo);
            }//if_myLevelZero
        }

        break;
    case 3:
        $toClean[0] = REPORT_MANAGER_EMPLOYEE_LIST;

        /* Companies Connected with */
        if ($superUser) {
            if (($levelZero) && ($myAccess)) {
                $myLevelAccess = $myAccess[$levelZero]->levelThree;
            }//if_parent
        }else {
            if ($myLevelThree) {
                $myLevelAccess = implode(',',$myLevelThree);
            }//if_myLevelZero
        }

        break;
}//switch
$data['clean'] = $toClean;

/* Get Companies List   */
if ($parent) {
    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$myLevelAccess);
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
