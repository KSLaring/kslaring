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

try {
    WSDOSKOM_Cron::cron();
}catch (Exception $ex) {
    throw $ex;
}