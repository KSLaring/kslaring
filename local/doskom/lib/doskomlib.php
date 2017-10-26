<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * DOSKOM library
 *
 * @package         local
 * @subpackage      doskom/lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/09/2017
 * @author          eFaktor     (fbv)
 *
 */

class doskom {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Write doskom log
     *
     * @param       $log
     *
     * @throws      Exception
     *
     * @creationDate    08/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function write_doskom_log($log) {
        /* Variables */
        global $DB;
        $info = null;

        try {
            // Write log
            if ($log) {
                asort($log);
                foreach ($log as $info) {
                    $DB->insert_record('doskom_log',$info);
                }//for_log
            }//if_log
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//write_doskom_log

    /**
     * Description
     * Get all doskom companies
     *
     * @param           $log
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    08/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function doskom_companies(&$log) {
        /* Variables */
        $doskom     = null;
        $infolog    = null;
        $time       = null;

        try {
            // Local time
            $time = time();

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'doskom_companies';
            $infolog->description   = 'Get all doskom companies,Active and not active';
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            // Doskom companies
            $doskom = new stdClass();
            // Get companies active
            $doskom->active = self::get_doskom_companies(1);
            // Get companies no active
            $doskom->noactive = self::get_doskom_companies(0);

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'doskom_companies';
            $infolog->description   = 'Finish getting all doskom companies,Active and not active';
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            return $doskom;
        }catch (Exception $ex) {
            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'doskom_companies';
            $infolog->description   = 'ERROR. Get all doskom companies,Active and not active';
            $infolog->description  .= ' ERROR: ' . $ex->getTraceAsString();
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            throw $ex;
        }//try_catch
    }//doskom_companies

    /**
     * Description
     * Import all users from DOSKOM
     *
     * @param           $company
     * @param           $log
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    09/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function import_doskom($company,&$log) {
        /* Variables */
        $noerror    = null;
        $infolog    = null;

        try {
            // Doskom log
            $infolog = new stdClass();
            $infolog->action      = 'import_doskom';
            $infolog->description = 'Start importing data for company ' . $company->id .'. First all users will be importted in a temporary table';
            $infolog->description .= ' and after they will be extracted and moved to user';
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            // Save in temporary table
            if (self::save_temporary_import($company->import,$company->id,$log)) {
                $nonerror = self::import_users($company->id,$company->label,$log);
            }//if_noerror

            // DOSKOM Log
            $infolog->action      = 'import_doskom';
            $infolog->description = 'Finish importing data for company ' . $company->id .'.';
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            return $noerror;
        }catch (Exception $ex) {
            // Doskom log
            $infolog = new stdClass();
            $infolog->action = 'import_doskom';
            $infolog->description  = 'ERROR. Finish extracting data for company ' . $company->id .' in a temporary table (user_personalia).';
            $infolog->description .= ' ERROR: ' . $ex->getTraceAsString() ;
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            throw $ex;
        }//try_catch
    }//import_doskom

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
    public static function clean_temporary(&$log) {
        /* Variables    */
        global $DB;
        $infolog   = null;

        try {
            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action = 'clean_temporary';
            $infolog->description = 'Clean temporary data from user_personalia';
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            // Clean table
            $DB->delete_records('user_personalia',array('status' =>1));
        }catch (Exception $ex) {
            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action = 'clean_temporary';
            $infolog->description = 'ERROR. Clean temporary data from user_personalia';
            $infolog->description .= ' ERROR: ' . $ex->getTraceAsString();
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            throw $ex;
        }//try_catch
    }//Clean_Temporary

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Import all users connected with a specific company
     *
     * @param           $company
     * @param           $label
     * @param           $log
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    09/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function import_users($company,$label,&$log) {
        /* Variables    */
        global $DB,$CFG;
        $userstoimp     = null;
        $usercompany    = null;
        $userid         = null;
        $infouser       = null;
        $newuser        = null;
        $time           = null;
        $secret         = null;
        $sql            = null;
        $infolog        = null;
        $logapi         = null;
        $imported       = null;

        try {
            // Local time
            $time  = time();

            // DOSKOM Log
            $infolog = new stdClass();
            $infolog->action      = 'import_users';
            $infolog->description = 'Start importing users from company ' . $company;
            $infolog->timecreated = $time;
            // Add log
            $log[] = $infolog;

            // Get users To Import
            $userstoimp = $DB->get_records('user_personalia',array('status' => 0));
            if ($userstoimp) {
                foreach($userstoimp as $infouser) {
                    $imported = false;

                    // DOSKOM LOG
                    $infolog = new stdClass();
                    $infolog->action      = 'import_users';
                    $infolog->description = 'Extracting user: ' . $infouser->personssn . ' Company ' . $company;
                    $infolog->timecreated = $time;
                    // Add log
                    $log[] = $infolog;

                    // New User
                    $newuser = new stdClass();
                    // Username && idnumber (personal number)
                    if ($infouser->personssn) {
                        $newuser->username  = $infouser->personssn;
                        $newuser->idnumber  = $infouser->personssn;
                    }else {
                        $newuser->username  = $infouser->username;
                    }//if_personssn
                    $newuser->password      = '';
                    $newuser->firstname     = $infouser->firstname;
                    $newuser->lastname      = $infouser->lastname;
                    $newuser->email         = $infouser->email;
                    // Lang
                    $newuser->lang         = 'no';
                    // City
                    if ($infouser->city) {
                        $newuser->city         = $infouser->city;
                    }//if_city

                    // Country
                    if ($infouser->country) {
                        // Countries List
                        $countries      = get_string_manager()->get_list_of_countries(false);
                        $country        = array_search($infouser->country,$countries);
                        if ($country) {
                            $newuser->country  = $country;
                        }
                    }//if_country

                    // Workplace
                    if ($infouser->divisionname) {
                        $newuser->department = $infouser->divisionname;
                    }//if_divisionName

                    // Identifier of user in Dossier Profile
                    $newuser->secret       = $infouser->companyid . '##SEP##'. $infouser->personid;
                    $newuser->confirmed    = '1';
                    $newuser->firstaccess  = $time;
                    $newuser->timemodified = $time;
                    $newuser->mnethostid   = $CFG->mnet_localhost_id;
                    $newuser->auth         = 'saml';
                    $newuser->password     = 'not cached';
                    $newuser->source       = $label;

                    // Check if the user already exists
                    $user_id = self::exists_user($newuser->secret,$newuser->username,$infouser->personssn,$log);
                    if ($user_id) {
                        // Update User
                        $newuser->id = $user_id;
                        $DB->update_record('user',$newuser);

                        // Update Status
                        $infouser->status = 1;
                        $DB->update_record('user_personalia',$infouser);

                        $imported = true;
                    }else {
                        // New User
                        $newuser->id = $DB->insert_record('user',$newuser);

                        // New User Company Relation
                        $usercompany = new stdClass();
                        $usercompany->userid       = $newuser->id;
                        $usercompany->companyid    = $infouser->companyid;
                        $usercompany->timecreated  = $time;
                        $DB->insert_record('user_company',$usercompany);

                        // Update Status
                        $infouser->status = 1;
                        $DB->update_record('user_personalia',$infouser);

                        $imported = true;
                    }//if_else_user_NewVersion

                    if ($imported) {
                        // Api log info
                        $logapi = new stdClass();
                        $logapi->username       = $newuser->username;
                        $logapi->firstname      = $newuser->firstname;
                        $logapi->lastname       = $newuser->lastname;
                        $logapi->personssn      = $newuser->personssn;
                        $logapi->email          = $newuser->email;
                        $logapi->companyid      = $infouser->companyid;
                        $logapi->imported       = true;
                        $logapi->timeimported   = $time;

                        // Insert
                        $DB->insert_record('doskom_api_log',$logapi);
                    }
                }//for
            }else {
                // DOSKOM LOG
                $infolog = new stdClass();
                $infolog->action      = 'import_users';
                $infolog->description = 'Company ' . $company . '. There are no users to import';
                $infolog->timecreated = $time;
                // Add log
                $log[] = $infolog;
            }//if_else_$userstoimp

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action      = 'import_users';
            $infolog->description = 'Finish importing users from company ' . $company;
            $infolog->timecreated = $time;
            // Add log
            $log[] = $infolog;

            return true;
        }catch (Exception $ex) {
            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action       = 'import_users';
            $infolog->description  = 'ERROR. Finish importing users from company ' . $company;
            $infolog->description .= ' ERROR: ' . $ex->getTraceAsString();
            $infolog->timecreated  = $time;
            // Add log
            $log[] = $infolog;

            throw $ex;
        }//try_catch
    }//import_users

    /**
     * Description
     * Save the users to import in a temporary table
     *
     * @param           $data
     * @param           $company
     * @param           $log
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    08/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function save_temporary_import($data,$company,&$log) {
        /* Variables    */
        global $DB;
        $infouser       = null;
        $fieldEntries   = null;
        $fieldEntry     = null;
        $trans          = null;
        $infolog        = null;
        $logapi         = null;
        $saved          = null;
        $time           = null;

        // Begin Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action      = 'save_temporary_import';
            $infolog->description = 'Start extractind data for company ' . $company .' in a temporary table (user_personalia).';
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            $fieldEntries = $data->fieldEntries;
            if ($fieldEntries) {
                foreach ($fieldEntries as $entries) {
                    $fieldEntry = $entries->fieldEntry;
                    if ($fieldEntry) {
                        // Get User Info
                        $infouser               = new stdClass();
                        $infouser->companyid    = $company;
                        $infouser->status       = 0;
                        $infouser->personssn    = '';

                        foreach ($fieldEntry as $entry) {
                            $saved          = false;
                            switch (strtolower(trim($entry->name))) {
                                case 'personid':
                                    $infouser->personid = trim($entry->value);

                                    break;

                                case 'personextid':
                                    $infouser->personextid = trim($entry->value);

                                    break;

                                case 'employmentid':
                                    $infouser->employmentid = trim($entry->value);

                                    break;

                                case 'employmentextid':
                                    $infouser->employmentextid = trim($entry->value);

                                    break;

                                case 'username':
                                    $infouser->username = trim($entry->value);

                                    break;

                                case 'userextname':
                                    $infouser->userextname = trim($entry->value);

                                    break;

                                case 'firstname':
                                    $infouser->firstname = trim($entry->value);

                                    break;

                                case 'lastname':
                                    $infouser->lastname = trim($entry->value);

                                    break;

                                case 'personssn':
                                    $infouser->personssn = trim($entry->value);

                                    break;

                                case 'email':
                                    $infouser->email = trim($entry->value);

                                    break;

                                case 'city':
                                    $infouser->city = trim($entry->value);

                                    break;

                                case 'country':
                                    $infouser->country = trim($entry->value);

                                    break;

                                case 'divisionname':
                                    $infouser->divisionname = trim($entry->value);

                                    break;

                                case 'divisionextid':
                                    $infouser->divisionextid = trim($entry->value);

                                    break;
                            }//switch_name
                        }//for_entry

                        // Check if the user already exists
                        if ($infouser->personssn) {
                            // Doskom log
                            $infolog = new stdClass();
                            $infolog->action = 'save_temporary_import';
                            $infolog->description = 'User extracted ' . $infouser->personssn;
                            $infolog->timecreated = $time;
                            // Add log
                            $log[] = $infolog;

                            // Save Temporary Table
                            $secret = $infouser->companyid . '##SEP##' . $infouser->personid;
                            if (!$DB->get_record('user',array('idnumber' => $infouser->personssn))) {
                                $DB->insert_record('user_personalia',$infouser);

                                // Doskom log
                                $infolog = new stdClass();
                                $infolog->action = 'save_temporary_import';
                                $infolog->description = 'User extracted && saved' . $infouser->personssn;
                                $infolog->timecreated = $time;
                                // Add log
                                $log[] = $infolog;

                                $saved = true;
                            }else if (!$DB->get_record('user',array('secret' => $secret))) {
                                $DB->insert_record('user_personalia',$infouser);

                                // Doskom log
                                $infolog = new stdClass();
                                $infolog->action = 'save_temporary_import';
                                $infolog->description = 'User extracted && saved' . $infouser->personssn;
                                $infolog->timecreated = $time;
                                // Add log
                                $log[] = $infolog;

                                $saved = true;
                            }
                        }//if_personssn

                        if ($saved) {
                            // Api log info
                            $logapi = new stdClass();
                            $logapi->username   = $infouser->username;
                            $logapi->firstname  = $infouser->firstname;
                            $logapi->lastname   = $infouser->lastname;
                            $logapi->personssn  = $infouser->personssn;
                            $logapi->email      = $infouser->email;
                            $logapi->companyid  = $infouser->companyid;
                            $logapi->saved      = true;
                            $logapi->timesaved  = $time;

                            // Insert
                            $DB->insert_record('doskom_api_log',$logapi);
                        }
                    }//if_fieldentry
                }
            }else {
                // Doskom log
                $infolog = new stdClass();
                $infolog->action = 'save_temporary_import';
                $infolog->description = 'Company ' . $company .' NO DATA TO EXTRACT';
                $infolog->timecreated = time();
                // Add log
                $log[] = $infolog;
            }

            // Doskom log
            $infolog = new stdClass();
            $infolog->action = 'save_temporary_import';
            $infolog->description = 'Finish extracting data for company ' . $company .' in a temporary table (user_personalia).';
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Doskom log
            $infolog = new stdClass();
            $infolog->action = 'save_temporary_import';
            $infolog->description  = 'ERROR. Finish extracting data for company ' . $company .' in a temporary table (user_personalia).';
            $infolog->description .= ' ERROR: ' . $ex->getTraceAsString() ;
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//save_temporary_import

    /**
     * Description
     * Get all doskom companies by status
     *
     * @param           $status
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    08/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_doskom_companies($status) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['status'] = $status;

            // SQL Instruction
            $sql = " SELECT	  cd.id,
                              cd.user,
                              cd.token,
                              dk.api,
                              dk.label
                     FROM	  {company_data}	cd
                        JOIN  {doskom_company}	dkco ON 	dkco.companyid  = cd.id
                                                     AND 	dkco.active     = :status
                        JOIN  {doskom}			dk	 ON 	dk.id           = dkco.doskomid ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_doskom_companies

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
    private static function exists_user($externalId,$username,$idNumber,&$log) {
        /* Variables */
        $userid = null;
        $infolog    = null;

        try {
            // DOSKOM Log
            $infolog = new stdClass();
            $infolog->action      = 'import_users';
            $infolog->description = 'Checking user exist --> ' . $externalId . ", " . $username . ", " . $idNumber . '.';
            $infolog->timecreated = time();
            $log[] = $infolog;

            /**
             * Check if user already exists.
             * First --> Check user with the external ID
             * After --> Chech with username, personalnumber
             */
            $userid = self::exists_user_secret($externalId);
            if (!$userid) {
                $userid = self::exists_user_no_secret($username,$idNumber);
            }

            // DOSKOM Log
            $infolog->action      = 'import_users';
            $infolog->description = 'Finish Checking user exist --> ' . $externalId . ", " . $username . ", " . $idNumber . '.';
            $infolog->timecreated = time();
            $log[] = $infolog;

            return $userid;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//exists_user

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

            // SQL Instruction
            $sql = " SELECT	u.id
                     FROM	{user} 	u
                     WHERE	u.secret = :secret
                        AND u.username NOT IN ('guest','admin') ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
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
}//doskom
