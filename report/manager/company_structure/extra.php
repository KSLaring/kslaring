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
 * Report Competence Manager - Company Structure - Extra Info Tardis
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/copany_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    14/11/2017
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('company_structurelib.php');
require_once('../managerlib.php');

global $CFG,$PAGE,$OUTPUT;

/* PARAMS   */
$zero   = required_param('zero',PARAM_INT);
$one    = required_param('one',PARAM_INT);
$two    = optional_param('two',0,PARAM_INT);
$three  = optional_param('three',0,PARAM_INT);

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/company_structure/extra.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

$data           =  array('extra' => array());

$extra = new stdClass();
$extra->zero  = CompetenceManager::get_extra_info_company($zero);
$extra->one   = CompetenceManager::get_extra_info_company($one);
$extra->two   = ($two ? CompetenceManager::get_extra_info_company($two) : 0);
$extra->three = ($three ? CompetenceManager::get_extra_info_company($three) : 0);

$data['extra'] = $extra;

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));