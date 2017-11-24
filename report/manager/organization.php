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

global $PAGE,$SITE,$OUTPUT,$USER,$SESSION;

// Params
$zero           = required_param('zero',PARAM_INT);
$one            = required_param('one',PARAM_INT);
$two            = required_param('two',PARAM_INT);
$level          = required_param('level',PARAM_INT);
$superUser      = required_param('sp',PARAM_INT);
$parent         = null;
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

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
require_sesskey();

echo $OUTPUT->header();

// Get Companies connected with super user
$IsReporter = CompetenceManager::is_reporter($USER->id);
if ($superUser) {
    $myAccess   = CompetenceManager::get_my_access($USER->id);
}else {
    /* My Hierarchy */
    $myHierarchy = CompetenceManager::get_my_hierarchy_level($USER->id,$context,$IsReporter,0);
    if ($IsReporter) {
        switch ($level) {
            case 1:
                if ($myHierarchy->competence->levelone[$zero]) {
                    $myLevelOne = $myHierarchy->competence->levelone[$zero];
                    $myLevelOne = implode(',',$myLevelOne);
                }

                break;
            case 2:
                if ($myHierarchy->competence->leveltwo[$one]) {
                    $myLevelTwo = $myHierarchy->competence->leveltwo[$one];
                    $myLevelTwo = implode(',',$myLevelTwo);
                }

                break;
            case 3:
                if ($myHierarchy->competence->levelthree[$two]) {
                    $myLevelThree = $myHierarchy->competence->levelthree[$two];
                    $myLevelThree = implode(',',$myLevelThree);
                }

                break;
        }
    }else {
        list($myLevelZero,$myLevelOne,$myLevelTwo,$myLevelThree) = CompetenceManager::get_my_companies_by_Level($myHierarchy->competence);
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
                $myLevelAccess = $myLevelZero;
            }//if_myLevelZero
        }

        break;
    case 1:
        $parent = $zero;
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
                $myLevelAccess = $myLevelOne;
            }//if_myLevelZero
        }

        break;
    case 2:
        $parent = $one;
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
                $myLevelAccess = $myLevelTwo;
            }//if_myLevelZero
        }

        break;
    case 3:
        $parent = $two;
        $toClean[0] = REPORT_MANAGER_EMPLOYEE_LIST;

        /* Companies Connected with */
        if ($superUser) {
            if (($levelZero) && ($myAccess)) {
                $myLevelAccess = $myAccess[$levelZero]->levelThree;
            }//if_parent
        }else {
            if ($myLevelThree) {
                $myLevelAccess = $myLevelThree;
            }//if_myLevelZero
        }

        break;
}//switch
$data['clean'] = $toClean;

// Company list
if ($parent) {
    $options = CompetenceManager::get_companies_level_list($level,$parent,$myLevelAccess);
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

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));

