<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
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

global $PAGE,$USER,$OUTPUT;

// Params
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
$options        = array();
$info           = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/organization.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}
require_sesskey();

echo $OUTPUT->header();

// Comapnies connected with super user
if ($superUser) {
    $myAccess   = CompetenceManager::get_my_access($USER->id);
}//if_superUser

// Get data
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

// Company list
if ($parent) {
    if ($superUser) {
        $options = CompetenceManager::get_companies_level_list($level,$parent,$myLevelAccess);
    }else {
        $options = CompetenceManager::get_companies_level_list($level,$parent);
    }

}else {
    // First element of the list
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

if ($options) {
    foreach ($options as $companyId => $company) {
        /* Info Company */
        $info            = new stdClass;
        $info->id        = $companyId;
        $info->name      = $company;

        /* Add Company*/
        $data['items'][$info->name] = $info;
    }
}

$extra = CompetenceManager::get_extra_info_company($parent);
$data['extra'] = $extra;

// Send data
$json[] = $data;
echo json_encode(array('results' => $json));

