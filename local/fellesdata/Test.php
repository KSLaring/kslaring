<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/02/16
 * Time: 12:45
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('cron/fellesdatacron.php');
require_once('lib/fellesdatalib.php');
require_once('lib/suspiciouslib.php');

require_login();

/* PARAMS */
$option = optional_param('op',0,PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/fellesdata/Test.php');

/* Print Header */
echo $OUTPUT->header();

try {
    echo " TESTING FELLESDATA CRON " . "</br>";
    echo "Start ... " . "</br>";

    if (!isset($SESSION->manual)) {
        $SESSION->manual = true;
    }

    $pluginInfo     = get_config('local_fellesdata');

    if ($option) {
        if ($option == 20) {
           echo "Sending suspicious notifications..." . "</br>";

            // Send Notifications
            suspicious::send_suspicious_notifications($pluginInfo);
            // Send Reminder
            suspicious::send_suspicious_notifications($pluginInfo,true);
        }else {
            FELLESDATA_CRON::cron_manual(true,$option);
        }
    }else {
        //
        //laststatus
        //fs_calendar_status

        // Calendar
        $calendar = array();
        $calendar[0] = new lang_string('sunday', 'calendar');
        $calendar[1] = new lang_string('monday', 'calendar');
        $calendar[2] = new lang_string('tuesday', 'calendar');
        $calendar[3] = new lang_string('wednesday', 'calendar');
        $calendar[4] = new lang_string('thursday', 'calendar');
        $calendar[5] = new lang_string('friday', 'calendar');
        $calendar[6] = new lang_string('saturday', 'calendar');

        echo "NEXT : " . $pluginInfo->nextstatus . "</br>";
        echo "LAST: " . $pluginInfo->nextstatus . "</br>";
        echo "Day: " . $calendar[$pluginInfo->fs_calendar_status] . "</br>";

        // Local time
        $time   = time();
        $today  = getdate($time);
        $add    = 0;

        switch ($today['weekday']) {
            case 'Monday':
                echo "Today is monday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $diff = 7;

                        break;
                    case 'Tuesday':
                        $diff = 6;

                        break;
                    case 'Wednesday':
                        $diff = 5;

                        break;
                    case 'Thursday':
                        $diff = 4;

                        break;
                    case 'Friday':
                        $diff = 3;

                        break;
                    case 'Saturday':
                        $diff = 2;

                        break;
                    case 'Sunday':
                        $diff = 1;

                        break;
                }//switch_calendar

                break;
            case 'Tuesday':
                echo "Today is tuesday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $diff = 1;

                        break;
                    case 'Tuesday':
                        $diff = 7;

                        break;
                    case 'Wednesday':
                        $diff = 6;

                        break;
                    case 'Thursday':
                        $diff = 5;

                        break;
                    case 'Friday':
                        $diff = 4;

                        break;
                    case 'Saturday':
                        $diff = 3;

                        break;
                    case 'Sunday':
                        $diff = 2;

                        break;
                }//switch_calendar

                break;
            case 'Wednesday':
                echo "Today is wednesday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $add = 5;

                        break;
                    case 'Tuesday':
                        $diff = 1;

                        break;
                    case 'Wednesday':
                        $add = 7;

                        break;
                    case 'Thursday':
                        $diff = 6;

                        break;
                    case 'Friday':
                        $diff = 5;

                        break;
                    case 'Saturday':
                        $diff = 4;

                        break;
                    case 'Sunday':
                        $diff = 3;

                        break;
                }//switch_calendar

                break;
            case 'Thursday':
                echo "Today is thursday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $diff = 3;

                        break;
                    case 'Tuesday':
                        $diff = 2;

                        break;
                    case 'Wednesday':
                        $diff = 1;

                        break;
                    case 'Thursday':
                        $diff = 7;

                        break;
                    case 'Friday':
                        $diff = 6;

                        break;
                    case 'Saturday':
                        $diff = 5;

                        break;
                    case 'Sunday':
                        $diff = 4;

                        break;
                }//switch_calendar

                break;
            case 'Friday':
                echo "Today is friday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $diff = 4;

                        break;
                    case 'Tuesday':
                        $diff = 3;

                        break;
                    case 'Wednesday':
                        $diff = 2;

                        break;
                    case 'Thursday':
                        $diff = 1;

                        break;
                    case 'Friday':
                        $diff = 7;

                        break;
                    case 'Saturday':
                        $diff = 6;

                        break;
                    case 'Sunday':
                        $diff = 5;

                        break;
                }//switch_calendar

                break;
            case 'Saturday':
                echo "Today is saturday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $diff = 5;

                        break;
                    case 'Tuesday':
                        $diff = 4;

                        break;
                    case 'Wednesday':
                        $diff = 3;

                        break;
                    case 'Thursday':
                        $diff = 2;

                        break;
                    case 'Friday':
                        $diff = 1;

                        break;
                    case 'Saturday':
                        $diff = 7;

                        break;
                    case 'Sunday':
                        $diff = 6;

                        break;
                }//switch_calendar

                break;
            case 'Sunday':
                echo "Today is sunday" . "</br>";
                switch ($calendar[$pluginInfo->fs_calendar_status]) {
                    case 'Monday':
                        $diff = 6;

                        break;
                    case 'Tuesday':
                        $diff = 5;

                        break;
                    case 'Wednesday':
                        $diff = 4;

                        break;
                    case 'Thursday':
                        $diff = 3;

                        break;
                    case 'Friday':
                        $diff = 2;

                        break;
                    case 'Saturday':
                        $diff = 1;

                        break;
                    case 'Sunday':
                        $diff = 7;

                        break;
                }//switch_calendar

                break;
        }//switch_today

        echo "DAYS TO ADD : " . $add . "</br>";
        $new = time() + ($add*24*60*60);
        $newdata = getdate($new);
        echo "New execution : " . $new . " - " . $newdata['weekday'] . " - " . $newdata['mday'] . "/" . $newdata['mon'] . "/" . $newdata['year'] . "<br>";
        //FELLESDATA_CRON::cron_test($pluginInfo,false);

        $new = mktime(0, 0, 0, $newdata['mon'], $newdata['mday'], $newdata['year']);

        echo "NEW NEW : " . $new . "</br>";
        
        //echo $pluginInfo->fs_days;
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();
