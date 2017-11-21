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
 * Report Competence Manager - Super User - Company Structure
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('spuserlib.php');
require_once('../managerlib.php');

global $USER,$PAGE,$OUTPUT;

// Params
$parent         = optional_param('parent',0,PARAM_INT);
$level          = required_param('level',PARAM_INT);

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/super_user/structure.php');

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
$data       = array('name' => SP_USER_COMPANY_STRUCTURE_LEVEL . $level, 'items' => array(),'clean' => array());
$toClean    = array();
switch ($level) {
    case 0:
        $toClean[0] = SP_USER_COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[1] = SP_USER_COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[2] = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 1:
        $toClean[0] = SP_USER_COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[1] = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 2:
        $toClean[0] = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;

        break;
}
$data['clean'] = $toClean;

if ($parent) {
    $options = CompetenceManager::get_companies_level_list($level,$parent);
}else {
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

if ($options) {
foreach ($options as $companyId => $company) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

    /* Add Company*/
    $data['items'][$infoCompany->name] = $infoCompany;
}
}

// Send
$json[] = $data;
echo json_encode(array('results' => $json));