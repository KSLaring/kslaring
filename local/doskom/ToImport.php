<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 05/02/15
 * Time: 14:31
 * To change this template use File | Settings | File Templates.
 */
require( '../../config.php' );
require_once('cron/wsssocron.php');
require_once('wsDOSKOMlib.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/doskom/ToImport.php');

/* Print Header */
echo $OUTPUT->header();

try {
    //wsdoskom_cron::cron();
    /**
    $criteria = array();
    $criteria['companyId']  = '4515801';
    $criteria['dateFrom']   = '2010.01.01';
    $criteria['dateTo']     = '2017.01.05';

    $result     = array();
    $result['error']        = 200;
    $result['msg_error']    = '';
    $result['courses']      = array();

    $result['courses'] = WS_DOSKOM::getHistoricalCoursesCompletion($criteria,$result);

    if ($result) {
        if ($result['courses']) {
            $courses = $result['courses'];

            foreach ($courses as $course) {
                echo "Course: " . $course['courseId'] . " - " . $course['courseName'] . "</br>";
                echo "USERS: " . "</br>";
                if ($course['users']) {
                    foreach ($course['users'] as $user) {
                        echo $user->userId . " - " . $user->completionDate . "</br>";
                    }
                }
            }

        }
    }else {
        echo "HOLA";
    }
    **/
}catch (Exception $ex) {
    throw $ex;
}

/* Print Footer */
echo $OUTPUT->footer();