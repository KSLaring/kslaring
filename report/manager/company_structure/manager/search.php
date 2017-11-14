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
 * Report Competence Manager - Managers - Search process
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      company_structure/manager
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

global $PAGE, $OUTPUT,$USER;
require_once('../../../../config.php');
require_once( 'managerslib.php');

/* PARAMS   */
$level      = required_param('level',PARAM_INT);
$levelZero  = required_param('levelzero',PARAM_INT);
$levelOne   = required_param('levelone',PARAM_INT);
$levelTwo   = required_param('leveltwo',PARAM_INT);
$levelThree = required_param('levelthree',PARAM_INT);
$search     = required_param('search',PARAM_TEXT);
$selectorId = required_param('selectorid',PARAM_ALPHANUM);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;
$parents        = array();
$tardis         = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/company_structure/manager/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Companies by Level */
switch ($level) {
    case 0:
        $parents[0] = $levelZero;

        break;
    case 1:
        $parents[0] = $levelZero;
        $parents[1] = $levelOne;

        break;
    case 2:
        $parents[0] = $levelZero;
        $parents[1] = $levelOne;
        $parents[2] = $levelTwo;

        break;
    case 3:
        $parents[0] = $levelZero;
        $parents[1] = $levelOne;
        $parents[2] = $levelTwo;
        $parents[3] = $levelThree;

        break;
}//switch_level

/* Validate if exits the selector   */
if (!isset($USER->manager_selectors[$selectorId])) {
    print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->manager_selectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

list($results,$tardis) = Managers::$class($search,$parents,$level);

foreach ($results as $groupName => $managers) {
    $groupData = array('name' => $groupName, 'users' => array());

    unset($managers[0]);

    foreach ($managers as $id=>$user) {
        $output     = new stdClass;
        $output->id     = $id;
        $output->name   = $user;
        $output->tardis = 0;
        if ($tardis) {
            if (array_key_exists($id,$tardis)) {
                $output->tardis = 1;
            }
        }
        if (!empty($user->disabled)) {
            $output->disabled = true;
        }
        if (!empty($user->infobelow)) {
            $output->infobelow = $user->infobelow;
        }
        $groupData['users'][$output->name] = $output;
    }

    $json[] = $groupData;
}

echo json_encode(array('results' => $json));
