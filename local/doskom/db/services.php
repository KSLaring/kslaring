<?php

/**
 * Single Sign On  Web Service
 *
 * @package         local
 * @subpackage      doskom
 * @copyright       2015 eFaktor    {@link https://www.efaktor.no}
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * This file defines all functions and services of the WS
 */

$class_name_sso = 'local_doskom_external';
$class_path_sso = 'local/doskom/externallib.php';

/**
 * Functions
 */

/* Single Sing On   */
$function_login_SSO_name   = 'wsLogInUser';
$function_login_SSO_config = array(
                                    'classname'    =>    $class_name_sso,
                                    'methodname'   =>    $function_login_SSO_name,
                                    'classpath'    =>    $class_path_sso,
                                    'description'  =>    'SSO Moodle',
                                    'type'         =>    'write'
                                  );

/* De-activate User */
$function_deactivate_name   = 'wsDeActivateUser';
$function_deactivate_config = array(
                                    'classname'     => $class_name_sso,
                                    'methodname'    => $function_deactivate_name,
                                    'classpath'     => $class_path_sso,
                                    'description'   => 'Deactivate a specific user',
                                    'type'          => 'write'
                                   );

/* Get Course Catalog   */
$function_catalog_name      = "wsGetCourseCatalog";
$function_catalog_config    = array(
                                    'classname'     =>  $class_name_sso,
                                    'methodname'    =>  $function_catalog_name,
                                    'classpath'     =>  $class_path_sso,
                                    'description'   =>  'Return Course Catalog',
                                    'type'          =>  'read'
                                   );

/* Get Completion Courses -- Historical */
$function_completion_courses_name   = 'wsGetAccomplishedCourses';
$function_completion_courses_config = array(
                                            'classname'     =>  $class_name_sso,
                                            'methodname'    =>  $function_completion_courses_name,
                                            'classpath'     =>  $class_path_sso,
                                            'description'   =>  'Return Completion Courses List - Historical',
                                            'type'          =>  'read'
                                           );

/**
 * Web Service Functions to install
 */
$functions = array(
                    $function_login_SSO_name             =>  $function_login_SSO_config,
                    $function_deactivate_name            =>  $function_deactivate_config,
                    $function_catalog_name               =>  $function_catalog_config,
                    $function_completion_courses_name    =>  $function_completion_courses_config
                  );

/**
 * Services to install
 */
$service_name   = 'doskom';
$service_config = array(
                        'functions'         => array($function_login_SSO_name,$function_deactivate_name,$function_catalog_name,$function_completion_courses_name),
                        'restrictedusers'   => 1,
                        'enabled'           => 1
                       );

$services = array('doskom' => $service_config);