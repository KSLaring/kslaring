<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 05/04/2017
 * Time: 10:51
 */
require('../../config.php');
require_once('lib.php');

$courseid   = required_param('id',PARAM_INT);

global $PAGE,$SITE,$OUTPUT,$DB;
require_login();

$contextCourse      = context_course::instance($courseid);
$url                = new moodle_url('/enrol/waitinglist/test.php',array('id' => $courseid));
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

$sql = " SELECT	cf.id,
                cf.value
         FROM	{course_format_options}	cf
         WHERE	cf.courseid = $courseid
            AND cf.name like '%time%' ";

echo $OUTPUT->header();

$rdo = $DB->get_record_sql($sql);
if ($rdo) {
    $script_tz = date_default_timezone_get();
    echo "TIME ZONE --> " . $script_tz . "</br>";

    $timeslst = explode(',',$rdo->value);
    foreach ($timeslst as $time) {
        echo "TIME : " . $time . "</br>";

        // Extract date and time
        $time  = str_replace('kl','#',str_replace('kl.','#',$time));
        $index = strrpos($time,'#');
        if ($index) {
            // Extract date
            $date = substr($time,0,$index);
            echo "DATE --> " . $date . "</br>";
            echo "TIMESTAMP DATE --> " . strtotime($date) . "</br>";
            // Extract time
            $time = substr($time,$index+1);
            echo "TIME --> " . $time . "</br>";
            $index = strrpos($time,'-');
            if ($index) {
                $from = substr($time,0,$index);
                $to = substr($time,$index+1);

                echo "TIME FROM --> " . $from . "</br>";
                echo "TIME TO   --> " . $to . "</br>";
            }
        }//inf_index

        // time start
        $start = strtotime($date . ' ' . $from);
        // time end
        $end = strtotime($date . ' ' . $to);

        echo " START : " . $start . "</br>";
        echo " END: " . $end . "</br>";

        echo "</br>-----</br></br>";
    }
}

echo $OUTPUT->footer();