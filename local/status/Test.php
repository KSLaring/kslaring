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

    // Plugin info
    $plugin = get_config('local_fellesdata');
/**
    // Get parameters service
    $to     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
    $to     = gmdate('Y-m-d\TH:i:s\Z',$to);
    $from   = gmdate('Y-m-d\TH:i:s\Z',0);

    echo "to --> " . $to . "</br>";
    echo "from --> " . $from . "</br>";

    // Build url end point
    $url = 'https://tjenester.bergen.kommune.no/tardis/fellesdata/v_leka_oren_tre_nivaa?fromDate=' . $from . '&toDate=' . $to;
    $url = trim($url);

    echo "END POINT --> " . $url . "</br>";

    echo " TESTING FELLESDATA STATUS CRON " . "</br>";
    echo "Start ... " . "</br>"; **/


    // Call cron
    if ($option) {
        global $SESSION;

        if (!isset($SESSION->manual)) {
            $SESSION->manual = true;
        }

        \STATUS_CRON::test($plugin,$option);
    }else {
        echo "You need a option" . "</br>";
        echo " Import status users              --> 1" . "</br>";
        echo " Import status organizations      --> 2" . "</br>";

        echo " Import status job roles          --> 3" . "</br>";
        echo " Import status managers/reporters --> 4" . "</br>";
        echo " Import status users competence   --> 5" . "</br></br>";

        echo " Sync status users                --> 6" . "</br>";
        echo " Sync status organizations        --> 7" . "</br>";
        echo " Sync status job roles            --> 8" . "</br>";
        echo " Sync status managers/reporters   --> 9" . "</br>";
        echo " Sync status users competence     --> 10" . "</br>";
    }

}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();