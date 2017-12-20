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

global $USER,$PAGE,$OUTPUT,$CFG;

// Params
$zero           = required_param('zero',PARAM_INT);
$one            = required_param('one',PARAM_INT);
$two            = required_param('two',PARAM_INT);
$three          = required_param('three',PARAM_INT);

$json           = array();
$data           = array();

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/user_report/activate.php');

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

$data = array('active' => 0);

$active = 0;
$active = CompetenceManager::is_reporter_in($USER->id,0,$zero);
// Check level one
if (!$active) {
    if ($one) {
        $active = CompetenceManager::is_reporter_in($USER->id,1,$zero,$one);

        // Check level two
        if (!$active) {
            if ($two) {
                $active = CompetenceManager::is_reporter_in($USER->id,2,$zero,$one,$two);

                // Check level three
                if (!$active) {
                    $active = CompetenceManager::is_reporter_in($USER->id,3,$zero,$one,$two,$three);
                }
            }
        }
    }
}

$data['active'] = ($active ? 1 : 0);

// Send data
$json[] = $data;
echo json_encode(array('results' => $json));