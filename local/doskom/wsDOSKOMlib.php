<?php

/**
 * Single Sign On Web Services - Library
 *
 * @package         local
 * @subpackage      doskom
 * @copyright       2015 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Implements all extra functions that the WS needs.
 *
 */

/* Constants for Redirect Page  */
define ('MAIN','main');
define ('ENROL','enrol');
define ('COURSES','courses');
/* Constants for UserRoles  */
define ('ROL_MANAGER','manager');
define ('ROL_CREATOR','coursecreator');
define ('ROL_EDIT_TEACHER','editingteacher');
define ('ROL_TEACHER','teacher');
define ('ROL_STUDENT','student');

define ('DOSKOM','kommit');

class WS_DOSKOM {

    /*************/
    /*  PUBLIC   */
    /*************/

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get a list of all the companies.
     */
    public static function getCompanyList() {
        /* Variables    */
        global $DB;
        $lst_company    = array();

        try {
            /* Company List */
            $lst_company[0] = get_string('sel_company','local_doskom');

            /* Get the companies    */
            $rdo = $DB->get_records('company_data',null,'name ASC','id,name');
            if ($rdo) {
                foreach ($rdo as $company) {
                    $lst_company[$company->id] = $company->name;
                }//for_company
            }//if_rdo

            return $lst_company;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCompanyList

    /**
     * @param           $userSSO
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Log in the user that comes from other system
     */
    public static function logInUser($userSSO,&$result) {
        /* Variables    */
        $url = null;

        try {
                /* Check if the user already exists */
                $user_id = self::checkUser($userSSO['id'],$userSSO,$result);
                if ($user_id) {
                    /* Update User  */
                    self::updateUser($user_id,$userSSO,$result);
                }else {
                    /* Create New User  */
                    $user_id = self::createUser($userSSO,$result);
                }//if_user_exist

                /* Add Gender   */
                if (is_numeric($userSSO['ssn']) && ($userSSO['ssn']) == 11) {
                    Gender::Add_UserGender($user_id,$userSSO['ssn']);
                }

                /* Assign Rol   */
                self::assignRol($user_id,$userSSO['UserRoles'],$result);

                /* Check if the user has been enrolled or not   */
                $action = strtolower($userSSO['RedirectPage']);
                if (($action == ENROL) && ($userSSO['course'])) {
                    self::assignRolCourse($user_id,$userSSO['companyId'],$userSSO['UserRoles'],$userSSO['course'],$result);
                }//if_Action_enrol

                /* We need to generate  the URL */
                $url = self::generateResponse($user_id,$userSSO,$result);

                $result['url'] = $url;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//logInUser

    /**
     * @param           $user_lst
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Deactivate all the users from the list.
     */
    public static function deactivateUsers($user_lst,&$result) {
        /* Variables    */
        $user_id    = null;
        $secret     = null;

        try {
            /* Deactivate the users one by one  */
            foreach($user_lst as $user_SSO) {
                /* Check if the user exists */
                $user_id = self::checkUser($user_SSO['id'],$user_SSO,$result);
                if ($user_id) {
                    self::deactivateUser($user_id,$result);
                }//if_user_id
            }//for_users
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . "-- Function: Deactivate Users";
            throw $ex;
        }//try_catch
    }//deactivateUsers

    /**
     * @param           $company
     * @param           $result
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the course catalog connected with
     */
    public static function getCourseCatalog($company,&$result) {
        /* Variables    */
        $catalog_lst = array();

        try {
            /* First, it must get all the categories connected with the company */
            $rdo_cat = self::getCategoryCompany($company);
            if ($rdo_cat) {
                foreach ($rdo_cat as $cat) {
                    /* Category Instance    */
                    $category = array();
                    $category['categoryId']     = $cat->id;
                    $category['categoryName']   = $cat->name;
                    $category['categoryDesc']   = html_to_text($cat->description);
                    $category['categoryParent'] = $cat->parent;
                    $category['courses']        = array();

                    /* Get the detail of all courses connected with */
                    $rdo_courses = self::getCoursesCategoryCompany($company,$cat->id);
                    if ($rdo_courses) {
                        $course_lst= array();
                        foreach ($rdo_courses as $instance) {
                            /* Course Instance  */
                            $course = new stdClass();
                            $course->courseId       = $instance->id;
                            $course->courseName     = $instance->fullname;
                            $course->courseSummary  = html_to_text($instance->summary);
                            $course->courseForm     = 'Online';

                            $course_lst[] = $course;
                        }//for_courses

                        $category['courses'] = $course_lst;
                    }//if_rdo_courses

                    $catalog_lst[] = $category;
                }//for_categories
            }//if_category_list

            return $catalog_lst;
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . " -- Function: Course Catalog";
            throw $ex;
        }//try_catch
    }//getCourseCatalog

    /**
     * @param           $criteria
     * @param           $result
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the historical of the completed courses.
     */
    public static function getHistoricalCoursesCompletion($criteria,&$result){
        /* Variables    */
        global $DB;
        global $CFG;
        $dblog      = null;
        $historical = array();
        $log        = array();
        $infoLog    = null;
        $time       = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START  Historical course completion . ' . "\n";

            // Local time
            $time = time();
            
            // first get courses
            $rdo_courses = self::get_courses_company_to_complete($criteria['companyId']);
            if ($rdo_courses) {
                // Get sql instruction
                $sql = self::get_sql_users_completions_in_period($criteria['dateFrom'],$criteria['dateTo'],$criteria['companyId']);

                $dblog .= "DATE FROM : " . $criteria['dateFrom'] . " - " . " DATE TO: " . $criteria['dateTo'] . " --> Company: " . $criteria['companyId'] . "\n";
                foreach ($rdo_courses as $course) {
                    // Log course
                    $dblog .= ' Course: ' . $course->id . ",";

                    // Search criteria
                    $courses = array();
                    $courses['courseId']    = $course->id;
                    $courses['courseName']  = $course->fullname;
                    $courses['users']       = array();

                    // Users have been completed
                    $rdo_users = $DB->get_records_sql($sql,array('course' => $course->id));
                    if ($rdo_users) {
                        $users = array();
                        foreach ($rdo_users as $instance) {
                            $user = new stdClass();
                            $user->completionId     = $instance->id;
                            $user->userId           = $instance->secret;
                            $user->completionDate   = $instance->completiondate;

                            $users[$instance->id] = $user;
                            
                            // Log
                            $infoLog = new stdClass();
                            $infoLog->company       = $criteria['companyId'];
                            $infoLog->course        = $course->id;
                            $infoLog->user          = $instance->user;
                            $infoLog->completion    = $instance->timecompleted;
                            $infoLog->timesent      = $time;
                            
                            // Add to the log
                            $log[] = $infoLog;
                        }//for_users

                        if ($users) {
                            $courses['users'] = $users;
                            $historical[]     = $courses;
                        }
                    }//if_rdo_users
                }//for_each_courses
            }else {
                $dblog .= ' No courses ' . "\n";
            }//if_rdo_courses

            // Close log
            $dblog .= "\n";
            error_log($dblog, 3, $CFG->dataroot . "/doskom.log");
            
            return array($historical,$log);
        }catch (Exception $ex) {
            $dblog .= "ERROR: " . $ex->getTraceAsString() . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/doskom.log");

            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . " -- Function: Historical Course COmpletion";
            throw $ex;
        }//try_catch
    }//getHistoricalCoursesCompletion

    /**
     * Description
     * Save log of historical completions sent to dossier
     * 
     * @creationDate        29/01/2017
     * @author              eFaktor     (fbv)
     * 
     * @param       array $log
     * 
     * @throws            Exception
     */
    public static function update_log_historical($log) {
        /* Variables */
        global $DB;
        
        try {
            foreach ($log as $instance) {
                $DB->insert_record('log_doskom_completions',$instance);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_log_historical
    
    /**
     * @param           $user_id
     * @param           $ticket
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Authenticate the user who is trying to log in
     */
    public static function authenticateUser($user_id,$ticket) {
        /* Variables */
        global $DB;
        $time = time();

        try {
            if ($user_id && $ticket) {
                /* Search Criteria  */
                $params = array();
                $params['script']   = 'doskom';
                $params['user_id']  = $user_id;
                $params['ticket']   = $ticket;
                $params['valid']    = $time;

                /* SQL Instruction  */
                $sql = " SELECT		upk.id
                         FROM		{user_private_key} upk
                         WHERE		upk.script      = :script
                            AND		upk.userid      = :user_id
                            AND		upk.value       = :ticket
                            AND		upk.validuntil  >= :valid ";

                /* Execute  */
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    return $rdo->id;
                }else {
                    return false;
                }//if_rdo
            }else {
                return false;
            }//if_else_params
        }catch(Exception $ex){
            throw $ex;
        }//try_catch
    }//authenticateUser

    /**
     * @param           $id
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the authenticate ticket for the user.
     */
    public static function deleteKey($id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $DB->delete_records('user_private_key',array('id' => $id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//deleteKey

    /************/
    /*  PRIVATE */
    /************/

    /**
     * @param           $userSSO
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Checks if another user already exists with the same username
     */
    private static function otherUser($userSSO) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params             = array();
            $params['username'] = $userSSO['ssn'];
            /* Person ID              */
            $params['secret']   = $userSSO['id'];

            /* SQL Instruction */
            $sql = " SELECT     u.id
                     FROM       {user} u
                     WHERE      u.username  = :username
                        AND     u.secret    != :secret
                        AND		u.secret	!= '' ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $a = new stdClass();
                $a->username = $userSSO['username'] . ' ' . $userSSO['ssn'];

                return html_to_text(get_string('exists_username','local_doskom',$a));
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//otherUser

    /**
     * @param           $userID
     * @param           $userSSO
     * @param           $result
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Checks if the user already exists
     *
     * @updateDate      21/04/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * The ssn es unique for each user. It has not to check the company
     */
    private static function checkUser($userID,$userSSO,&$result) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params             = array();
            $params['secret']   = $userID;
            $params['username'] = $userSSO['ssn'];
            $params['company']  = $userSSO['companyId'];

            /* SQL Instruction */
            $sql = " SELECT		u.id
                     FROM		{user} 	u ";

            /* First Only Personale Number  */
            $sqlTotal = $sql . " WHERE		u.username  = :username ";
            /* Execute  */
            $rdo = $DB->get_record_sql($sqlTotal,$params);
            if ($rdo) {
                return $rdo->id;
            }else {
                /* Second with secret   */
                $sqlTotal = $sql . " WHERE		u.secret  = :secret ";
                /* Execute  */
                $rdo = $DB->get_record_sql($sqlTotal,$params);
                if ($rdo) {
                    return $rdo->id;
                }else {
                    return false;
                }
            }//if_rdo
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' -- Function: checkUser';

            throw $ex;
        }//try_catch
    }//checkUser

    /**
     * @param           $user_id
     * @param           $userSSO
     * @param           $result
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the user
     */
    private static function updateUser($user_id,$userSSO,&$result) {
        /* Variables   */
        global $DB;

        try {
            /* Details to Update    */
            $instance = new stdClass();
            $instance->id           = $user_id;
            $instance->deleted      = 0;
            /* Secret   */
            $instance->secret       = $userSSO['id'];

            /* User Name    */
            $instance->username     = $userSSO['ssn'];
            /* Auth method  */
            $instance->auth         = 'saml';
            /* Password     */
            $instance->password     = 'not cached';

            /* Source   */
            $instance->source       = $userSSO['source'];

            /* First name   */
            $instance->firstname    = $userSSO['firstname'];
            /* Surname      */
            $instance->lastname     = $userSSO['lastname'];
            /* City         */
            $instance->email        = $userSSO['email'];
            /* Language */
            $instance->lang = 'no';
            if ($userSSO['lang']) {
                $instance->lang    = $userSSO['lang'];
            }//if_lang

            /* Personal Number  */
            $instance->idnumber = $userSSO['ssn'];

            /* Work Place --> Department    */
            if ($userSSO['workPlace']) {
                $instance->department  = $userSSO['workPlace'];
            }//if_work_place

            /* Execute - Update User    */
            $DB->update_record('user',$instance);

            /* Assign the company to the user   */
            self::assignCompanyUser($instance->id,$userSSO['companyId'],$userSSO['company']);

            return $instance->id;
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . '-- Function: updateUser';

            throw $ex;
        }//try_catch
    }//updateUser

    /**
     * @param           $userSSO
     * @param           $result
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new user
     */
    private static function createUser($userSSO,&$result) {
        /* Variables    */
        global $DB, $CFG;

        try {
            /* New User */
            $new_user = new stdClass();

            /* Username     */
            $new_user->username     = $userSSO['ssn'];
            /* Auth method  */
            $new_user->auth         = 'saml';
            /* Password     */
            $new_user->password     = 'not cached';

            /* Source   */
            $new_user->source = $userSSO['source'];

            /* First name   */
            $new_user->firstname    = $userSSO['firstname'];
            /* Last name    */
            $new_user->lastname     = $userSSO['lastname'];
            /* eMail        */
            $new_user->email        = $userSSO['email'];
            /* Lang */
            $new_user->lang = 'no';
            if ($userSSO['lang']) {
                $new_user->lang     = $userSSO['lang'];
            }//lang

            /* Personal Number  */
            $new_user->idnumber = $userSSO['ssn'];

            /* Work Place --> Department    */
            if ($userSSO['workPlace']) {
                $new_user->department  = $userSSO['workPlace'];
            }//if_work_place

            /* Identifier of user in Dossier Profile    */
            $new_user->secret       = $userSSO['id'];
            $new_user->confirmed    = '1';
            $new_user->firstaccess  = time();
            $new_user->timemodified = time();
            $new_user->mnethostid   = $CFG->mnet_localhost_id;

            /* Execute  */
            $new_user->id = $DB->insert_record('user',$new_user);

            /* Assign Company to User   */
            self::assignCompanyUser($new_user->id,$userSSO['companyId'],$userSSO['company']);

            return $new_user->id;
        }catch(Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . "-- Function: Create User";

            throw $ex;
        }//try_catch
    }//createUser

    /**
     * @param           $user_id
     * @param           $company_id
     * @param           $company_name
     * @return          bool
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Assign the company to the user
     */
    private static function assignCompanyUser($user_id,$company_id,$company_name) {
        /* Variables */
        global $DB;

        try {
            /* Check the user is already connected with */
            /* Search Criteria  */
            $params = array();
            $params['userid']       = $user_id;
            $params['companyid']    = $company_id;

            /* Get User Company */
            $rdo = $DB->get_record('user_company',$params,'id');
            if (!$rdo) {
                /* Check if the company already exists or must create it    */
                self::existCreateCompany($company_id,$company_name);

                /* Assign the company to the user   */
                $user_company = new stdClass();
                $user_company->userid       = $user_id;
                $user_company->companyid    = $company_id;
                $user_company->timecreated  = time();

                /* Execute  */
                $DB->insert_record('user_company',$user_company);
            }//if_rdo

            return true;
        }catch(Exception $ex) {
            return false;
        }//try_catch
    }//assignCompanyUser

    /**
     * @param           $company_id
     * @param           $company_name
     * @return          bool
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the company already exists. If the company not exits, will create it.
     */
    private static function existCreateCompany($company_id,$company_name) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params         = array();
            $params['id']   = $company_id;

            /* Execute  */
            $rdo = $DB->get_record('company_data',$params,'id');
            if (!$rdo) {
                /* Create the company   */
                $sql = " INSERT INTO {company_data} (id,name,timecreated)
                         VALUES ("  . $company_id     . ",'"
                                    . $company_name   . "',"
                                    . time()          . ") ";

                /* Execute  */
                $DB->execute($sql);
            }//if_not_exists

            return true;
        }catch(Exception $ex) {
            return false;
        }//try_catch
    }//existCreateCompany

    /**
     * @param           $user_id
     * @param           $rol
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Assign the correct to the user.
     */
    private static function assignRol($user_id,$rol,&$result) {
        /* Variables    */
        global $DB;

        try {
            switch ($rol) {
                case ROL_MANAGER:
                case ROL_CREATOR:
                    $archetype  = strtolower($rol);
                    $rdo_rol    = $DB->get_record('role',array('archetype' => $archetype),'id');
                    if ($rdo_rol) {
                        role_assign($rdo_rol->id, $user_id,1);
                    }//if_rdo_rol
                    break;
                default:
                    break;
            }//switch_rol
        }catch(Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' -- Function: assignRol';

            throw $ex;
        }//try_catch
    }//assignRol

    /**
     * @param           $user_id
     * @param           $company_id
     * @param           $rol
     * @param           $course_id
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Assign the rol of the student connected with the course and enrol to
     */
    private static function assignRolCourse($user_id,$company_id,$rol,$course_id,&$result) {
        /* Variables    */
        global $DB;

        try {
            switch ($rol) {
                case ROL_EDIT_TEACHER:
                case ROL_TEACHER:
                    self::enrolUser($user_id,$company_id,$course_id);
                    $archetype  = strtolower($rol);
                    $rdo_rol    = $DB->get_record('role',array('archetype' => $archetype),'id');
                    if ($rdo_rol) {
                        $context = CONTEXT_COURSE::instance($course_id);
                        role_assign($rdo_rol->id, $user_id,$context->id);
                    }//if_rdo_rol

                    break;
                case ROL_STUDENT:
                    $archetype  = strtolower($rol);
                    $rdo_rol    = $DB->get_record('role',array('archetype' => $archetype),'id');
                    if ($rdo_rol) {
                        $context = CONTEXT_COURSE::instance($course_id);
                        role_assign($rdo_rol->id, $user_id,$context->id);
                    }//if_rdo_rol

                    self::enrolUser($user_id,$company_id,$course_id);

                    break;
                default:
                    break;
            }//switch_rol
        }catch(Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' -- Function: assignRolCourse';
            throw $ex;
        }//try_catch
    }//assignRolCourse

    /**
     * @param           $user_id
     * @param           $company_id
     * @param           $course_id
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Enroll the user if he/she has not enrolled yet.
     */
    private static function enrolUser($user_id,$company_id,$course_id) {
        /* Variables    */
        global $DB;
        $plugin = null;

        try {
            /* Enrol WS_DOSKOM  */
            $plugin = enrol_get_plugin('wsdoskom');

            /* Enroll the user  */
            if (!self::isEnrolled($user_id,$course_id)) {

                /* Check if the enrol instance exists for the company   */
                $instance = self::getEnrolmentInstance($course_id,$company_id);

                if ($instance) {
                    /* Enrol User   */
                    $plugin->enrol_user($instance,$user_id,null,time());
                }//if_instance
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//enrolUser

    /**
     * @param           $user_id
     * @param           $course_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is Enrolled
     */
    private static function isEnrolled($user_id,$course_id) {
        /* Variables   */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course_id'] = $course_id;
            $params['user_id']   = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		ue.enrolid
                     FROM		{user_enrolments} 	ue
                        JOIN	{user}				u	ON 		u.id        = ue.userid
                                                        AND		u.id        = :user_id
                                                        AND		u.deleted   = 0
                        JOIN	{enrol}				e	ON 		e.id        = ue.enrolid
                                                        AND		e.courseid  = :course_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//isEnrolled

    /**
     * @param           $course_id
     * @param           $company_id
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    26/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the enrolment instance connected to the course and company
     */
    private static function getEnrolmentInstance($course_id,$company_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['enrol']    = 'wsdoskom';
            $params['course']   = $course_id;
            $params['company']  = $company_id;

            /* SQL Instruction  */
            $sql = " SELECT		e.*
                     FROM		{enrol}	e
                     WHERE		e.enrol		= :enrol
                        AND		e.courseid	= :course
                        AND		(
                                 e.company = :company
                                 OR
                                 e.company	LIKE '%,"   . $company_id . ",%'
                                 OR
                                 e.company  LIKE '"     . $company_id . ",%'
                                 OR
                                 e.company  LIKE '%,"   . $company_id . "'
                                ) ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getEnrolmentInstance

    /**
     * @param           $user_id
     * @param           $user_SSO
     * @param           $result
     * @return          string
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the response (url) from the system who wants to log in.
     */
    private static function generateResponse($user_id,$user_SSO,&$result) {
        /* Variables    */
        global $CFG;
        /* URL response of the Web Service  */
        $response       = null;
        $param_id       = null;
        $param_ticket   = null;
        $param_back     = null;

        try {
            /* Generate the key */
            $key = self::generateKey($user_id,$user_SSO['id']);

            /* Build the URL Response */
            $action     = strtolower($user_SSO['RedirectPage']);
            $action_url = self::getAction($action,$user_SSO['UserRoles'],$user_SSO['course']);
            $action_url = urlencode($action_url);
            $back       = urlencode($user_SSO['LogoutUrl']);
            $params     = '?id=' . $user_id . '&ticket=' . $key . '&RedirectPage=' . $action_url . '&LogoutUrl=' . $back;
            $response   =  urlencode($CFG->wwwroot . '/local/doskom/autologin.php' . $params);

            return $response ;
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . "-- Function: Generate Response";
            throw $ex;
        }//try_catch
    }//generateResponse

    /**
     * @param           $user_id
     * @param           $sso_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the authentication ticket for the user who wants to log in.
     * unique and long.
     */
    private static function generateKey($user_id,$sso_id) {
        /* Variables        */
        global $DB;
        /* Expiration Date  */
        $valid_until = time() + 60*5;
        /* Ticket User      */
        $ticket = null;
        /* Token            */
        $token = null;

        try {
            /* Ticket - Something long and Unique   */
            $token = uniqid(mt_rand(),1);
            $ticket = $sso_id . '_' . time() . '_' . $token . random_string();
            /* Key */
            $key = new stdClass();
            $key->script        = 'doskom';
            $key->userid        = $user_id;
            $key->validuntil    = $valid_until;
            $key->timecreated   = time();
            $key->value         = md5($ticket);
            while ($DB->record_exists('user_private_key', array('value' => $key->value))) {
                /* Ticket - Something long and Unique   */
                $token = uniqid(mt_rand(),1);
                $ticket = $sso_id . '_' . time() . '_' . $token . random_string();
                $key->value         = md5($ticket);
            }//while

            $DB->insert_record('user_private_key', $key);
            return $key->value;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generateKey

    /**
     * @param           $action
     * @param           $rol
     * @param           $course_id
     * @return          moodle_url
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the url action. Where the user must be redirected.
     */
    private static  function getAction($action,$rol,$course_id) {
        /* Variables    */
        global $CFG;
        /* URL Action   */
        $action_url = null;

        switch ($action) {
            case MAIN:
                /* Main Page    */
                $action_url = new moodle_url($CFG->wwwroot);
                break;
            case ENROL:
                /* Start Course */
                if ($course_id) {
                    $action_url = new moodle_url('/course/view.php',array('id' => $course_id));
                }else {
                    $action_url = new moodle_url($CFG->wwwroot);
                }//if_course

                break;
            case COURSES:
                if (($rol == ROL_CREATOR) || ($rol == ROL_MANAGER)) {
                    /* Create Courses   */
                    $action_url = new moodle_url('/course/management.php');
                }else {
                    $action_url = new moodle_url($CFG->wwwroot);
                }//if_rol

                break;
            default:
                $action_url = new moodle_url($CFG->wwwroot);
                break;
        }//switch_action

        return $action_url;
    }//getAction

    /**
     * @param           $user_id
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Deactivate a specific user.
     */
    private static function deactivateUser($user_id,&$result) {
        /* Variables    */
        global $DB;
        $instance = null;

        try {
            /* Deactivate the user  */
            $instance = new stdClass();
            $instance->id           = $user_id;
            $instance->deleted      = 1;
            $instance->timemodified = time();

            $DB->update_record('user',$instance);
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' -- Function: deactivateUser';
            throw $ex;
        }//try_catch
    }//deactivateUser

    /**
     * @param           $company
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Returns all the categories connected with
     */
    private static function getCategoryCompany($company) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company'] = $company;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT	ca.id,
                                            ca.name,
                                            ca.description,
                                            ca.parent
                     FROM		{enrol}					e
                        JOIN	{course}				c	ON	c.id 	= e.courseid
                        JOIN	{course_categories}		ca	ON	ca.id 	= c.category
                     WHERE		e.status        = 0
                        AND		(
                                  e.company 	    = :company
                                  OR
                                  e.company	 LIKE '%,"    . $company . ",%'
                                  OR
                                  e.company  LIKE '"     . $company . ",%'
                                  OR
                                  e.company  LIKE '%,"   . $company . "'
                                )
                     ORDER BY	ca.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCategoryCompany

    /**
     * @param           $company
     * @param           $category
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Returns all the courses connected with the category and company
     */
    private static function getCoursesCategoryCompany($company,$category) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company']  = $company;
            $params['category'] = $category;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT  c.id,
                                          c.fullname,
                                          c.summary
                     FROM		{course}	c
                        JOIN	{enrol}		e	ON 		e.courseid 	 = c.id
                                                AND		(
                                                          e.company    = :company
                                                          OR
                                                          e.company	LIKE '%,"    . $company . ",%'
                                                          OR
                                                          e.company  LIKE '"     . $company . ",%'
                                                          OR
                                                          e.company  LIKE '%,"   . $company . "'
                                                        )
                     WHERE		c.category = :category
                     ORDER BY 	c.fullname ASC";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCoursesCategoryCompany

    /**
     * Description
     * Get all the courses, which have to be completed, for a specific company.
     * 
     * @creationDate    04/01/2017
     * @author          eFaktor     (fbv)
     * 
     * @param       String $company
     *
     * @return             array|null
     * @throws             Exception
     */
    private static function get_courses_company_to_complete($company) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['company'] = $company;

            // SQL Instruction
            $sql = " SELECT  DISTINCT 
                                c.id,
                                c.fullname
                     FROM		{course_completions}	cc
                        JOIN	{user_company}		    uc	ON	uc.userid		= cc.userid
                                                            AND	uc.companyid 	= :company
                        JOIN 	{course}				c 	ON c.id             = cc.course
                     ORDER BY c.fullname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_courses_company_to_complete

    /**
     * Description
     * It builds the SQL Instruction to get the users have been completed a course given.
     *
     * @creationDate        04/01/2017
     * @author              eFaktor     (fbv)
     *
     * @param       String $date_from
     * @param       String $date_to
     * @param       String $company
     *
     * @return      null|string
     * @throws      Exception
     */
    private static function get_sql_users_completions_in_period($date_from,$date_to,$company) {
        /* Variables */
        $sql    = null;
        $from   = null;
        $to     = null;
        $secret = null;

        try {
            // Secret criteria
            $secret = $company . '##SEP##';

            // Date range - Formatting dates
            $from = DateTime::createFromFormat('Y.m.d H:i:s',$date_from . ' 00:00:00');
            $to   = DateTime::createFromFormat('Y.m.d H:i:s',$date_to . ' 23:59:59');

            // SQL Instruction
            $sql = " SELECT   cc.id,
                              u.id as 'user',
                              u.secret,
                              FROM_UNIXTIME(cc.timecompleted,'%Y.%m.%d')as 'completiondate',
                              cc.timecompleted
                     FROM	  {course_completions}	cc
                        -- USERS DOSSSIER
                        JOIN  {user}				u	  ON  u.id 		= cc.userid
                                                          AND u.deleted	= 0
                                                          AND u.auth	= 'saml'
                                                          AND u.source  IN ('DOSKOM','KOMMIT')
                                                          AND u.secret	LIKE '"   . $secret . "%'
                     WHERE	  cc.course = :course
                        AND	  cc.timecompleted BETWEEN " . $from->getTimestamp() . " AND " . $to->getTimestamp();

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sql_users_completions_in_period
}//WS_DOSKOM
