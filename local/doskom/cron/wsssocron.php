<?php
/**
 * WSDOSKOM  Cron - Library
 *
 * @package         local/wsdoskom
 * @subpackage      cron
 * @copyright       2015        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/02/2015
 * @author          eFaktor     (fbv)
 *
 */

define ('EXITS_USERNAME','manager');

class wsdoskom_cron {

    /**
     * Description
     * WSSSO - Cron
     * To import users
     *
     * @return          bool
     *
     * @creationDate    12/01/2015
     * @author          eFaktor     (fbv)
     */
    public static function cron() {
        /* Variables    */
        $companies      = null;

        try {
            mtrace('Start WSDOSKOM Import Users '. time());

            // Get the companies
            $companies = self::get_companies();

            /**
             * Action for each company
             * --> Call Web Service
             * --> Save Users
             * --> Status Users
             */
            // Call Web Service and Get the Users to Import
            mtrace('Start WSDOSKOM Import Users '. time() . "\n");
            foreach ($companies as $company) {
                $company->import         = self::call_ws($company);

                //Add company
                $companies[$company->id] = $company;
            }//for_companies

            // Save users temporary table
            foreach ($companies as $company) {
              if ($company->import) {
                self::save_temp_users_to_import($company->import,$company->id);
              }//if_companyImport
            }//for_companies

            // Import Users
            self::import_users();

            // Clean Temporary Table
            self::clean_temporary();

            mtrace('Finish WSDOSKOM Import Users '. time());

            return true;
        }catch (Exception $ex) {
            mtrace($ex->getTraceAsString());
           return false;
        }//try_catch
    }//cron


    /************/
    /* PRIVATE  */
    /************/

    /**
     * Description
     * Get the companies, which they need to import their usrs
     *
     * @return          array       Companies list
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_companies() {
        /* Variables    */
        global $DB;
        $companies = array();

        try {
            // Execute
            $rdo = $DB->get_records('company_data',null,'id','id,user,token');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Company     */
                    $info = new stdClass();
                    $info->id       = $instance->id;
                    $info->user     = $instance->user;
                    $info->token    = $instance->token;
                    $info->import   = null;

                    // Add company
                    $companies[$instance->id] = $info;
                }//for_rdo
            }//if_rdo

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_companies

    /**
     * Description
     * Call the Web Services to get the users
     *
     * @param           int         $company  Id company
     *
     * @return          mixed|null            Service response
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function call_ws($company) {
        /* Variables    */
        $urlWs          = null;
        $response       = null;
        $plugin_info    = null;

        try {
            // Plugin Info
            $plugin_info     = get_config('local_doskom');

            // Build url end point
            $urlWs = $plugin_info->wsdoskom_end_point . '/' . $company->id .'/personalia/no';

            // Call Web Service
            $ch = curl_init($urlWs);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, false );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'User-Agent: Moodle 1.0',
                    'Content-Type: application/json ',
                    'DOSSIER_USER: ' . $company->user,
                    'DOSSIER_PASSWORD: ' . $company->token)
            );

            $response   = curl_exec( $ch );
            curl_close( $ch );

            // Format Data
            if ($response === false) {
                return null;
            }else {
                $response = json_decode($response);
                if (isset($response->status)) {
                    mtrace($response->msg);
                    return null;
                }else {
                    return $response;
                }
            }//if_response
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Call_WS_PILOT

    /**
     * Description
     * Save the users to import in a temporary table
     *
     * @param           Object      $infoImport     User info
     * @param           int         $companyId      Id company
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function save_temp_users_to_import($infoImport,$companyId) {
        /* Variables    */
        global $DB;
        $userInfo       = null;
        $fieldEntries   = null;
        $fieldEntry     = null;

        // Begin Transaction
        $trans = $DB->start_delegated_transaction();
        try {
            $fieldEntries = $infoImport->fieldEntries;
            if ($fieldEntries) {
                foreach ($fieldEntries as $entries) {
                    $fieldEntry = $entries->fieldEntry;
                    if ($fieldEntry) {
                        // Get User Info
                        $userInfo               = new stdClass();
                        $userInfo->companyid    = $companyId;
                        $userInfo->status       = 0;
                        $userInfo->personssn    = '';

                        foreach ($fieldEntry as $entry) {
                            switch (strtolower(trim($entry->name))) {
                                case 'personid':
                                    $userInfo->personid = trim($entry->value);

                                    break;

                                case 'personextid':
                                    $userInfo->personextid = trim($entry->value);

                                    break;

                                case 'employmentid':
                                    $userInfo->employmentid = trim($entry->value);

                                    break;

                                case 'employmentextid':
                                    $userInfo->employmentextid = trim($entry->value);

                                    break;

                                case 'username':
                                    $userInfo->username = trim($entry->value);

                                    break;

                                case 'userextname':
                                    $userInfo->userextname = trim($entry->value);

                                    break;

                                case 'firstname':
                                    $userInfo->firstname = trim($entry->value);

                                    break;

                                case 'lastname':
                                    $userInfo->lastname = trim($entry->value);

                                    break;

                                case 'personssn':
                                    $userInfo->personssn = trim($entry->value);

                                    break;

                                case 'email':
                                    $userInfo->email = trim($entry->value);

                                    break;

                                case 'city':
                                    $userInfo->city = trim($entry->value);

                                    break;

                                case 'country':
                                    $userInfo->country = trim($entry->value);

                                    break;

                                case 'divisionname':
                                    $userInfo->divisionname = trim($entry->value);

                                    break;

                                case 'divisionextid':
                                    $userInfo->divisionextid = trim($entry->value);

                                    break;
                            }//switch_name
                        }//for_entry

                        // Check if the user already exists
                        if ($userInfo->personssn) {
                            // Save Temporary Table
                            $secret = $userInfo->companyid . '##SEP##' . $userInfo->personid;
                            if (!$DB->get_record('user',array('idnumber' => $userInfo->personssn))) {
                                $DB->insert_record('user_personalia',$userInfo);
                            }else if (!$DB->get_record('user',array('secret' => $secret))) {
                                $DB->insert_record('user_personalia',$userInfo);
                            }
                        }//if_personssn
                    }//if_fieldEntry
                }//for_entries
            }//if_fieldEntries

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//save_temp_users_to_import


    /**
     * Description
     * Import the users into the correct company
     *
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function import_users() {
        /* Variables    */
        global $DB,$CFG;
        $usersToImport      = null;
        $user_id            = null;
        $time               = null;
        $secret             = null;
        $sql                = null;

        try {
            // Local time
            $time  = time();

            // Get users To Import
            $usersToImport = $DB->get_records('user_personalia',array('status' => 0));
            if ($usersToImport) {
                foreach($usersToImport as $userInfo) {
                    // New User
                    $new_user = new stdClass();

                    // Username
                    if ($userInfo->personssn) {
                        $new_user->username     = $userInfo->personssn;
                    }else {
                        $new_user->username     = $userInfo->username;
                    }//if_personssn

                    // Password
                    $new_user->password     = '';
                    // First name
                    $new_user->firstname    = $userInfo->firstname;
                    // Last name
                    $new_user->lastname     = $userInfo->lastname;
                    // eMail
                    $new_user->email        = $userInfo->email;
                    // Lang
                    $new_user->lang         = 'no';
                    // City
                    if ($userInfo->city) {
                        $new_user->city         = $userInfo->city;
                    }//if_city

                    // Country
                    if ($userInfo->country) {
                        // Countries List
                        $countries      = get_string_manager()->get_list_of_countries(false);
                        $country        = array_search($userInfo->country,$countries);
                        if ($country) {
                            $new_user->country  = $country;
                        }
                    }//if_country

                    // Personal Number
                    if ($userInfo->personssn) {
                        $new_user->idnumber = $userInfo->personssn;
                    }//if_personalNumber

                    // Workplace
                    if ($userInfo->divisionname) {
                        $new_user->department = $userInfo->divisionname;
                    }//if_divisionName

                    // Identifier of user in Dossier Profile
                    $new_user->secret       = $userInfo->companyid . '##SEP##'. $userInfo->personid;
                    $new_user->confirmed    = '1';
                    $new_user->firstaccess  = $time;
                    $new_user->timemodified = $time;
                    $new_user->mnethostid   = $CFG->mnet_localhost_id;
                    $new_user->auth         = 'saml';
                    $new_user->password     = 'not cached';
                    $new_user->source       = 'KOMMIT';

                    // Check if the user already exists
                    $user_id = self::exists_user($new_user->secret,$new_user->username,$userInfo->personssn);
                    if ($user_id) {
                        // Update User
                        $new_user->id = $user_id;
                        $DB->update_record('user',$new_user);

                        // Update Status
                        $userInfo->status = 1;
                        $DB->update_record('user_personalia',$userInfo);
                    }else {
                        // New User
                        $new_user->id = $DB->insert_record('user',$new_user);

                        // New User Company Relation
                        $user_company = new stdClass();
                        $user_company->userid       = $new_user->id;
                        $user_company->companyid    = $userInfo->companyid;
                        $user_company->timecreated  = $time;
                        $DB->insert_record('user_company',$user_company);

                        // Update Status
                        $userInfo->status = 1;
                        $DB->update_record('user_personalia',$userInfo);
                    }//if_else_user_NewVersion
                }//for_users_import
            }//if_UsersToImport

        }catch (Exception $ex) {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' DSOKOM API CRON . ' . "\n";
            $dbLog .= " ERROR: " . $ex->getTraceAsString();
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_users

    /**
     * Description
     * Check if the user already exists
     *
     * @param           string  $externalId   Id from the external system
     * @param           string  $username     username
     * @param           string  $idNumber     personal number
     *
     * @return          bool|null               Internal user id connected with
     * @throws          Exception
     *
     * @creationDate    29/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function exists_user($externalId,$username,$idNumber) {
        /* Variables */
        $user_id = null;

        try {
            /**
             * Check if user already exists.
             * First --> Check user with the external ID
             * After --> Chech with username, personalnumber
             */
            $user_id = self::exists_user_secret($externalId);
            if (!$user_id) {
                $user_id = self::exists_user_no_secret($username,$idNumber);
            }

            return $user_id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//exists_user

    /**
     * Description
     * Check if the user already exits by username or idnumber
     *
     * @param           string  $username   username
     * @param           string  $idNumber   personal number
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the users already exists
     *
     * @updateDate      24/09/2016
     * @author          eFaktor     (fbv)
     */
    private static function exists_user_no_secret($username,$idNumber) {
        /* Variables    */
        global $DB;

        try {
            // Search Criteria
            $params['username']     = $username;
            $params['idnumber']     = $idNumber;

            // SQL Instruction
            $sql = " SELECT	u.id
                     FROM	{user} 	u
                     WHERE	(u.username = :username
                            OR 
                            (u.idnumber = :idnumber
                             AND
                             u.idnumber IS NOT NULL
                             AND
                             u.idnumber != ''
                            ))
                            AND u.username NOT IN ('guest','admin') ";

            //Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->id;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistsUser

    /**
     * Description
     * Check if the user already exists by the external id
     *
     * @param           string  $externalId   Internal ID from the external system
     *
     * @return          int|null
     * @throws          Exception
     *
     * @creationDate    29//11/2016
     * @author          eFaktor     (fbv)
     */
    private static function exists_user_secret($externalId) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['secret'] = $externalId;

            // Execute
            $rdo = $DB->get_record('user',$params,'id');
            if ($rdo) {
                return $rdo->id;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//exists_user_secret

    /**
     * Description
     * Clean the temporary table.
     * Remove only the users have been imported
     * 
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function clean_temporary() {
        /* Variables    */
        global $DB;

        try {
            // Clean table
            $DB->delete_records('user_personalia',array('status' =>1));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Clean_Temporary
}//WSSSO_Cron