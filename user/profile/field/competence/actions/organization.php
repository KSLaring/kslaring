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

require_once('../../../../../config.php');
require_once('../competencelib.php');

/* PARAMS   */
$parent         = optional_param('parent',0,PARAM_INT);
$level          = required_param('level',PARAM_INT);
$userId         = required_param('id',PARAM_INT);

$myCompanies    = null;

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/user/profile/field/competence/actions/organization.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('name' => 'level_' . $level, 'items' => array(),'clean' => array());
$toClean    = array();
/* To Clean */
switch ($level) {
    case 0:
        $toClean[0] = 'level_' . 0;
        $toClean[1] = 'level_' . 1;
        $toClean[2] = 'level_' . 2;
        $toClean[3] = 'level_' . 3;
        $toClean[4] = 'job_roles';

        break;
    case 1:
        $toClean[0] = 'level_' . 1;
        $toClean[1] = 'level_' . 2;
        $toClean[2] = 'level_' . 3;
        $toClean[3] = 'job_roles';

        break;
    case 2:
        $toClean[0] = 'level_' . 2;
        $toClean[1] = 'level_' . 3;
        $toClean[2] = 'job_roles';

        break;
    case 3:
        /* Get the companies connected with the user    */
        $myCompanies = Competence::get_mycompanies($userId);

        $toClean[0] = 'job_roles';

        break;
}//switch
$data['clean'] = $toClean;

/* Get Companies List   */
if ($parent) {
    $options = Competence::get_companies_level($level,$parent,$myCompanies);
}else {
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

foreach ($options as $companyId => $company) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

    /* Add Company*/
    $data['items'][$infoCompany->name] = $infoCompany;
}

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));

