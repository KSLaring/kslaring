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
 * Web services DOSKOM library
 *
 * @package         local
 * @subpackage      doskom/lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      11/09/2017
 * @author          eFaktor     (fbv)
 *
 */

// Constants for Redirect Page
define ('MAIN','main');
define ('ENROL','enrol');
define ('COURSES','courses');
// Constants for UserRoles
define ('ROL_MANAGER','manager');
define ('ROL_CREATOR','coursecreator');
define ('ROL_EDIT_TEACHER','editingteacher');
define ('ROL_TEACHER','teacher');
define ('ROL_STUDENT','student');


class wsdoskom {
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
    public static function write_log($log) {
        /* Variables */
        global $DB;
        $info = null;

        try {
            if ($log) {
                foreach ($log as $info) {
                    $DB->insert_record('doskom_log',$info);
                }//for_log
            }//if_log

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//write_log

    /**
     * Description
     * Authenticate the user who is trying to log in
     *
     * @param           $userid
     * @param           $ticket
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    public static function authenticate_user($userid,$ticket) {
        /* Variables */
        global $DB;
        $time   = time();
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            if ($userid && $ticket) {
                // Search criteria
                $params = array();
                $params['script']   = 'doskom';
                $params['user_id']  = $userid;
                $params['ticket']   = $ticket;
                $params['valid']    = $time;

                // SQL Instruction
                $sql = " SELECT		upk.id
                         FROM		{user_private_key} upk
                         WHERE		upk.script      = :script
                            AND		upk.userid      = :user_id
                            AND		upk.value       = :ticket
                            AND		upk.validuntil  >= :valid ";

                // Execute
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
    }//authenticate_user

    /**
     * Description
     * Delete the authenticate ticket for the user.
     *
     * @param           $id
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    public static function delete_key($id) {
        /* Variables    */
        global $DB;

        try {
            // Execute
            $DB->delete_records('user_private_key',array('id' => $id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//delete_key

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
    public static function get_company_list() {
        /* Variables    */
        global $DB;
        $lstcompanies   = null;
        $company        = null;


        try {
            // Company list. First element
            $lstcompanies = array();
            $lstcompanies[0] = get_string('sel_company','local_doskom');

            // Get companies
            $rdo = $DB->get_records('company_data',null,'name ASC','id,name');
            if ($rdo) {
                foreach ($rdo as $company) {
                    $lstcompanies[$company->id] = $company->name;
                }//for_company
            }//if_rdo

            return $lstcompanies;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_company_list

    /**
     * @param           $userSSO
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Log in the user that comes from other system
     */
    public static function log_in_user($userSSO,&$result,&$log) {
        /* Variables    */
        $url        = null;
        $userid     = null;
        $action     = null;
        $infolog    = null;
        $company    = null;
        $time       = null;


        try {
            // Local time
            $time = time();

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action       = 'wsLogInUser';
            $infolog->description  = 'User : ' . $userSSO['ssn'];
            $infolog->timecreated  = $time;
            // Add log
            $log[] = $infolog;

            // Get company connected with the user
            // Does not exist the company --> user will be not created
            $company = self::get_company($userSSO['companyId']);
            if ($company) {
                // Check if the user already exists
                $userid = self::check_user($userSSO['id'],$userSSO,$result);
                if ($userid) {
                    // Update user
                    self::update_user($userid,$userSSO,$company->label,$result);
                }else {
                    // Create new user
                    $userid = self::create_user($userSSO,$company->label,$result,$log);
                }//if_user_exist

                // DOSKOM log
                $infolog = new stdClass();
                $infolog->action      = 'wsLogInUser';
                $infolog->description = 'User created/Updated. USer --> ' . $userSSO['ssn'];
                $infolog->timecreated = $time;
                // Add log
                $log[] = $infolog;

                // Add gender
                if (is_numeric($userSSO['ssn']) && ($userSSO['ssn']) == 11) {
                    Gender::Add_UserGender($userid,$userSSO['ssn']);

                    // DOSKOM log
                    $infolog = new stdClass();
                    $infolog->action      = 'wsLogInUser';
                    $infolog->description = 'Gender added for user --> ' . $userSSO['ssn'];
                    $infolog->timecreated = $time;
                    // Add log
                    $log[] = $infolog;
                }

                // Assing rol
                self::assign_rol($userid,$userSSO['UserRoles'],$result,$log);

                // Check if the user has been enrolled or not
                $action = strtolower($userSSO['RedirectPage']);
                if (($action == ENROL) && ($userSSO['course'])) {
                    self::assign_rol_course($userid,$userSSO['companyId'],$userSSO['UserRoles'],$userSSO['course'],$result,$log);
                }//if_Action_enrol

                // We need to generate  the URL
                $url = self::generate_response($userid,$userSSO,$result);

                // DOSKOM log
                $infolog = new stdClass();
                $infolog->action      = 'wsLogInUser';
                $infolog->description = 'Generate response. user ' . $userSSO['ssn'] . " company " . $company->id;
                $infolog->timecreated = $time;
                // Add log
                $log[] = $infolog;

                $result['url'] = $url;
            }else {
                // DOSKOM log
                $infolog = new stdClass();
                $infolog->action       = 'wsLogInUser';
                $infolog->description  = 'ERROR. Company ' . $userSSO['companyId'] . 'does not exits. So, the user cannot be connected with and log in';
                $infolog->description .= 'User : ' . $userSSO['ssn'];
                $infolog->timecreated  = $time;
                // Add log
                $log[] = $infolog;

                $result['error']        = 409;
                $result['msg_error']    = 'Company does not exits. So, the user cannot be connected with and log in';
            }
        }catch (Exception $ex) {
            throw $ex;
        }//Try_catch
    }//log_in_user

    /**
     * Description
     * Deactivate all the users from the list.
     *
     * @param           $userlst
     * @param           $result
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    public static function deactivate_users($userlst,&$result,&$log) {
        /* Variables    */
        $userid     = null;
        $secret     = null;
        $usersso    = null;
        $infolog    = null;

        try {
            // Deactivate the users one by one
            foreach($userlst as $usersso) {
                // DOSKOM log
                $infolog = new stdClass();
                $infolog->action      = 'wsDeActivateUser';
                $infolog->description = 'Deactivate user : ' . $usersso['id'];
                $infolog->timecreated = time();
                // Add log
                $log[] = $infolog;

                // Check if the user exists
                $userid = self::check_user($usersso['id'],$usersso,$result);
                if ($userid) {
                    self::deactivate_user($userid,$result);

                    // DOSKOM log
                    $infolog = new stdClass();
                    $infolog->action      = 'wsDeActivateUser';
                    $infolog->description = 'Deactivate user. User deactivated --> user : ' . $usersso['id'];
                    $infolog->timecreated = time();
                    // Add log
                    $log[] = $infolog;
                }//if_user_id
            }//for_users
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . "-- Function: Deactivate Users";
            throw $ex;
        }//try_catch
    }//deactivate_users

    /**
     * Description
     * Return the course catalog connected with
     *
     * @param           $company
     * @param           $result
     * @param           $log
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_course_catalog($company,&$result,&$log) {
        /* Variables */
        $infolog    = null;
        $catalog    = null;
        $categories = null;
        $cat        = null;
        $category   = null;
        $rdocourses = null;
        $lstcourses = null;
        $course     = null;
        $catlog     = array();
        $infocatlog =  null;
        $time       = null;

        try {
            // Local time
            $time = time();

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsGetCourseCatalog';
            $infolog->description = 'Course catalog for company : ' . $company;
            $infolog->timecreated = $time;
            // Add log
            $log[] = $infolog;

            // Course catalog
            $catalog = array();
            // all the categories connected with the company
            $categories = self::get_category_company($company);

            if ($categories) {
                foreach ($categories as $cat) {
                    // Category instance
                    $category = array();
                    $category['categoryId']     = $cat->id;
                    $category['categoryName']   = $cat->name;
                    $category['categoryDesc']   = html_to_text($cat->description);
                    $category['categoryParent'] = $cat->parent;
                    $category['courses']        = array();

                    // Get courses connected with
                    $rdocourses = self::get_courses_category_company($company,$cat->id);
                    if ($rdocourses) {
                        $lstcourses= array();
                        foreach ($rdocourses as $instance) {
                            // Course instance
                            $course = new stdClass();
                            $course->courseId       = $instance->id;
                            $course->courseName     = $instance->fullname;
                            $course->courseSummary  = html_to_text($instance->summary);
                            $course->courseForm     = 'Online';

                            // Add course
                            $lstcourses[] = $course;

                            // Info catalog log
                            $infocatlog = new stdClass();
                            $infocatlog->companyid  = $company;
                            $infocatlog->categoryid = $cat->id;
                            $infocatlog->catname    = $cat->name;
                            $infocatlog->courseid   = $instance->id;
                            $infocatlog->coname     = $instance->fullname;
                            $infocatlog->timesend   = $time;
                            // Add to log
                            $catlog[] = $infocatlog;
                        }//for_courses

                        $category['courses'] = $lstcourses;
                    }else {
                        // DOSKOM log
                        $infolog = new stdClass();
                        $infolog->action      = 'wsGetCourseCatalog';
                        $infolog->description = 'No courses for Category: ' . $cat->name . ' Company : ' . $company;
                        $infolog->timecreated = $time;
                        // Add log
                        $log[] = $infolog;
                    }//if_rdo_courses

                    $catalog[] = $category;
                }//for_categories
            }else {
                // DOSKOM log
                $infolog = new stdClass();
                $infolog->action      = 'wsGetCourseCatalog';
                $infolog->description = 'None categories. No courses. Company : ' . $company;
                $infolog->timecreated = time();
                // Add log
                $log[] = $infolog;
            }//if_category_list

            return array($catalog,$catlog);
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . " -- Function: Course Catalog";

            throw $ex;
        }//try_catch
    }//get_course_catalog

    /**
     * Description
     * Get the historical of the completed courses.
     *
     * @param           $criteria
     * @param           $result
     * @param           $log
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_historical_courses_completion($criteria,&$result,&$log){
        /* Variables */
        global $DB;
        $rdocourses     = null;
        $rdousers       = null;
        $courses        = null;
        $users          = null;
        $infouser       = null;
        $time           = null;
        $historical     = array();
        $historicallog  = array();
        $infoLog        = null;
        $params         = null;

        try {
            // Local time
            $time = time();

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsGetAccomplishedCourses';
            $infolog->description = 'Course completion historical for company: ' . $criteria['companyId'];
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            // Get data company
            $company = self::get_company($criteria['companyId']);
            if ($company) {
                // first get courses
                $rdocourses = self::get_courses_company_to_complete($company->id);
                if ($rdocourses) {
                    // Search criteria
                    $params = array();
                    $params['label'] = $company->label;

                    // Get sql instruction
                    $sql = self::get_sql_users_completions_in_period($criteria['dateFrom'],$criteria['dateTo'],$criteria['companyId']);
                    foreach ($rdocourses as $course) {
                        // Course criteria
                        $params['course'] = $course->id;

                        // Users have been completed
                        $rdousers = $DB->get_records_sql($sql,$params);
                        if ($rdousers) {
                            // Users
                            $users = array();
                            foreach ($rdousers as $instance) {
                                $infouser = new stdClass();
                                $infouser->completionId     = $instance->id;
                                $infouser->userId           = $instance->secret;
                                $infouser->completionDate   = $instance->completiondate;

                                $users[$instance->id] = $infouser;

                                // Log
                                $infoLog = new stdClass();
                                $infoLog->company       = $criteria['companyId'];
                                $infoLog->course        = $course->id;
                                $infoLog->user          = $instance->user;
                                $infoLog->completion    = $instance->timecompleted;
                                $infoLog->timesent      = $time;

                                // Add to the log
                                $historicallog[] = $infoLog;
                            }//for_users

                            // Add course with course completions
                            if ($users) {
                                $courses = array();
                                $courses['courseId']    = $course->id;
                                $courses['courseName']  = $course->fullname;
                                $courses['users']       = $users;

                                // Historical
                                $historical[]     = $courses;
                            }else {
                                $infolog = new stdClass();
                                $infolog->action      = 'wsGetAccomplishedCourses';
                                $infolog->description = 'NO USERS for course .' . $course->fullname . '(' . $course->id . ') .  and company: ' . $company->id;
                                $infolog->timecreated = time();
                                // Add log
                                $log[] = $infolog;
                            }//if_users
                        }//if_rdousers
                    }//for_$rdocourses
                }else {
                    // DOSKOM log
                    $infolog = new stdClass();
                    $infolog->action      = 'wsGetAccomplishedCourses';
                    $infolog->description = 'ERROR company : ' . $criteria['companyId'] . " does not exists";
                    $infolog->timecreated = time();
                    // Add log
                    $log[] = $infolog;
                }//if_$rdocourses
            }else {
                $infolog = new stdClass();
                $infolog->action      = 'wsGetAccomplishedCourses';
                $infolog->description = 'NO COURSES for company: ' . $company->id;
                $infolog->timecreated = time();
                // Add log
                $log[] = $infolog;

            }

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsGetAccomplishedCourses';
            $infolog->description = 'Finish. Course completion historical for company: ' . $criteria['companyId'];
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            return array($historical,$historicallog);
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . " -- Function: Historical Course COmpletion";

            throw $ex;
        }//try_catch
    }//get_historical_courses_completion

    /**
     * Description
     * Save log of historical completions sent to dossier
     *
     * @param       array $log
     *
     * @throws            Exception
     *
     * @creationDate      29/01/2017
     * @author            eFaktor     (fbv)
     */
    public static function update_log_historical($log) {
        /* Variables */
        global $DB;
        $instance = null;

        try {
            foreach ($log as $instance) {
                $DB->insert_record('log_doskom_completions',$instance);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_log_historical

    /**
     * Description
     * Save course catalog sent. log
     *
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    13/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function add_catalog_log($log) {
        /* Variables */
        global $DB;
        $info = null;

        try {
            foreach ($log as $info) {
                $DB->insert_record('doskom_catalog_log',$info);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_catalog_log


    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get data connected with the company
     *
     * @param           $companyid
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    11/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_company($companyid) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;
        $sql    = null;

        try {
            // Search criteria
            $params = array();
            $params['id'] = $companyid;

            // SQL Instruction
            $sql = " SELECT	  cd.id,
                              cd.user,
                              cd.token,
                              dk.api,
                              dk.label
                     FROM	  {company_data}	cd
                        JOIN  {doskom_company}  dkco  ON  dkco.companyid = cd.id
                                                      AND dkco.active    = 1
                        JOIN  {doskom}		    dk	  ON  dk.id          = dkco.doskomid
                     WHERE    cd.id = :id ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_company

    /**
     * Description
     * Get all the courses, which have to be completed, for a specific company.
     *
     * @param           String $company
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    04/01/2017
     * @author          eFaktor     (fbv)
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

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_courses_company_to_complete

    /**
     * Description
     * It builds the SQL Instruction to get the users have been completed a course given.
     *
     * @param       String $datefrom
     * @param       String $dateto
     * @param       String $company
     *
     * @return      null|string
     * @throws      Exception
     *
     * @creationDate        04/01/2017
     * @author              eFaktor     (fbv)
     */
    private static function get_sql_users_completions_in_period($datefrom,$dateto,$company) {
        /* Variables */
        $sql    = null;
        $from   = null;
        $to     = null;
        $secret = null;

        try {
            // Secret criteria
            $secret = $company . '##SEP##';

            // Date range - Formatting dates
            $from = DateTime::createFromFormat('Y.m.d H:i:s',$datefrom . ' 00:00:00');
            $to   = DateTime::createFromFormat('Y.m.d H:i:s',$dateto . ' 23:59:59');

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
                                                          AND u.source  = :label
                                                          AND u.secret	LIKE '"   . $secret . "%'
                     WHERE	  cc.course = :course
                        AND	  cc.timecompleted BETWEEN " . $from->getTimestamp() . " AND " . $to->getTimestamp();

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sql_users_completions_in_period

    /**
     * Description
     * Returns all the categories connected with
     *
     * @param           $company
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_category_company($company) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['company'] = $company;

            // SQL Instruction
            $sql = " SELECT	DISTINCT	
                                ca.id,
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

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_category_company

    /**
     * Description
     * Returns all the courses connected with the category and company
     *
     * @param           $company
     * @param           $category
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_courses_category_company($company,$category) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql = null;

        try {
            // Search criteria
            $params = array();
            $params['company']  = $company;
            $params['category'] = $category;

            // SQL Instruction
            $sql = " SELECT	  DISTINCT  
                                c.id,
                                c.fullname,
                                c.summary
                     FROM	  {course}	c
                        JOIN  {enrol}	e	ON 		e.courseid 	 = c.id
                                            AND		(
                                                      e.company    = :company
                                                      OR
                                                      e.company	LIKE '%,"    . $company . ",%'
                                                      OR
                                                      e.company  LIKE '"     . $company . ",%'
                                                      OR
                                                      e.company  LIKE '%,"   . $company . "'
                                                    )
                     WHERE	  c.category = :category
                     ORDER BY c.fullname ASC";

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
    }//get_courses_category_company

    /**
     * Description
     * The ssn es unique for each user. It has not to check the company
     * @param           $userID
     * @param           $userSSO
     * @param           $result
     *
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
     */
    private static function check_user($userID,$userSSO,&$result) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $sqltotal   = null;

        try {
            // Search Criteria
            $params             = array();
            $params['secret']   = $userID;
            $params['username'] = $userSSO['ssn'];
            $params['company']  = $userSSO['companyId'];

            // SQL Instruction
            $sql = " SELECT		u.id
                     FROM		{user} 	u 
                     WHERE      u.username NOT IN ('guest','admin') ";

            // First, only personal number
            $sqltotal = $sql . " AND		u.username  = :username ";
            // Execute
            $rdo = $DB->get_record_sql($sqltotal,$params);
            if ($rdo) {
                return $rdo->id;
            }else {
                // After, secret
                $sqltotal = $sql . " AND	u.secret  = :secret ";
                // Execute
                $rdo = $DB->get_record_sql($sqltotal,$params);
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
    }//check_user

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
    private static function update_user($user_id,$userSSO,$label,&$result) {
        /* Variables   */
        global $DB;
        $instance = null;

        try {
            // Data to update
            $instance = new stdClass();
            $instance->id           = $user_id;
            $instance->deleted      = 0;
            $instance->secret       = $userSSO['id'];
            $instance->username     = $userSSO['ssn'];
            $instance->auth         = 'saml';
            $instance->password     = 'not cached';
            $instance->firstname    = $userSSO['firstname'];
            $instance->lastname     = $userSSO['lastname'];
            $instance->email        = $userSSO['email'];
            // Personal number
            $instance->idnumber     = $userSSO['ssn'];
            // Source is connected with the company
            $instance->source       = $label;
            // Language
            $instance->lang = 'no';
            if ($userSSO['lang']) {
                $instance->lang    = $userSSO['lang'];
            }//if_lang
            // Workplace --> department
            if ($userSSO['workPlace']) {
                $instance->department  = $userSSO['workPlace'];
            }//if_work_place

            // Execute
            $DB->update_record('user',$instance);

            // Assign the company to the user
            self::assign_company_user($instance->id,$userSSO['companyId']);

            return $instance->id;
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . '-- Function: updateUser';

            throw $ex;
        }//try_catch
    }//update_user

    /**
     * Description
     * Create a new user
     *
     * @param           $userSSO
     * @param           $label
     * @param           $result
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function create_user($userSSO,$label,&$result,&$log) {
        /* Variables    */
        global $DB, $CFG;
        $newuser = null;

        try {
            // New user
            $newuser = new stdClass();
            $newuser->username      = $userSSO['ssn'];
            $newuser->auth          = 'saml';
            $newuser->password      = 'not cached';
            $newuser->source        = $label;
            $newuser->firstname     = $userSSO['firstname'];
            $newuser->lastname      = $userSSO['lastname'];
            $newuser->email         = $userSSO['email'];
            $newuser->confirmed     = '1';
            $newuser->firstaccess   = time();
            $newuser->timemodified  = time();
            $newuser->mnethostid    = $CFG->mnet_localhost_id;
            // Personal number
            $newuser->idnumber      = $userSSO['ssn'];
            // Identifier of user in Dossier Profile
            $newuser->secret        = $userSSO['id'];
            // Language
            $newuser->lang = 'no';
            if ($userSSO['lang']) {
                $newuser->lang      = $userSSO['lang'];
            }//lang
            // Workplace --> department
            if ($userSSO['workPlace']) {
                $newuser->department  = $userSSO['workPlace'];
            }//if_work_place

            // DOSKOM log

            $infolog = new stdClass();
            $infolog->action      = 'wsLogInUser';
            $infolog->description = ' username : ' . $newuser->username ;
            $infolog->description .= ', auth : saml';
            $infolog->description .= ', password : not cached';
            $infolog->description .= ', source:' . $label;
            $infolog->description .= ', firstname: ' . $newuser->firstname;
            $infolog->description .= ', lastname: ' . $newuser->lastname;
            $infolog->description .= ', email: ' . $newuser->email;
            $infolog->description .= ', confirmed: 1';
            $infolog->description .= ', firstaccess: ' . $newuser->firstaccess;
            $infolog->description .= ', timemodified: ' . $newuser->timemodified;
            $infolog->description .= ', mnethostid: ' . $newuser->mnethostid;
            $infolog->description .= '. idnumber : ' . $newuser->idnumber;
            $infolog->description .= ', secret: ' . $newuser->secret;
            $infolog->description .= ', lang: ' . $newuser->lang;
            $infolog->description .= ', department: ' . $newuser->department;
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;

            // Execute
            $newuser->id = $DB->insert_record('user',$newuser);

            // Assign company to the user
            self::assign_company_user($newuser->id,$userSSO['companyId']);

            return $newuser->id;
        }catch(Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . "-- Function: Create User";

            throw $ex;
        }//try_catch
    }//create_user

    /**
     * Description
     * Assign the company to the user
     *
     * @param           $userid
     * @param           $companyid
     *
     * @return          bool
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function assign_company_user($userid,$companyid) {
        /* Variables */
        global $DB;
        $params      = null;
        $rdo         = null;
        $usercompany = null;

        try {
            // Search criteria
            $params = array();
            $params['userid']       = $userid;
            $params['companyid']    = $companyid;

            // Check the user is already connected with
            $rdo = $DB->get_record('user_company',$params,'id');
            if (!$rdo) {
                // Assign the company to the user
                $usercompany = new stdClass();
                $usercompany->userid       = $userid;
                $usercompany->companyid    = $companyid;
                $usercompany->timecreated  = time();

                // Execute
                $DB->insert_record('user_company',$usercompany);
            }//if_rdo

            return true;
        }catch(Exception $ex) {
            return false;
        }//try_catch
    }//assign_company_user

    /**
     * Description
     * Assign the correct to the user.
     *
     * @param           $userid
     * @param           $rol
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function assign_rol($userid,$rol,&$result,&$log) {
        /* Variables    */
        global $DB;
        $archetype  = null;
        $rdo        = null;
        $infolog    = null;

        try {
            switch ($rol) {
                case ROL_MANAGER:
                case ROL_CREATOR:
                    $archetype  = strtolower($rol);
                    $rdo        = $DB->get_record('role',array('archetype' => $archetype),'id');
                    if ($rdo) {
                        role_assign($rdo->id, $userid,1);

                        // DOSKOM log
                        $infolog = new stdClass();
                        $infolog->action      = 'wsLogInUser';
                        $infolog->description = 'Rol assigned.User --> ' . $userid . " ROL: " . $rol;
                        $infolog->timecreated = time();
                        // Add log
                        $log[] = $infolog;
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
    }//assign_rol

    /**
     * @param           $userid
     * @param           $companyid
     * @param           $rol
     * @param           $courseid
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Assign the rol of the student connected with the course and enrol to
     */
    private static function assign_rol_course($userid,$companyid,$rol,$courseid,&$result,&$log) {
        /* Variables    */
        global $DB;
        $archetype  = null;
        $rdo        = null;
        $infolog    = null;
        $time       = null;

        try {
            // Local time
            $time = time();

            switch ($rol) {
                case ROL_EDIT_TEACHER:
                case ROL_TEACHER:
                    self::enrol_user($userid,$companyid,$courseid,$log);
                    $archetype  = strtolower($rol);
                    $rdo        = $DB->get_record('role',array('archetype' => $archetype),'id');
                    if ($rdo) {
                        $context = CONTEXT_COURSE::instance($companyid);
                        role_assign($rdo->id, $userid,$context->id);

                        // DOSKOM log
                        $infolog = new stdClass();
                        $infolog->action      = 'wsLogInUser';
                        $infolog->description = 'Assign rol course. rol : ' . $rol . " user : " . $userid . " company " . $companyid . " course " . $courseid;
                        $infolog->timecreated = $time;
                        // Add log
                        $log[] = $infolog;
                    }//if_rdo_rol

                    break;
                case ROL_STUDENT:
                    $archetype  = strtolower($rol);
                    $rdo        = $DB->get_record('role',array('archetype' => $archetype),'id');
                    if ($rdo) {
                        $context = CONTEXT_COURSE::instance($courseid);
                        role_assign($rdo->id, $userid,$context->id);

                        // DOSKOM log
                        $infolog = new stdClass();
                        $infolog->action      = 'wsLogInUser';
                        $infolog->description = 'Assign rol course. rol : ' . $rol . " user : " . $userid . " company " . $companyid . " course " . $courseid;
                        $infolog->timecreated = $time;
                        // Add log
                        $log[] = $infolog;
                    }//if_rdo_rol

                    self::enrol_user($userid,$companyid,$courseid,$log);

                    break;
                default:
                    break;
            }//switch_rol
        }catch(Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' -- Function: assignRolCourse';
            throw $ex;
        }//try_catch
    }//assign_rol_course

    /**
     * Description
     * Enroll the user if he/she has not enrolled yet.
     *
     * @param           $userid
     * @param           $companyid
     * @param           $courseid
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function enrol_user($userid,$companyid,$courseid,&$log) {
        /* Variables    */
        $plugin = null;

        try {
            // wsdoskom enrolment
            $plugin = enrol_get_plugin('wsdoskom');
            if ($plugin) {
                // Check if the enrol instance exists for the company
                $instance = self::get_enrolment_instance($courseid,$companyid);
                if ($instance) {
                    // Enrol user
                    $plugin->enrol_user($instance,$userid,null,time());

                    // DOSKOM log
                    $infolog = new stdClass();
                    $infolog->action      = 'wsLogInUser';
                    $infolog->description = 'Enrol user ' . $userid . " company " . $companyid . " course " . $courseid;
                    $infolog->timecreated = time();
                    // Add log
                    $log[] = $infolog;
                }//if_instance
            }//if_plugin
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//enrolUser

    /**
     * Description
     * Get the enrolment instance connected to the course and company
     *
     * @param           $courseid
     * @param           $companyid
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    26/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_enrolment_instance($courseid,$companyid) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['enrol']    = 'wsdoskom';
            $params['course']   = $courseid;
            $params['company']  = $companyid;

            // SQL Instruction
            $sql = " SELECT		e.*
                     FROM		{enrol}	e
                     WHERE		e.enrol		= :enrol
                        AND		e.courseid	= :course
                        AND		(
                                 e.company = :company
                                 OR
                                 e.company	LIKE '%,"   . $companyid . ",%'
                                 OR
                                 e.company  LIKE '"     . $companyid . ",%'
                                 OR
                                 e.company  LIKE '%,"   . $companyid . "'
                                ) ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_enrolment_instance

    /**
     * Description
     * Generate the response (url) from the system who wants to log in.
     *
     * @param           $userid
     * @param           $userSSO
     * @param           $result
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function generate_response($userid,$userSSO,&$result) {
        /* Variables    */
        global $CFG;
        // URL response of the Web Service
        $response       = null;
        $key            = null;
        $back           = null;
        $params         = null;
        $action         = null;
        $acturl         = null;
        $infolog        = null;

        try {
            // Generate the key
            $key = self::generate_key($userid,$userSSO['id']);

            // Build the URL Response
            $action     = strtolower($userSSO['RedirectPage']);
            $acturl     = self::get_action($action,$userSSO['UserRoles'],$userSSO['course']);
            $acturl     = urlencode($acturl);
            $back       = urlencode($userSSO['LogoutUrl']);
            $params     = '?id=' . $userid . '&ticket=' . $key . '&RedirectPage=' . $acturl . '&LogoutUrl=' . $back;
            $response   =  urlencode($CFG->wwwroot . '/local/doskom/autologin.php' . $params);

            return $response ;
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . "-- Function: Generate Response";
            throw $ex;
        }//try_catch
    }//generate_response

    /**
     * Description
     * Get the url action. Where the user must be redirected.
     *
     * @param           $action
     * @param           $rol
     * @param           $courseid
     *
     * @return          moodle_url
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static  function get_action($action,$rol,$courseid) {
        /* Variables    */
        global $CFG;
        $acturl = null;

        switch ($action) {
            case MAIN:
                // Main page
                $acturl = new moodle_url($CFG->wwwroot);

                break;
            case ENROL:
                if ($courseid) {
                    // Start course
                    $acturl = new moodle_url('/course/view.php',array('id' => $courseid));
                }else {
                    // Main page
                    $acturl = new moodle_url($CFG->wwwroot);
                }//if_course

                break;
            case COURSES:
                if (($rol == ROL_CREATOR) || ($rol == ROL_MANAGER)) {
                    // Create courses
                    $acturl = new moodle_url('/course/management.php');
                }else {
                    // Main page
                    $acturl = new moodle_url($CFG->wwwroot);
                }//if_rol

                break;
            default:
                // Main page
                $acturl = new moodle_url($CFG->wwwroot);

                break;
        }//switch_action

        return $acturl;
    }//get_action

    /**
     * Description
     * Create the authentication ticket for the user who wants to log in.
     * unique and long.
     *
     * @param           $userid
     * @param           $ssoid
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function generate_key($userid,$ssoid) {
        /* Variables        */
        global $DB;
        $validuntil = time() + 60*5;
        $ticket     = null;
        $token  = null;

        try {
            // Ticket. Something long and unique
            $token = uniqid(mt_rand(),1);
            $ticket = $ssoid . '_' . time() . '_' . $token . random_string();
            // Key
            $key = new stdClass();
            $key->script        = 'doskom';
            $key->userid        = $userid;
            $key->validuntil    = $validuntil;
            $key->timecreated   = time();
            $key->value         = md5($ticket);
            while ($DB->record_exists('user_private_key', array('value' => $key->value))) {
                // Ticket. Something long and unique
                $token      = uniqid(mt_rand(),1);
                $ticket     = $ssoid . '_' . time() . '_' . $token . random_string();
                $key->value = md5($ticket);
            }//while

            $DB->insert_record('user_private_key', $key);
            return $key->value;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generate_key


    /**
     * Description
     * Deactivate a specific user.
     *
     * @param           $userid
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function deactivate_user($userid,&$result) {
        /* Variables    */
        $instance = null;
        $user     = null;

        try {
            // Deactivate user
            $user = get_complete_user_data('id',$userid);
            delete_user($user);
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' -- Function: deactivateUser';
            throw $ex;
        }//try_catch
    }//deactivate_user
}//wsdoskom