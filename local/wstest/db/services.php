<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 29/09/15
 * Time: 10:22
 * To change this template use File | Settings | File Templates.
 */

$className = 'local_wstest_external';
$classPath = 'local/wstest/externallib.php';


$functionName   = 'HelloWorld';
$functionConfig = array('classname'     =>    $className,
    'methodname'    =>    $functionName,
    'classpath'     =>    $classPath,
    'description'   =>    'TEst',
    'type'          =>    'write',
    'capabilities'  =>    ''
);

$functions = array($functionName    =>  $functionConfig);

/**
 * Services to install
 */
$service_name   = 'wstest';
$service_config = array(
    'functions'         => array($functionName),
    'restrictedusers'   => 1,
    'enabled'           => 1
);

$services = array('wstest' => $service_config);