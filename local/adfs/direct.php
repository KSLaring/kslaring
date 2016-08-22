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
if (isset($_GET['directlink'])) {
    $SESSION->directlink = $_GET['directlink'];
    $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START DIRECT APGE. ' . "\n";

    $dbLog .= ' 111 ' . '\n';
    $dbLog .= 'DIRECTLINK --> ' . $SESSION->directlink . ' - ' . $_GET['directlink'] . '\n\n';
    error_log($dbLog, 3, $CFG->dataroot . "/Testing PAQUI.log");
}

redirect($url);