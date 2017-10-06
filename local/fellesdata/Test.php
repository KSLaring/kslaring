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
            $SESSION->manual = true;
            FELLESDATA_CRON::cron_manual(true,$option);
        }
    }else {
        // Get parameters service
        $toDate     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
        $toDate     = gmdate('Y-m-d\TH:i:s\Z',$toDate);

        echo $toDate . "</br>";

        //echo " --> " . FS_CRON::can_run();

        $notifyTo   = explode(',',$pluginInfo->mail_notification);
        if ($notifyTo) {
            // Get companies to send notifications
            $toMail = FSKS_COMPANY::get_companiesfs_to_mail();

            if ($toMail) {
                // Subject
                $subject = (string)new lang_string('subject','local_fellesdata',$SITE->shortname,$USER->lang);

                // url mapping
                $urlMapping = new moodle_url('/local/fellesdata/mapping/mapping_org.php');

                $info = new stdClass();
                if ($toMail) {
                    $info->companies = implode('<br/>',$toMail);
                }else {
                    $info->companies = null;
                }//if_ToMail

                $urlMapping->param('m','co');
                $info->mapping  = $urlMapping;

                $body = (string)new lang_string('body_company_to_sync','local_fellesdata',$info,$USER->lang);

                // send
                foreach ($notifyTo as $to) {
                    $USER->email    = $to;
                    email_to_user($USER, $SITE->shortname, $subject, $body,$body);
                }//for_Each
            }else {
                echo " No notifications " . "</br>";
            }//if_toMail
        }//if_notify
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();
