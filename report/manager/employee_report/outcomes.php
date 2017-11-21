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
 * Report Competence Manager - Employee Report - JS get Outcomes
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/employee_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('employeelib.php');

global $PAGE,$USER,$OUTPUT;

// Params
$levelZero    = required_param('levelZero',PARAM_INT);
$levelOne     = required_param('levelOne',PARAM_INT);
$levelTwo     = required_param('levelTwo',PARAM_INT);
$levelThree   = required_param('levelThree',PARAM_INT);

$json           = array();
$data           = array();
$outcome_lst    = null;
$outcomes       = array();
$info           = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/employee_report/outcomes.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

// Check the correct access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}
require_sesskey();

echo $OUTPUT->header();

// Get outcomes
if ($levelThree) {
    $outcome_lst    = array();
    $outcome_lst = EmployeeReport::GetOutcomes_EmployeeReport($levelZero,$levelOne,$levelTwo,$levelThree);
}else {
    $outcome_lst    = array();
    $outcome_lst[0] = get_string('select');
}//IF_COOKIE

// get data
$data               =  array('outcomes' => array());
if (!$outcome_lst) {
    $outcome_lst    = array();
    $outcome_lst[0] = get_string('select');
}

foreach ($outcome_lst as $id => $outcome) {
    /* Info Company */
    $info            = new stdClass;
    $info->id        = $id;
    $info->name      = $outcome;

    /* Add Company*/
    $outcomes[$info->name] = $info;
}
// Send data
$data['outcomes'] = $outcomes;
$json[] = $data;
echo json_encode(array('results' => $json));


