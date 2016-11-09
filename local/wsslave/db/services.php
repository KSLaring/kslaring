<?php
/**
 * Slaves Integration - Web Services
 *
 * @package         local
 * @subpackage      wsslave
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * This file defines all functions and services of the WS
 */

$className = 'local_wsslave_external';
$classPath = 'local/wsslave/externallib.php';

/**
 * Services to install
 */
$functionName   = 'wsUpdateMainService';
$functionConfig = array('classname'     =>    $className,
                        'methodname'    =>    $functionName,
                        'classpath'     =>    $classPath,
                        'description'   =>    'Update Service from the Main system',
                        'type'          =>    'write',
                        'capabilities'  =>    ''
                    );

/**
 * Functions to install
 */
$functions = array($functionName => $functionConfig);

/**
 * SLAVE Service
 */
$serviceSLAVE        = 'slave';
$serviceSLAVEConfig  = array(
                            'functions'         => array($functionName),
                            'restrictedusers'   => 1,
                            'enabled'           => 1
                           );

/* Services */
$services = array($serviceSLAVE  => $serviceSLAVEConfig);