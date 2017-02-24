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



/**
 * Services to install
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
 * SERVICES FELLESDATA
 */
/* Organization Structure   */
/* Company  */
$fonCompany         = 'wsFSCompany';
$fonCompanyConfig   = array(
                            'classname'     => $className,
                            'methodname'    => $fonCompany,
                            'classpath'     => $classPath,
                            'description'   => 'FELLESDATA Integration',
                            'type'          => 'write',
                            'capabilities'  => ''
                           );
/* Hierarchy    */
$fonHierarchy       = 'wsKSOrganizationStructure';
$fonHierarchyConfig = array(
                            'classname'     => $className,
                            'methodname'    => $fonHierarchy,
                            'classpath'     => $classPath,
                            'description'   => 'Fellesdata integration. Get hierarchy',
                            'type'          => 'read',
                            'capabilities'  => ''
                           );

/* JOB ROLES */
/* Job roles from fellesdata */
$fonFSJobRoles          = 'wsFSJobRoles';
$fonFSJobRolesConfig    = array(
                                'classname'     => $className,
                                'methodname'    => $fonFSJobRoles,
                                'classpath'     => $classPath,
                                'description'   => 'Fellesdata Integraton. JR from FS',
                                'type'          => 'write',
                                'capabilities'  => ''
                               );
/* Job Roles from KS */
$fonKSJobRoles          = 'wsKSJobRoles';
$fonKSJobRolesConfig    = array(
                                'classname'     => $className,
                                'methodname'    => $fonKSJobRoles,
                                'classpath'     => $classPath,
                                'description'   => 'Fellesdata Integration. JR from KS',
                                'type'          => 'read',
                                'capabilities'  => ''
                               );

/* Job Roles Generics   */
$fonKSJobRolesGenerics       = 'wsKSJobRolesGenerics';
$fonKSJobRolesGenericsConfig = array(
                                     'classname'    => $className,
                                     'methodname'   => $fonKSJobRolesGenerics,
                                     'classpath'    => $classPath,
                                     'description'  => 'Fellesdata Integration. JR generics from KS',
                                     'type'         => 'read',
                                     'capabilities' => ''
                                    );


/* User Manager Reporter        */
$fonManagerReporter             = 'wsManagerReporter';
$fonManagerReporterConfig       = array(
                                        'classname'     => $className,
                                        'methodname'    => $fonManagerReporter,
                                        'classpath'     => $classPath,
                                        'description'   => 'Fellesdata Integration. Manager Reporter',
                                        'type'          => 'write',
                                        'capabilities'  => ''
);

/* User Competence Profile  */
$fonUserCompetence             = 'wsUserCompetence';
$fonUserCompetenceConfig       = array(
                                       'classname'     => $className,
                                       'methodname'    => $fonUserCompetence,
                                       'classpath'     => $classPath,
                                       'description'   => 'Fellesdata Integration. User Competence',
                                       'type'          => 'write',
                                       'capabilities'  => ''
);


/* USER */
/* Users Accounts */
$fonUsersAccounts       = 'wsUsersAccounts';
$fonUsersAccountsConfig = array(
                                'classname'     => $className,
                                'methodname'    => $fonUsersAccounts,
                                'classpath'     => $classPath,
                                'description'   => 'Fellesdata Integration. Users accounts',
                                'type'          => 'write',
                                'capabilities'  => ''
                               );


/**
 * Unmap user competence
 */
$fonUnMapCompetence         = 'wsUnMapUserCompetence';
$fonUnMapCompetenceConfig   = array(
                                    'classname'     => $className,
                                    'methodname'    => $fonUnMapCompetence,
                                    'classpath'     => $classPath,
                                    'description'   => 'Fellesdata Integration. Unmap user competence',
                                    'type'          => 'write',
                                    'capabilities'  => ''
                                   );

/**
 * Unmap organizations FS-KS
 */
$fonUnMapOrganizations          = 'wsUnMapCompany';
$fonUnMapOrganizationsConfig    = array(
                                        'classname'     => $className,
                                        'methodname'    => $fonUnMapOrganizations,
                                        'classpath'     => $classPath,
                                        'description'   => 'Fellesdata Integration. Unmap companies',
                                        'type'          => 'write',
                                        'capabilities'  => ''
                                       );

/**
 * Get competence
 */
$foncompetencesync          = 'ws_get_competence';
$foncompetencesyncconfig    = array(
                                'classname'     => $className,
                                'methodname'    => $foncompetencesync,
                                'classpath'     => $classPath,
                                'description'   => 'Get competence.',
                                'type'          => 'read',
                                'capabilities'  => ''
                            );

/**
 * Get managers/reporters
 */


/**
 * Functions to install
 */
$functions = array(
                    $functionName           => $functionConfig,
                    $fonCompany 			=> $fonCompanyConfig,
                    $fonHierarchy			=> $fonHierarchyConfig,
                    $fonFSJobRoles			=> $fonFSJobRolesConfig,
                    $fonKSJobRoles	        => $fonKSJobRolesConfig,
                    $fonKSJobRolesGenerics	=> $fonKSJobRolesGenericsConfig,
                    $fonManagerReporter     => $fonManagerReporterConfig,
                    $fonUserCompetence      => $fonUserCompetenceConfig,
                    $fonUsersAccounts		=> $fonUsersAccountsConfig,
                    $fonUnMapCompetence     => $fonUnMapCompetenceConfig,
                    $fonUnMapOrganizations  => $fonUnMapOrganizationsConfig,
                    $foncompetencesync      => $foncompetencesyncconfig
                    );

/**
 * ADFS Service
 */
$serviceADFS        = 'adfs';
$serviceADFSConfig  = array(
                            'functions'         => array($functionName),
                            'restrictedusers'   => 1,
                            'enabled'           => 1
                           );

/**
 * Fellesdata Service
 */
$serviceFS        = 'fellesdata';
$serviceFSConfig  = array(
                          'functions'         => array($fonCompany,$fonHierarchy,$fonFSJobRoles,$fonKSJobRoles,$fonKSJobRolesGenerics,
                                                       $fonManagerReporter,$fonUserCompetence,$fonUsersAccounts,
                                                       $fonUnMapCompetence,$fonUnMapOrganizations,$foncompetencesync),
                          'restrictedusers'   => 1,
                          'enabled'           => 1
);

/* Services */
$services = array(
                  $serviceADFS  => $serviceADFSConfig,
                  $serviceFS    => $serviceFSConfig
                 );