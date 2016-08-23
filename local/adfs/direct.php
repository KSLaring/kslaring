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
    $params = array('directlink' => $_GET['directlink']);
    $url->params($params);
}

redirect($url);