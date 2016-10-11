<?php
/**
 * Kommit ADFS Integration WebService - External Lib
 *
 * @package         local
 * @subpackage      wsks
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    30/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Implements all functions that the Web Service supply
 * We have to implement the next structure for each function:
 * - function xxxx
 * - function xxxx_parameters
 * - function xxxx_return
 */

require_once('../../config.php');
require_once ($CFG->libdir.'/externallib.php');
require_once ('wsadfslib.php');
require_once ('fellesdata/wsfellesdatalib.php');

class local_wsks_external extends external_api {

    /********************/
    /* wsUserADFS */
    /********************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Parameters that ws has to get to create or update the user
     * 
     * @updateDate      15/08/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Add course/activity link.
     */
    public static function wsUserADFS_parameters() {
        /* User Info    */
        $userName   = new external_value(PARAM_TEXT,'username. Personal number');
        $firstName  = new external_value(PARAM_TEXT,'First name');
        $lastName   = new external_value(PARAM_TEXT,'Last name');
        $eMail      = new external_value(PARAM_TEXT,'eMail');
        $city       = new external_value(PARAM_TEXT,'city');
        $country    = new external_value(PARAM_TEXT,'country');
        $language   = new external_value(PARAM_TEXT,'language');
        $modlnk     = new external_value(PARAM_TEXT,'Direct link. Course or activity link');
        $modid      = new external_value(PARAM_TEXT,'Course Id. Activity Id');

        /* USER ADFS */
        $userADFS = new external_single_structure(array('username'  => $userName,
                                                        'firstname' => $firstName,
                                                        'lastname'  => $lastName,
                                                        'email'     => $eMail,
                                                        'city'      => $city,
                                                        'country'   => $country,
                                                        'lang'      => $language,
                                                        'modlnk'    => $modlnk,
                                                        'modid'     => $modid));

        return new external_function_parameters(array('user'=> $userADFS));
    }//wsUserADFS_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * The response from the service
     */
    public static function wsUserADFS_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');
        $url        = new external_value(PARAM_TEXT,'Where the user must be redirected');
        $valid      = new external_value(PARAM_INT,'User Created/updated or not');


        $exist_return = new external_single_structure(array('error'         => $error,
                                                            'msg_error'     => $msg_error,
                                                            'valid'         => $valid,
                                                            'url'           => $url));

        return $exist_return;
    }//wsUserADFS_returns

    /**
     * @param           $userADFS
     *
     * @return          array
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create/update the user from ADFS
     */
    public static function wsUserADFS($userADFS) {
        /* Variables    */
        global $CFG;
        $result     = array();
        $userId     = null;

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsUserADFS_parameters(), array('user' => $userADFS));

        /* Execute  */
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['valid']        = 1;
        $result['url']          = '';
        try {
            /* Library  */
            //require_once('../../user/profile/field/gender/lib/genderlib.php');
            
            /* Create or Update User ADFS   */
            $result['url'] = WS_ADFS::Process_UserADFS($userADFS,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error']        == '200') {
                $result['error']        = 500;
                $result['valid']        = 0;
                $result['msg_error']    = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            $result['url']          = urlencode($CFG->wwwroot . '/local/wsks/error.php');

            return $result;
        }
    }//wsUserADFS


    /**********************/
    /* FELLESDATA SERVICE */
    /**********************/

    /***************/
    /* wsFSCompany */
    /***************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Parameters of web service to create, updated or delete KS companies from FELLESDATA
     *
     * @updateDate      06/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add invoice data
     */
    public static function wsFSCompany_parameters() {
        /* Company Info */
        $companyFSID            = new external_value(PARAM_TEXT,'Fellesdata Id');
        $companyKSID            = new external_value(PARAM_INT,'Company KS ID. Update and Delete');
        $companyName            = new external_value(PARAM_TEXT,'Company Name');
        $companyIndustryCode    = new external_value(PARAM_TEXT,'Industry Code');
        $companyLevel           = new external_value(PARAM_INT,'Company Level');
        $companyParent          = new external_value(PARAM_INT,'Company Parent');
        $action                 = new external_value(PARAM_INT,'Action. Add/Update/Delete');
        /* Public /Private  */
        $companyPublic          = new external_value(PARAM_INT,'Private or Public');
        /* Invoice Data */
        $companyAnsvar          = new external_value(PARAM_TEXT,'Company Ansvar');
        $companyTjneste         = new external_value(PARAM_TEXT,'Company Tjeneste');
        $companyAdrOne          = new external_value(PARAM_TEXT,'Adresse 1');
        $companyAdrTwo          = new external_value(PARAM_TEXT,'Adresse 2');
        $companyAdrThree        = new external_value(PARAM_TEXT,'Adresse 3');
        $companyPostNr          = new external_value(PARAM_TEXT,'Post NR');
        $companyPostSted        = new external_value(PARAM_TEXT,'Post Sted');
        $companyEPost           = new external_value(PARAM_TEXT,'ePost');

        $companiesFS = new external_single_structure(array( 'fsId'          => $companyFSID,
                                                            'ksId'          => $companyKSID,
                                                            'name'          => $companyName,
                                                            'industry'      => $companyIndustryCode,
                                                            'level'         => $companyLevel,
                                                            'parent'        => $companyParent,
                                                            'public'        => $companyPublic,
                                                            'ansvar'        => $companyAnsvar,
                                                            'tjeneste'      => $companyTjneste,
                                                            'adresseOne'    => $companyAdrOne,
                                                            'adresseTwo'    => $companyAdrTwo,
                                                            'adresseThree'  => $companyAdrThree,
                                                            'postnr'        => $companyPostNr,
                                                            'poststed'      => $companyPostSted,
                                                            'epost'         => $companyEPost,
                                                            'action'        => $action));

        return new external_function_parameters(array('companiesFS'=> new external_multiple_structure($companiesFS)));
    }//wsFSCompany_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Response from the service. To know which companies have been synchronized.
     */
    public static function wsFSCompany_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Companies */
        $companyFSID    = new external_value(PARAM_TEXT,'Fellesdata Id');
        $companyKSID    = new external_value(PARAM_INT,'KS Company Id');
        $imported       = new external_value(PARAM_INT,'Imported');
        $key            = new external_value(PARAM_INT,'Key Id record imported');

        $companiesInfo  = new external_single_structure(array('fsId'        => $companyFSID,
                                                              'ksId'        => $companyKSID,
                                                              'imported'    => $imported,
                                                              'key'         => $key));

        $existReturn = new external_single_structure(array('error'         => $error,
                                                           'message'       => $msgError,
                                                           'companies'     => new external_multiple_structure($companiesInfo)));

        return $existReturn;
    }//wsFSCompany_returns

    /**
     * @param           $companiesFS
     *
     * @return          array
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize KS Companies and FS Companies.
     */
    public static function wsFSCompany($companiesFS) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsFSCompany_parameters(), array('companiesFS' => $companiesFS));

        /* Web Service Response */
        $result['error']        = '200';
        $result['message']      = '';
        $result['companies']    = array();

        try {
            /* Synchronize companies */
            WS_FELLESDATA::Synchronize_FSKS_Companies($companiesFS,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsFSCompany

    /*****************************/
    /* wsKSOrganizationStructure */
    /*****************************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to get all organization structure from KS Læring for a specific level.
     */
    public static function wsKSOrganizationStructure_parameters() {
        /* Top Company   */
        $topCompany  = new external_value(PARAM_TEXT,'Top Company');
        $topLevel    = new external_value(PARAM_INT,'Top Level');
        $notIn       = new external_value(PARAM_TEXT,'Not In');

        $info = new external_single_structure(array('company'   => $topCompany,
                                                    'level'     => $topLevel,
                                                    'notIn'     => $notIn));

        return new external_function_parameters(array('topCompany'  => $info));
    }//wsKSOrganizationStructure_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Response of the web service. Organization structure for a given level.
     */
    public static function wsKSOrganizationStructure_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Organization Info   */
        $orgName            = new external_value(PARAM_TEXT,'Level Name');
        $orgId              = new external_value(PARAM_INT,'Level Id');
        $orgHierarchy       = new external_value(PARAM_INT,'Level Hierarchy');
        $orgIndustryCode    = new external_value(PARAM_TEXT,'Industry Code');
        $orgParent          = new external_value(PARAM_INT,'Parent');

        $orgInfo      = new external_single_structure(array('id'            => $orgId,
                                                            'name'          => $orgName,
                                                            'industrycode'  => $orgIndustryCode,
                                                            'level'         => $orgHierarchy,
                                                            'parent'        => $orgParent));

        $existReturn = new external_single_structure(array('error'         => $error,
                                                           'message'       => $msgError,
                                                           'structure'     => new external_multiple_structure($orgInfo)));

        return $existReturn;
    }//wsKSOrganizationStructure_returns


    /**
     * @param           $topCompany
     *
     * @return          array
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get organization structure for a specific level.
     */
    public static function wsKSOrganizationStructure($topCompany) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsKSOrganizationStructure_parameters(), array('topCompany' => $topCompany));

        /* Web Service response */
        $result['error']        = 200;
        $result['message']      = '';
        $result['structure']    = array();

        try {
            /* Get Organization Structure */
            WS_FELLESDATA::OrganizationStructureByTop($topCompany,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsKSOrganizationStructure

    /****************/
    /* wsFSJobRoles */
    /****************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize the job roles between KS Læring and FELLESDATA
     */
    public static function wsFSJobRoles_parameters() {
        /* job Role Info */
        $jobRoleFSID        = new external_value(PARAM_TEXT,'Fellesdata Job role Id');
        $jobRoleKSID        = new external_value(PARAM_TEXT,'KS Job role Id. Update and delete');
        $jobRoleName        = new external_value(PARAM_TEXT,'Job Role name');
        $jobRoleIndustry    = new external_value(PARAM_TEXT,'Industry code');
        $jrLevelZero        = new external_value(PARAM_INT,'Level Zero');
        $jrLevelOne         = new external_value(PARAM_INT,'Level One');
        $jrLevelTwo         = new external_value(PARAM_INT,'Level Two');
        $jrLevelThree       = new external_value(PARAM_INT,'Level Three');
        $action             = new external_value(PARAM_INT,'Action. Add/Update/delete');

        $relationInfo = new external_single_structure(array('levelZero'     => $jrLevelZero,
                                                            'levelOne'      => $jrLevelOne,
                                                            'levelTwo'      => $jrLevelTwo,
                                                            'levelThree'    => $jrLevelThree
                                                           ));

        $jobRolesFS = new external_single_structure(array('fsId'        => $jobRoleFSID,
                                                          'ksId'        => $jobRoleKSID,
                                                          'name'        => $jobRoleName,
                                                          'industry'    => $jobRoleIndustry,
                                                          'relation'    => new external_multiple_structure($relationInfo),
                                                          'action'      => $action));

        return new external_function_parameters(array('jobRoles'=> new external_multiple_structure($jobRolesFS)));
    }//wsFSJobRoles_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Response of the web service. Job roles have been imported
     */
    public static function wsFSJobRoles_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Job Roles */
        $jobRolesFSID   = new external_value(PARAM_TEXT,'FS Job Role Id');
        $jobRolesKSID   = new external_value(PARAM_INT,'KS Job Role Id');
        $imported       = new external_value(PARAM_INT,'Imported');
        $key            = new external_value(PARAM_INT,'Key ID record imported');

        $jobRolesInfo  = new external_single_structure(array('fsId'     => $jobRolesFSID,
                                                             'ksId'     => $jobRolesKSID,
                                                             'imported' => $imported,
                                                             'key'      => $key));

        $existReturn = new external_single_structure(array('error'        => $error,
                                                           'message'      => $msgError,
                                                           'jobRoles'     => new external_multiple_structure($jobRolesInfo)));

        return $existReturn;
    }//wsFSJobRoles_returns

    /**
     * @param           $jobRolesFS
     *
     * @return          array
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * To synchronize job roles between KS Læring and FELLESDATA
     */
    public static function wsFSJobRoles($jobRolesFS) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsFSJobRoles_parameters(), array('jobRoles' => $jobRolesFS));

        /* Web Service Response */
        $result['error']    = 200;
        $result['message']  = '';
        $result['jobRoles'] = array();

        try {
            /* Synchronize Job Roles */
            WS_FELLESDATA::Synchronize_FSKS_JobRoles($jobRolesFS,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsFSJobRoles

    /****************/
    /* wsKSJobRoles */
    /****************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Parameters - Web service to get all job roles from KS Læring that belong to a specific level zero
     */
    public static function wsKSJobRoles_parameters() {
        /* Level   */
        $level  = new external_value(PARAM_INT,'Top Level to Import');
        $notIn  = new external_value(PARAM_TEXT,'Not Int');

        $levelTop = new external_single_structure(array('top'  => $level,
                                                        'notIn' => $notIn));

        return new external_function_parameters(array('hierarchy'=> $levelTop));
    }//wsKSJobRoles_parameters

    public static function wsKSJobRoles_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Job Role Info   */
        $jobRoleID      = new external_value(PARAM_INT,'Job Role Id');
        $industryCode   = new external_value(PARAM_TEXT,'Industry Code');
        $jobRoleName    = new external_value(PARAM_TEXT,'Job Role Name');
        $jrLevelZero    = new external_value(PARAM_INT,'Job Role Level Zero');
        $jrLevelOne     = new external_value(PARAM_INT,'Job Role Level One');
        $jrLevelTwo     = new external_value(PARAM_INT,'Job Role Level Two');
        $jrLevelThree   = new external_value(PARAM_INT,'Job Role Level Three');


        $relationInfo = new external_single_structure(array('levelZero'     => $jrLevelZero,
                                                            'levelOne'      => $jrLevelOne,
                                                            'levelTwo'      => $jrLevelTwo,
                                                            'levelThree'    => $jrLevelThree
                                                           ));

        $jobRoleInfo    = new external_single_structure(array('id'              => $jobRoleID,
                                                              'name'            => $jobRoleName,
                                                              'industryCode'    => $industryCode,
                                                              'relation'        => new external_multiple_structure($relationInfo)
                                                              ));

        $existReturn = new external_single_structure(array('error'      => $error,
                                                           'message'    => $msgError,
                                                           'jobroles'   => new external_multiple_structure($jobRoleInfo)));

        return $existReturn;
    }//wsKSJobRoles_returns

    /**
     * @param           $hierarchy
     *
     * @return          array
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * To get all job roles from KS Læring that belong to a specific level zero
     */
    public static function wsKSJobRoles($hierarchy) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsKSJobRoles_parameters(), array('hierarchy' => $hierarchy));

        /* Web service response */
        $result['error']        = 200;
        $result['message']      = '';
        $result['jobroles']     = array();

        try {
            /* Get Job Roles connected with a level */
            WS_FELLESDATA::JobRolesByLevel($hierarchy,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsKSJobRoles

    /************************/
    /* wsKSJobRolesGenerics */
    /************************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Parameters - Web service to get all generics job roles from KS Læring
     */
    public static function wsKSJobRolesGenerics_parameters() {
        $notIn = new external_value(PARAM_TEXT,'Not IN');

        return new external_function_parameters(array('notIn'=> $notIn));
    }//wsKSJobRolesGenerics_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service response. Generics job roles
     */
    public static function wsKSJobRolesGenerics_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Job Role Info   */
        $jobRoleID      = new external_value(PARAM_INT,'Job Role Id');
        $jobRoleName    = new external_value(PARAM_TEXT,'Job Role Name');
        $industryCode   = new external_value(PARAM_TEXT,'Industry Code');

        $jobRoleInfo    = new external_single_structure(array('id'              => $jobRoleID,
                                                              'name'            => $jobRoleName,
                                                              'industryCode'    => $industryCode));

        $existReturn = new external_single_structure(array('error'      => $error,
                                                           'message'    => $msgError,
                                                           'jobroles'   => new external_multiple_structure($jobRoleInfo)));

        return $existReturn;
    }//wsKSJobRolesGenerics_returns

    /**
     * @param           $notIn
     *
     * @return          array
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to get all generics job roles from KS Læring
     */
    public static function wsKSJobRolesGenerics($notIn) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsKSJobRolesGenerics_parameters(), array('notIn' => $notIn));

        /* Web Service response */
        $result['error']    = 200;
        $result['message']  = '';
        $result['jobroles'] = array();

        try {
            /* Get Job Roles generics */
            WS_FELLESDATA::GenericsJobRoles($notIn,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $result['message']. ' ' . $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsKSJobRolesGenerics

    /**********************/
    /* wsManagerReporter */
    /*********************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize managers reporters between FS and KS - Parameters
     */
    public static function wsManagerReporter_parameters() {
        /* Manager Reporter Info */
        $personalNumber = new external_value(PARAM_TEXT,'Personal Number');
        $fsId           = new external_value(PARAM_INT,'FS Company Id');
        $companyID      = new external_value(PARAM_INT,'Company Id KS');
        $level          = new external_value(PARAM_INT,'Level');
        $priority       = new external_value(PARAM_INT,'prioritet');
        $action         = new external_value(PARAM_INT,'Action. Add/Update/Delete');

        /* Manager Reporter */
        $userManagerReporter = new external_single_structure(array('personalNumber'  => $personalNumber,
                                                                   'fsId'            => $fsId,
                                                                   'ksId'            => $companyID,
                                                                   'level'           => $level,
                                                                   'prioritet'         => $priority,
                                                                   'action'          => $action));

        return new external_function_parameters(array('managerReporter'=> new external_multiple_structure($userManagerReporter)));
    }//wsManagerReporter_parameters

    /**
     * @return              external_single_structure
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize managers reporters between FS and KS - Returns
     */
    public static function wsManagerReporter_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Manager Reporter Info */
        $personalNumber = new external_value(PARAM_TEXT,'Personal Number');
        $imported       = new external_value(PARAM_INT,'True/False');
        $key            = new external_value(PARAM_INT,'Key Id record imported');

        /* Manager Reporter */
        $userManagerReporter = new external_single_structure(array('personalNumber'  => $personalNumber,
                                                                   'imported'        => $imported,
                                                                   'key'             => $key));

        $existReturn = new external_single_structure(array('error'              => $error,
                                                           'message'            => $msgError,
                                                           'managerReporter'    => new external_multiple_structure($userManagerReporter)));

        return $existReturn;
    }//wsManagerReporter_returns

    /**
     * @param           $userManagerReporter
     * @return          array
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize managers reporters between FS and KS
     */
    public static function wsManagerReporter($userManagerReporter) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsManagerReporter_parameters(), array('managerReporter' => $userManagerReporter));

        /* Web Service response */
        $result['error']            = 200;
        $result['message']          = '';
        $result['managerReporter']  = array();

        try {
            /* Synchronize Managers Reporters */
            WS_FELLESDATA::Synchronize_UserManagerReporter($userManagerReporter,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsManagerReporter

    /********************/
    /* wsUserCompetence */
    /********************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize user competence between FS and KS - Parameters
     */
    public static function wsUserCompetence_parameters() {
        /* User Competence Info */
        $personalNumber = new external_value(PARAM_TEXT,'Personal Number');
        $jobRole        = new external_value(PARAM_TEXT,'Job Roles Id');
        $fsjobRoles     = new external_value(PARAM_TEXT,'FS job roles');
        $companyID      = new external_value(PARAM_INT,'Company Id');
        $fsId           = new external_value(PARAM_INT,'FS Company Id');
        $level          = new external_value(PARAM_INT,'Level');
        $impKeys        = new external_value(PARAM_TEXT,'KEYS IMP FS JR');
        $action         = new external_value(PARAM_INT,'Action. Add/Update/Delete');

        $userCompetence = new external_single_structure(array('personalNumber'  => $personalNumber,
                                                              'jobrole'         => $jobRole,
                                                              'fsjobroles'      => $fsjobRoles,
                                                              'company'         => $companyID,
                                                              'fsId'            => $fsId,
                                                              'level'           => $level,
                                                              'impkeys'         => $impKeys,
                                                              'action'          => $action));

        return new external_function_parameters(array('usersCompetence'=> new external_multiple_structure($userCompetence)));
    }//wsUserCompetence_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize user competence between FS and KS - Returns
     */
    public static function wsUserCompetence_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* User Competence Company Info */
        $personalNumber = new external_value(PARAM_TEXT,'Personal Number');
        $imported       = new external_value(PARAM_INT,'True/False');
        $key            = new external_value(PARAM_INT,'Key Id record imported');

        $userCompetence = new external_single_structure(array('personalNumber'  => $personalNumber,
                                                              'imported'        => $imported,
                                                              'key'             => $key));

        $existReturn = new external_single_structure(array('error'              => $error,
                                                           'message'            => $msgError,
                                                           'usersCompetence'    => new external_multiple_structure($userCompetence)));

        return $existReturn;
    }//wsUserCompetence_returns

    /**
     * @param           $usersCompetence
     * @return          array
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service to synchronize user competence between FS and KS
     */
    public static function wsUserCompetence($usersCompetence) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsUserCompetence_parameters(), array('usersCompetence' => $usersCompetence));

        /* Web Service Response */
        $result['error']            = 200;
        $result['message']          = '';
        $result['usersCompetence']  = array();

        try {
            /* Synchronization */
            WS_FELLESDATA::Synchronize_UserCompetence($usersCompetence,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsUserCompetenceJobRole

    /****************************/
    /* wsUsersAccounts          */
    /****************************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Parameters - Web service to synchronize the users accounts between KS Læring and FELLESDATA
     *
     * @updateDate      23/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add resource number
     */
    public static function wsUsersAccounts_parameters() {
        /* User account info */
        $personalNumber = new external_value(PARAM_TEXT,'Personal number');
        $firstName      = new external_value(PARAM_TEXT,'First Name');
        $lastName       = new external_value(PARAM_TEXT,'Last Name');
        $eMail          = new external_value(PARAM_TEXT,'eMail');
        $resourceNum    = new external_value(PARAM_TEXT,'resource number');
        $industryCode   = new external_value(PARAM_TEXT,'Industry code');
        $action         = new external_value(PARAM_INT,'Action. Add/Update/Delete');

        $accountInfo = new external_single_structure(array('personalnumber' => $personalNumber,
                                                           'firstname'      => $firstName,
                                                           'lastname'       => $lastName,
                                                           'email'          => $eMail,
                                                           'ressursnr'      => $resourceNum,
                                                           'industry'       => $industryCode,
                                                           'action'         => $action));

        return new external_function_parameters(array('usersAccounts'=> new external_multiple_structure($accountInfo)));
    }//wsUsersAccounts_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web service response. Users accounts imported
     */
    public static function wsUsersAccounts_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msgError   = new external_value(PARAM_TEXT,'Error Description');

        /* Account Info */
        $personalNumber = new external_value(PARAM_TEXT,'Personal Number');
        $imported       = new external_value(PARAM_INT,'True/False');
        $key            = new external_value(PARAM_INT,'Key Id record imported');

        $accountInfo = new external_single_structure(array('personalnumber'  => $personalNumber,
                                                           'imported'        => $imported,
                                                           'key'             => $key));

        $existReturn = new external_single_structure(array('error'          => $error,
                                                           'message'        => $msgError,
                                                           'usersAccounts'  => new external_multiple_structure($accountInfo)));

        return $existReturn;
    }//wsUsersAccounts_returns

    /**
     * @param           $usersAccounts
     *
     * @return          array
     *
     * @creationDate    26/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * To synchronize the users accounts between KS Læring and FELLESDATA
     *
     * @updateDate      05/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the gender
     */
    public static function wsUsersAccounts($usersAccounts) {
        /* Variables    */
        global $CFG;
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsUsersAccounts_parameters(), array('usersAccounts' => $usersAccounts));

        /* Web Service Response */
        $result['error']            = 200;
        $result['message']          = '';
        $result['usersAccounts']    = array();

        try {
            /* Library  */
            //require_once('../../user/profile/field/gender/lib/genderlib.php');
            
            /* Synchronization */
            WS_FELLESDATA::Synchronize_UsersAccounts($usersAccounts,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']    = 500;
                $result['message']  = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsUsersAccounts


    /*****************************/
    /*****************************/
    /*****************************/

    /**
     * @static
     * @param           external_description $description
     * @param           mixed $params
     * @return          array|bool|mixed|null
     * @throws          moodle_exception
     * @throws          invalid_parameter_exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate parameters received
     */
    public static function validate_parameters(external_description $description, $params) {
        if ($description instanceof external_value) {
            if (is_array($params) or is_object($params)) {
                throw new invalid_parameter_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                // special case for PARAM_BOOL - we want true/false instead of the usual 1/0 - we can not be too strict here ;-)
                if (is_bool($params) or $params === 0 or $params === 1 or $params === '0' or $params === '1') {
                    return (bool)$params;
                }
            }
            $debuginfo = 'Invalid external api parameter: the value is "' . $params .
                '", the server was expecting "' . $description->type . '" type';
            return self::validate_param($params, $description->type, $description->allownull, $debuginfo);
        } else if ($description instanceof external_single_structure) {
            if (!is_array($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $params)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new moodle_exception('generalexceptionmessage','error',null,'Missing required key in single structure: '. $key);
                    }
                    if ($subdesc->required == VALUE_DEFAULT) {
                        try {
                            $result[$key] = self::validate_parameters($subdesc, $subdesc->default);
                        } catch (invalid_parameter_exception $e) {
                            //we are only interested by exceptions returned by validate_param() and validate_parameters()
                            //(in order to build the path to the faulty attribut)
                            throw new moodle_exception('generalexceptionmessage','error',null,$key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                        }
                    }
                } else {
                    try {
                        $result[$key] = self::validate_parameters($subdesc, $params[$key]);
                    } catch (invalid_parameter_exception $e) {
                        //we are only interested by exceptions returned by validate_param() and validate_parameters()
                        //(in order to build the path to the faulty attribut)
                        throw new moodle_exception('generalexceptionmessage','error',null,$key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                    }
                }
                unset($params[$key]);
            }
            if (!empty($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Unexpected keys (' . implode(', ', array_keys($params)) . ') detected in parameter array.');
            }
            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($params as $param) {
                $result[] = self::validate_parameters($description->content, $param);
            }
            return $result;

        } else {
            throw new moodle_exception('generalexceptionmessage','error',null,'Last Step');
        }
    }//validate_parameters

    /**
     * @static
     * @param           $param
     * @param           $type
     * @param           bool $allownull
     * @param           string $debuginfo
     * @return          mixed|null
     * @throws          moodle_exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the parameter
     */
    public static function validate_param($param, $type, $allownull=NULL_NOT_ALLOWED, $debuginfo='') {
        if (is_null($param)) {
            if ($allownull == NULL_ALLOWED) {
                return null;
            } else {
                throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
            }
        }
        if (is_array($param) or is_object($param)) {
            throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
        }

        $cleaned = clean_param($param, $type);

        if ($type == PARAM_FLOAT) {
            // Do not detect precision loss here.
            if (is_float($param) or is_int($param)) {
                // These always fit.
            } else if (!is_numeric($param) or !preg_match('/^[\+-]?[0-9]*\.?[0-9]*(e[-+]?[0-9]+)?$/i', (string)$param)) {
                throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
            }
        } else if ((string)$param !== (string)$cleaned) {
            // Conversion to string is usually lossless.
            throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
        }

        return $cleaned;
    }
}//local_wsks_external
