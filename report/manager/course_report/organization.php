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

global $PAGE,$USER,$OUTPUT,$CFG;

// Params
$zero           = required_param('zero',PARAM_INT);
$one            = required_param('one',PARAM_INT);
$two            = required_param('two',PARAM_INT);
$level          = required_param('level',PARAM_INT);
$reportLevel    = required_param('rpt',PARAM_INT);
$myLevelZero    = null;
$myLevelOne     = null;
$myLevelTwo     = null;
$myLevelThree   = null;
$myCompanies    = null;
$parent         = null;
$options        = array();

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/course_report/organization.php');


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

// Get my hierarchy level
$IsReporter     = CompetenceManager::is_reporter($USER->id);
$myHierarchy    = CompetenceManager::get_my_hierarchy_level($USER->id,$context,$IsReporter,$reportLevel);

if ($IsReporter) {
    $hierarchy = null;
    switch ($reportLevel) {
        case 0:
            $hierarchy  = $myHierarchy->competence->hierarchyzero;

            break;
        case 1:
            $hierarchy  = $myHierarchy->competence->hierarchyone;

            break;
        case 2:
            $hierarchy  = $myHierarchy->competence->hierarchytwo;

            break;
        case 3:
            $hierarchy  = $myHierarchy->competence->hierarchythree;

            break;
    }

    switch ($level) {
        case 1:
            if (($hierarchy->one) && isset($hierarchy->one[$zero])) {
                $myLevelOne = $hierarchy->one[$zero];
                $myLevelOne = implode(',',$myLevelOne);
            }

            break;
        case 2:
            if (($hierarchy->two) && isset($hierarchy->two[$one])) {
                $myLevelTwo = $hierarchy->two[$one];
                $myLevelTwo = implode(',',$myLevelTwo);
            }

            break;
        case 3:
            if (($hierarchy->three) && isset($hierarchy->three[$two])) {
                $myLevelThree   = $hierarchy->three[$two];
                $myLevelThree   = implode(',',$myLevelThree);
            }

            break;
    }
}else {
    list($myLevelZero,$myLevelOne,$myLevelTwo,$myLevelThree) = CompetenceManager::get_my_companies_by_level($myHierarchy->competence);
}//if_IsReporter

// Get data
$name       = COMPANY_STRUCTURE_LEVEL . $level;
$data       = array('name' => $name, 'items' => array(),'clean' => array());
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
                $toClean[1] = COMPANY_STRUCTURE_LEVEL . 2;
                $toClean[2] = REPORT_MANAGER_JOB_ROLE_LIST;

                break;
            case 2:
                $toClean[0] = COMPANY_STRUCTURE_LEVEL . 2;
                $toClean[1] = REPORT_MANAGER_JOB_ROLE_LIST;

                break;
        }

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

switch ($level) {
    case 0:
        $myCompanies = $myLevelZero;

        break;
    case 1:
        $parent      = $zero;
        $myCompanies = $myLevelOne;

        break;
    case 2:
        $parent      = $one;
        $myCompanies = $myLevelTwo;

        break;
    case 3:
        $parent      = $two;
        $myCompanies = $myLevelThree;

        break;
}//switch_level

// Get companies list
if ($parent) {
    $options = CompetenceManager::get_companies_level_list($level,$parent,$myCompanies);
}else {
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

if ($options) {
foreach ($options as $companyId => $company) {
        // Company info
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

        // Add company
    $data['items'][$infoCompany->name] = $infoCompany;
}
}

// Encode and send
$json[] = $data;
echo json_encode(array('results' => $json));

