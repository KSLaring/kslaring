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

// PARAMS
$level          = required_param('level',PARAM_INT);
$levelZero      = required_param('levelZero',PARAM_INT);
$levelOne       = optional_param('levelOne',0,PARAM_INT);
$levelTwo       = optional_param('levelTwo',0,PARAM_INT);
$levelThree     = optional_param('levelThree',0,PARAM_TEXT);
$outcome        = optional_param('outcome',0,PARAM_INT);

$json           = array();
$data           = array();
$options        = array();
$jobRoles       = null;
$infoJR         = null;
$jrOutcomes     = array();

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/jobrole.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

// Correct access
require_login();
require_sesskey();

echo $OUTPUT->header();

// Get data
$data       = array('jr' => array());

// Get job roles
$options[0] = get_string('select_level_list','report_manager');
switch ($level) {
    case 0:
        // Job roles connected with the level
        if ($levelZero) {
            // Public job roles
            if (CompetenceManager::IsPublic($levelZero)) {
                CompetenceManager::GetJobRoles_Generics($options);
            }//if_isPublic

            // Job roles connected with the level
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero);
        }//if_level_Zero

        break;
    case 1:
        // Public job roles
        if (CompetenceManager::IsPublic($levelZero)) {
            CompetenceManager::GetJobRoles_Generics($options);
        }//if_isPublic

        // Job roles connected with the level
        if ($levelOne) {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne);
        }//if_level_One

        break;
    case 2:
        // Generics job roles
        if (CompetenceManager::IsPublic($levelOne)) {
            CompetenceManager::GetJobRoles_Generics($options);
        }//if_isPublic

        // Job roles connected with the level
        if ($levelTwo) {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo);
        }//if_level_Two

        break;
    case 3:
        // Generic job roles
        if (CompetenceManager::IsPublic($levelTwo)) {
            CompetenceManager::GetJobRoles_Generics($options);
        }//if_isPublic

        // Job roles connected with the level
        if ($levelThree) {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo,$levelThree);
        }else {
            CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
        }//if_level_Three

        break;
}//switch_level

// Only job roles connected with the level and the outcome
if ($outcome) {
    $jrOutcomes = outcome_report::Outcome_JobRole_List($outcome);
    if ($jrOutcomes) {
        $jrOutcomes[0] = 0;
        $options = array_intersect_key($options,$jrOutcomes);
    }//if_jr_outcomes
}//if_outcome_selected

if ($options) {
    foreach ($options as $id => $jr) {
        // Company info
        $infoJR            = new stdClass;
        $infoJR->id        = $id;
        $infoJR->name      = $jr;

        /* Add Company*/
        $jobRoles[$infoJR->name] = $infoJR;
    }

    $data['jr'] = $jobRoles;
}


$json[]     = $data;
echo json_encode(array('results' => $json));
