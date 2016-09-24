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

class WSDOSKOM_Cron {

    /**
     * @return          bool
     *
     * @creationDate    12/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * WSSSO - Cron
     * To import users
     */
    public static function cron() {
        /* Variables    */
        $companies      = null;

        /* Plugins Info */
        $plugin_info     = get_config('local_doskom');

        try {
            mtrace('Start WSDOSKOM Import Users '. time());

            /* Get the companies    */
            $companies = self::Get_Companies();
            /* Actions for each company */
            /*      --> Call Web Service    */
            /*      --> Save Users          */
            /*      --> Status Users        */
            /* Call Web Service and Get the Users to Import */
            mtrace('Start WSDOSKOM Import Users '. time() . "\n");
            foreach ($companies as $company) {
                $company->import = self::Call_WS($company->id);
                $companies[$company->id] = $company;
            }//for_companies

            /* Save users temporary table      */
            foreach ($companies as $company) {
              if ($company->import) {
                self::SaveTemp_UsersToImport($company->import,$company->id);
              }//if_companyImport
            }//for_companies

            /* Import Users */
            self::ImportUsers();

            /* Clean Temporary Table    */
            self::Clean_Temporary();

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
     * @return          array
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies, which they need to import their usrs
     */
    private static function Get_Companies() {
        /* Variables    */
        global $DB;
        $companies = array();

        try {
            /* Execute  */
            $rdo = $DB->get_records('company_data',null,'id','id');
            if ($rdo) {
                foreach ($rdo as $instance) {
                        /* Info Company     */
                        $info = new stdClass();
                        $info->id       = $instance->id;
                        $info->import   = null;

                        $companies[$instance->id] = $info;
                }//for_rdo
            }//if_rdo

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Companies

    /**
     * @param           $companyId
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Call the Web Services to get the users
     */
    private static function Call_WS($companyId) {
        /* Variables    */
        $urlWs      = null;
        $response   = null;

        try {
            /* Plugins Info */
            $plugin_info     = get_config('local_doskom');

            /* Build url end point  */
            $urlWs = $plugin_info->wsdoskom_end_point . '/' . $companyId .'/personalia/no';

            /* Call Web Service     */
            $ch = curl_init($urlWs);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, false );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'User-Agent: Moodle 1.0',
                    'Content-Type: application/json ',
                    'DOSSIER_USER: ' . $plugin_info->local_wsdoskom_username,
                    'DOSSIER_PASSWORD: ' . $plugin_info->local_wsdoskom_password)
            );

            $response   = curl_exec( $ch );
            curl_close( $ch );

            /* Format Data  */
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
     * @param           $infoImport
     * @param           $companyId
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save the users to import in a temporary table
     */
    private static function SaveTemp_UsersToImport($infoImport,$companyId) {
        /* Variables    */
        global $DB;
        $userInfo       = null;
        $fieldEntries   = null;
        $fieldEntry     = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            $fieldEntries = $infoImport->fieldEntries;
            if ($fieldEntries) {
                foreach ($fieldEntries as $entries) {
                    $fieldEntry = $entries->fieldEntry;
                    if ($fieldEntry) {
                        /* Get User Info    */
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
                                case 'mobilephone':
                                    //$userInfo->mobilephone = trim($entry->value);

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

                        /* Check if the user already exists*/
                        //if (isset($userInfo->personid)) {
                        if ($userInfo->personssn) {
                            /* Save Temporary Table */
                            $secret = $userInfo->companyid . '##SEP##' . $userInfo->personid;
                            if (!$DB->get_record('user',array('idnumber' => $userInfo->personssn))) {
                                $DB->insert_record('user_personalia',$userInfo);
                            }else if (!$DB->get_record('user',array('secret' => $secret))) {
                                $DB->insert_record('user_personalia',$userInfo);
                            }
                        }

                    }//if_fieldEntry
                }//for_entries
            }//if_fieldEntries

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SaveTemp_UsersToImport_PILOT


    /**
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import the users into the correct company
     */
    private static function ImportUsers() {
        /* Variables    */
        global $DB,$CFG;
        $usersToImport      = null;
        $user_id            = null;
        $time               = time();
        $secret             = null;
        $sql                = null;

        try {
            /* Get users To Import  */
            $sql = " SELECT *
                     FROM   {user_personalia} 
                     WHERE  status = 0
                        AND	(username 	IS NOT NULL OR username != '')
                        AND	(personssn 	IS NOT NULL OR personssn != '')
                        AND (firstname 	IS NOT NULL OR firstname != '')
                        AND (lastname 	IS NOT NULL OR lastname != '')
                        AND (email 		IS NOT NULL OR email != '') ";

            $usersToImport = $DB->get_records('user_personalia',array('status' => 0));
            if ($usersToImport) {
                foreach($usersToImport as $userInfo) {
                    /* New User */
                    $new_user = new stdClass();

                    /* Username     */
                    if ($userInfo->personssn) {
                        $new_user->username     = $userInfo->personssn;
                    }else {
                        $new_user->username     = $userInfo->username;
                    }//if_personssn
                    /* Password     */
                    $new_user->password     = '';
                    /* First name   */
                    $new_user->firstname    = $userInfo->firstname;
                    /* Last name    */
                    $new_user->lastname     = $userInfo->lastname;
                    /* eMail        */
                    $new_user->email        = $userInfo->email;
                    /* Lang         */
                    $new_user->lang         = 'no';
                    /* City         */
                    if ($userInfo->city) {
                        $new_user->city         = $userInfo->city;
                    }//if_city
                    /* Country      */
                    if ($userInfo->country) {
                        /* Countries List */
                        $countries      = get_string_manager()->get_list_of_countries(false);
                        $country        = array_search($userInfo->country,$countries);
                        if ($country) {
                            $new_user->country  = $country;
                        }
                    }//if_country
                    /* Personal Number  */
                    if ($userInfo->personssn) {
                        $new_user->idnumber = $userInfo->personssn;
                    }//if_personalNumber
                    /* Mobile/Phone */
                    if ($userInfo->mobilephone) {
                        //$new_user->phone1   = $userInfo->mobilephone;
                    }//if_mobilePhone
                    /* Workplace    */
                    if ($userInfo->divisionname) {
                        $new_user->department = $userInfo->divisionname;
                    }//if_divisionName

                    /* Identifier of user in Dossier Profile    */
                    $new_user->secret       = $userInfo->companyid . '##SEP##'. $userInfo->personid;
                    $new_user->confirmed    = '1';
                    $new_user->firstaccess  = $time;
                    $new_user->timemodified = $time;
                    $new_user->mnethostid   = $CFG->mnet_localhost_id;
                    $new_user->auth         = 'saml';
                    $new_user->password     = 'not cached';
                    $new_user->source       = 'KOMMIT';

                    /* Check if the user already exists */
                    /* Check if the user exists with the new version */
                    $user_id = self::ExistsUser($new_user->username,$userInfo->personssn);
                    if ($user_id) {
                        /* Update User  */
                        $new_user->id = $user_id;
                        $DB->update_record('user',$new_user);

                        /* Update Status    */
                        $userInfo->status = 1;
                        $DB->update_record('user_personalia',$userInfo);
                    }else {
                        /* New User     */
                        $new_user->id = $DB->insert_record('user',$new_user);

                        /* New User Company Relation    */
                        $user_company = new stdClass();
                        $user_company->userid = $new_user->id;
                        $user_company->companyid = $userInfo->companyid;
                        $user_company->timecreated = $time;
                        $DB->insert_record('user_company',$user_company);

                        /* Update Status    */
                        $userInfo->status = 1;
                        $DB->update_record('user_personalia',$userInfo);
                    }//if_else_user_NewVersion
                }//for_users_import
            }//if_UsersToImport

        }catch (Exception $ex) {
            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' DSOKOM API CRON . ' . "\n";
            $dbLog .= " ERROR: " . $ex->getTraceAsString();
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//ImportUsers

    /**
     * @param               $userID
     * @param               $username
     * @param               $companyID
     * @return              bool
     * @throws              Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the users already exists
     */
    private static function ExistsUser_old($userID,$username,$companyID) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params['secret']   = $userID;
            $params['username'] = $username;
            $params['company']  = $companyID;

            /* SQL Instruction */
            $sql = " SELECT		u.id
                     FROM		{user} 				u
                        JOIN	{user_company}		uc	ON 	uc.userid 		= u.id
                                                        AND	uc.companyid 	= :company
                     WHERE		u.secret    = :secret
                        AND     u.username  = :username ";

            /* Execute  */
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
     * @param           $username
     * @param           $idNumber
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
     *
     * Description
     * Check if the user already exits by username or idnumber
     */
    private static function ExistsUser($username,$idNumber) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params['username']     = $username;
            $params['idnumber']     = $idNumber;

            /* SQL Instruction */
            $sql = " SELECT		u.id
                     FROM		{user} 				u
                     WHERE		(u.username  = :username
                                 OR 
                                 u.idnumber = :idnumber) ";

            /* Execute  */
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
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean the temporary table.
     * Remove only the users have been imported
     */
    private static function Clean_Temporary() {
        /* Variables    */
        global $DB;

        try {
            /* Clean Table  */
            $DB->delete_records('user_personalia',array('status' =>1));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Clean_Temporary
}//WSSSO_Cron