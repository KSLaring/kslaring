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
 * Report Competence Manager - Company Structure - JS get Employees
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/copany_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('company_structurelib.php');
require_once('../managerlib.php');

global $PAGE,$OUTPUT,$USER;

// Params
$levelThree     = optional_param('levelThree',0,PARAM_TEXT);
$employees      = optional_param('employees',0,PARAM_TEXT);
$delete         = optional_param('delete',0,PARAM_INT);
$deleteAll      = optional_param('deleteAll',0,PARAM_INT);

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/company_structure/employees.php');

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

// Get company
if ($levelThree) {
    $levelThree = str_replace('#',',',$levelThree);
}

// get employees
if ($employees) {
    $employees = str_replace('#',',',$employees);
}
if ($deleteAll) {
    company_structure::delete_employees($levelThree,$employees,true);
}else if ($delete) {
    company_structure::delete_employees($levelThree,$employees);
}else {
    $employees = company_structure::get_employee_level($levelThree);

    // GEt Employees Info to Send
    $employeesInfo = array();
    if ($employees) {
    foreach ($employees as $id=>$user) {
        /* Info */
        $info = new stdClass();
        $info->id   = $id;
        $info->name = $user;

        /* Add Employee Info    */
        $employeesInfo[$info->name] = $info;
    }
    }
    // Get data
    $data           =  array('users' => array());
    $data['users']  = $employeesInfo;
}//if_delete

$extra = CompetenceManager::get_extra_info_company($levelThree);
$data['extra'] = $extra;

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));