<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 22/08/16
 * Time: 14:26
 */

require( '../../config.php' );

/* PARAMS       */
/* Log In URL   */
$url        = new moodle_url('/auth/saml/index.php');


/**
 * @updateDate  15/08/2016
 * @author      eFaktor     (fbv)
 *
 * Description
 * Activity/Course link
 */
global $SESSION,$CFG;

$dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' DIRECT COURSE LINK ' . "\n";
error_log($dbLog, 3, $CFG->dataroot . "/COURSE_LNK.log");

if (isset($_GET['directlink'])) {
    $SESSION->directlink = $_GET['directlink'];
    $params = array('directlink' => $_GET['directlink']);

    $dbLog = ' directlink: ' . $SESSION->directlink .   "\n";
    error_log($dbLog, 3, $CFG->dataroot . "/COURSE_LNK.log");
    
    $url->params($params);
}

redirect($url);