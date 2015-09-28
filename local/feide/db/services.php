<?php
/**
 * Feide Integration WebService
 *
 * @package         local
 * @subpackage      feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    21/09/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * This file defines all functions and services of the WS
 */

$className = 'local_feide_external';
$classPath = 'local/feide/externallib.php';

/**
 * Functions
 */

/* Validate User From Feide */
$functionName   = 'wsValidateUserFeide';
$functionConfig = array('classname'    =>    $className,
                        'methodname'   =>    $functionName,
                        'classpath'    =>    $classPath,
                        'description'  =>    'Feide Integration',
                        'type'         =>    'read'
);


/**
 * Web Service Functions to install
 */
$functions = array($functionName    =>  $functionConfig);

/**
 * Services to install
 */
$service_name   = 'feide';
$service_config = array(
    'functions'         => array($functionName),
    'restrictedusers'   => 0,
    'enabled'           => 1
);

$services = array('feide' => $service_config);