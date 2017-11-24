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
 * Report Competence Manager - Search Process - Users Selector
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    16/10/2015
 * @author          eFaktor     (fbv)
 *
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('spuserlib.php');

global $PAGE,$USER,$OUTPUT;

// Params
$search             = required_param('search',PARAM_RAW);
$selectorId         = required_param('selectorid',PARAM_ALPHANUM);
$levelZero          = optional_param('levelZero',0,PARAM_INT);
$levelOne           = optional_param('levelOne',0,PARAM_INT);
$levelTwo           = optional_param('levelTwo',0,PARAM_INT);
$levelThree         = optional_param('levelThree',0,PARAM_TEXT);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/super_user/search.php');

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

// Validate if exits the selector
if (!isset($USER->userselectors[$selectorId])) {
   print_error('unknownuserselector');
}//if_userselector

// Get the options connected with the selector
$optSelector = $USER->userselectors[$selectorId];

// Get class
$class = $optSelector['class'];

if ($levelThree) {
    $levelThree = str_replace('#',',',$levelThree);
}

// Find users
$results = SuperUser::$class($search,$levelZero,$levelOne,$levelTwo,$levelThree);
if ($results) {
foreach ($results as $groupName => $users) {
    $groupData = array('name' => $groupName, 'users' => array());

    unset($users[0]);

    foreach ($users as $id=>$user) {
        $output     = new stdClass;
        $output->id     = $id;
        $output->name   = $user;

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
}

// Send data
echo json_encode(array('results' => $json));
