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

global $PAGE,$USER,$OUTPUT;

// Params
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


$PAGE->set_context($context);
$PAGE->set_url($url);

// Check correct access
require_login();
require_sesskey();

echo $OUTPUT->header();

// Get data
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

// Get comapnies by level
// My hierarchy
$IsReporter     = CompetenceManager::IsReporter($USER->id);
$myHierarchy    = CompetenceManager::get_MyHierarchyLevel($USER->id,$context,$IsReporter,$reportLevel);
if ($IsReporter) {
    $myLevelZero  = implode(',',array_keys($myHierarchy->competence));
    $myLevelOne   = $myHierarchy->competence[$levelZero]->levelone;
    $myLevelTwo   = $myHierarchy->competence[$levelZero]->leveltwo;
    $myLevelThree = $myHierarchy->competence[$levelZero]->levelthree;
}else {
    list($myLevelZero,$myLevelOne,$myLevelTwo,$myLevelThree) = CompetenceManager::get_mycompanies_by_level($myHierarchy->competence);
}//if_IsReporter

switch ($level) {
    case 0:
        $myCompanies = $myLevelZero;

        break;
    case 1:
        $myCompanies = $myLevelOne;

        break;
    case 2:
        $myCompanies = $myLevelTwo;

        break;
    case 3:
        $myCompanies = $myLevelThree;

        break;
}//switch_level

// Get companies list
if ($parent) {
    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompanies);
}else {
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent
foreach ($options as $companyId => $company) {
    // Company info
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

    // Add company
    $data['items'][$infoCompany->name] = $infoCompany;
}

// Encode and send
$json[] = $data;
echo json_encode(array('results' => $json));

