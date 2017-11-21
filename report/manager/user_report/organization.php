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
 * @subpackage      manager/user_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    24/05/2017
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../managerlib.php');

global $USER,$PAGE,$OUTPUT;

// Params
$zero           = required_param('zero',PARAM_INT);
$one            = required_param('one',PARAM_INT);
$two            = required_param('two',PARAM_INT);
$level          = required_param('level',PARAM_INT);
$myLevelZero    = null;
$myLevelOne     = null;
$myLevelTwo     = null;
$myLevelThree   = null;
$myCompanies    = null;
$parent         = null;

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/user_report/organization.php');

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

// Get data
$data       = array('name' => COMPANY_STRUCTURE_LEVEL . $level, 'items' => array(),'clean' => array());
$toClean    = array();
switch ($level) {
    case 0:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 0;
        $toClean[1] = COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[2] = COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[3] = COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 1:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[1] = COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[2] = COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 2:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[1] = COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 3:
        $toClean[0] = COMPANY_STRUCTURE_LEVEL . 3;

        break;
    default:
        break;
}
$data['clean'] = $toClean;

// Hierarchy
$IsReporter = CompetenceManager::is_reporter($USER->id);
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

switch ($level) {
    case 0:
        if ($myLevelZero) {
            $myCompanies = implode(',',$myLevelZero);
        }//if_myLevelZero

        break;
    case 1:
        $parent = $zero;
        $myCompanies = $myLevelOne;

        break;
    case 2:
        $parent = $one;
        $myCompanies = $myLevelTwo;

        break;
    case 3:
        $parent = $two;
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
        // Info
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

        // Add company
    $data['items'][$infoCompany->name] = $infoCompany;
}
}

// Send data
$json[] = $data;
echo json_encode(array('results' => $json));

