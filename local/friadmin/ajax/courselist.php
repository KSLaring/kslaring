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
 * Performs friadmin courslist ajax actions.
 *
 * Please note functions may throw exceptions, please ensure your JS handles them as well
 * as the outcome objects.
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');

$action = required_param('action', PARAM_ALPHA);
$municipalityid = optional_param('municipalityid', -1, PARAM_INT);
$sectorid = optional_param('sectorid', -1, PARAM_INT);

require_sesskey(); // Gotta have the sesskey.
require_login(); // Gotta be logged in.
$PAGE->set_context(context_system::instance());

// Prepare an outcome object. We always use this.
$outcome = new stdClass;
$outcome->error = false;
$outcome->outcome = false;

echo $OUTPUT->header();

switch ($action) {
    case 'municipalitychange':
        $data = local_friadmin_helper::get_user_locationdata(null, $municipalityid);
        $outcome->outcome = array(
            'municipalityid' => $municipalityid,
            'sector' => $data['sector'],
            'location' => $data['location']
        );
        break;
    case 'sectorchange':
        $data = local_friadmin_helper::get_user_locationdata(null, $municipalityid, $sectorid);
        $outcome->outcome = array(
            'municipalityid' => $municipalityid,
            'sectorid' => $sectorid,
            'location' => $data['location']
        );
        break;
}

echo json_encode($outcome);
echo $OUTPUT->footer();
// Don't ever even consider putting anything after this. It just wouldn't make sense.
exit;
