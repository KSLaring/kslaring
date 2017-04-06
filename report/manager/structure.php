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
$info           = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/organization.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Companies connected with super user  */
if ($superUser) {
    $myAccess   = CompetenceManager::Get_MyAccess($USER->id);
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
        }

        break;
    case 3:
        $toClean[0] = REPORT_MANAGER_EMPLOYEE_LIST;

        /* Companies Connected with */
        if ($superUser) {
            if (($levelZero) && ($myAccess)) {
                $myLevelAccess = $myAccess[$levelZero]->levelThree;
            }//if_parent
        }

        break;
}//switch
$data['clean'] = $toClean;

/* Get Companies List   */
if ($parent) {
    if ($superUser) {
        $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$myLevelAccess);
    }else {
        $options = CompetenceManager::GetCompanies_LevelList($level,$parent);
    }

}else {
    // First element of the list
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

foreach ($options as $companyId => $company) {

    /* Info Company */
    $info            = new stdClass;
    $info->id        = $companyId;
    $info->name      = $company;

    /* Add Company*/
    $data['items'][$info->name] = $info;
}

$extra = CompetenceManager::get_extra_info_company($parent);
$data['extra'] = $extra;

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));

