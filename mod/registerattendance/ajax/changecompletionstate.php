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
 * Changes the completion state for the given user and the given course module.
 *
 * Please note functions may throw exceptions, please ensure your JS handles them as well
 * as the outcome objects.
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$action = required_param('action', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);
$state = required_param('state', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$userids = optional_param('userids', '', PARAM_RAW);

require_sesskey(); // Gotta have the sesskey.
require_login(); // Gotta be logged in.
$PAGE->set_context(context_module::instance($cmid));

// Prepare an outcome object. We always use this.
$outcome = new stdClass;
$outcome->error = false;
$outcome->outcome = false;

echo $OUTPUT->header();

switch ($action) {
    case 'changestate':
        if ($userid) {
            $result = mod_registerattendance_helper::change_completionstate($cmid, $userid, $state);

            if ($result) {
                $feedback = new stdClass();
                $feedback->state = $state;
                $feedback->amount = 1;

                $outcome->outcome = $feedback;
            } else {
                $outcome->error = true;
                $outcome->outcome = $result;
            }
        } else {
            $feedback = new stdClass();
            $feedback->state = $state;
            $feedback->amount = 0;

            $outcome->outcome = $feedback;
        }
        break;

    case 'bulkchangestate':
        $result = false;

        if (is_array($userids)) {
            $result = mod_registerattendance_helper::change_completionstates($cmid, $userids, $state);

            if ($result) {
                $feedback = new stdClass();
                $feedback->state = $state;
                $feedback->amount = count($userids);

                $outcome->outcome = $feedback;
            } else {
                $outcome->error = true;
                $outcome->outcome = $result;
            }
        } else {
            $feedback = new stdClass();
            $feedback->state = $state;
            $feedback->amount = 0;

            $outcome->outcome = $feedback;
        }
        break;
}

echo json_encode($outcome);
echo $OUTPUT->footer();
// Don't ever even consider putting anything after this. It just wouldn't make sense.
exit;
