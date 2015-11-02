<?php
/**
 * Kommit ADFS Integration WebService
 *
 * @package         local
 * @subpackage      wsks
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    30/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * This file defines all functions and services of the WS
 */

$className = 'local_wsks_external';
$classPath = 'local/wsks/externallib.php';

/**
 * Functions
 */

/* User From ADFS   */
/* Create/Update    */
$functionName   = 'wsUserADFS';
$functionConfig = array('classname'     =>    $className,
                        'methodname'    =>    $functionName,
                        'classpath'     =>    $classPath,
                        'description'   =>    'ADFS Integration',
                        'type'          =>    'write',
                        'capabilities'  =>    ''
                       );

/**
 * Web Service Functions to install
 */
$functions = array($functionName    =>  $functionConfig);

/**
 * Services to install
 */
$service_name   = 'adfs';
$service_config = array(
                        'functions'         => array($functionName),
                        'restrictedusers'   => 1,
                        'enabled'           => 1
                       );

$services = array('adfs' => $service_config);