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

require( '../../config.php' );
require_once('../../local/fellesdata/lib/fellesdatalib.php');
require_once('cron/statuscron.php');
require_once('lib/statuslib.php');

require_login();

/* PARAMS */
$option = optional_param('op',0,PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/status/Test.php');

/* Print Header */
echo $OUTPUT->header();

try {
    echo " TESTING FELLESDATA STATUS CRON " . "</br>";
    echo "Start ... " . "</br>";

    // Plugin info
    $plugin = get_config('local_fellesdata');

    // Call cron
    \STATUS_CRON::test($plugin);
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();