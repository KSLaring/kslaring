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
 * Report Manager -  Cron
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/cron
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/05/2017
 * @author          eFaktor     (fbv)
 *
 */
class Manager_Cron {
    const maxrecords = 25;//100000;

    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    public static function cron() {
        /* Variables */
        global $CFG;
        $gender = null;
        $total  = null;
        $dbLog  = null;

        try {
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Views. ' . "\n";

            // Get gender filed id
            $gender = self::get_gender_fieldid();

            // Create view profile
            self::view_profile($gender);

            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' User profile view. ' . "\n";

            // User course view
            // Get if there are courses where extract data connected with the report
            $total = self::get_total_users_course_view();
            if ($total) {
                // Get content of the view
                self::set_content_users_course_view();
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish User course view. ' . "\n";
            }//if_total

            // view_companies_with_users
            self::view_companies_with_users();
            // view_course_company_user_enrol
            self::view_course_company_user_enrol();

            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Views. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/rpt_manager.log");
        }catch (Exception $ex) {
            $dbLog .= " FINISH ERROR " . "\n";
            $dbLog .= " ERROR : " . $ex->getTraceAsString() . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/rpt_manager.log");

            throw $ex;
        }//try_catch
    }//CRON

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * Description
     * Create companies_with_users view
     *
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function view_companies_with_users() {
        /* Variables */
        global $DB;
        $view       = null;
        $sql        = null;

        try {
            // SQL for the view
            $sql = " SELECT DISTINCT
                              co.id 		  AS 'levelthree',
                              cr_tre.parentid AS 'leveltwo',
                              cr_two.parentid AS 'levelone',
                              cr_one.parentid AS 'levelzero',
                              co.industrycode AS 'industrycode'
                     FROM 	  {report_gen_companydata} co
                        JOIN  {user_info_competence_data} 	uic 	ON uic.companyid 	= co.id
                        JOIN  {report_gen_company_relation} cr_tre  ON cr_tre.companyid = uic.companyid
                        JOIN  {report_gen_company_relation} cr_two  ON cr_two.companyid = cr_tre.parentid
                        JOIN  {report_gen_company_relation} cr_one  ON cr_one.companyid = cr_two.parentid
                     WHERE	co.hierarchylevel = 3
                     ORDER bY co.industrycode, cr_one.parentid,cr_two.parentid,cr_tre.parentid ";

            // Create view
            $view = " CREATE OR REPLACE VIEW companies_with_users 
                                        AS ($sql) ";

            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_companies_with_users

    /**
     * Description
     * Create course_company_user_enrol view
     *
     * @throws      Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function view_course_company_user_enrol() {
        /* Variables */
        global $DB;
        $view       = null;
        $sql        = null;

        try {
            // SQL for view - OLD
            $sql = " SELECT       CONCAT(e.courseid,'_',uic.companyid,'_',u.id) AS 'id',
                                  e.courseid                                    AS 'courseid',
                                  uic.companyid                                 AS 'companyid',
                                  u.id                                          AS 'user',
                                  CONCAT(u.firstname, ' ', u.lastname)          AS 'name',
                                  uic.jobroles                                  AS 'jobroles',
                                  IF(cc.timecompleted,cc.timecompleted,0)       AS 'timecompleted'
                     FROM		  {user_enrolments}		      ue
                        JOIN 	  {enrol}					  e   ON  e.id 	 		= ue.enrolid
                                                                  AND e.status 		= 0
                        JOIN 	  {user} 					  u   ON  u.id 	 		= ue.userid
                                                                  AND u.deleted 	= 0
                        JOIN 	  {user_info_competence_data} uic ON  uic.userid  	= u.id
                        JOIN 	  companies_with_users 		  co  ON  co.levelthree = uic.companyid
                        LEFT JOIN {course_completions}	      cc  ON  cc.userid 	= uic.userid
                                                                  AND cc.course 	= e.courseid
                     ORDER BY e.courseid , uic.companyid , u.id ";

            // SQL for view
            $sql = " SELECT	  cc.id,
                              cc.course 									AS 'courseid',
                              uic.companyid                                 AS 'companyid',
                              u.id                                          AS 'user',
                              CONCAT(u.firstname, ' ', u.lastname)          AS 'name',
                              uic.jobroles                                  AS 'jobroles',
                              IF(cc.timecompleted,cc.timecompleted,0)       AS 'timecompleted'
                     FROM	  {course_completions} 			cc
                        JOIN  {user}						u   	ON  u.id 	 		= cc.userid
                                                                    AND u.deleted 		= 0
                        JOIN  {user_info_competence_data} 	uic 	ON  uic.userid  	= u.id
                        JOIN  companies_with_users 		  	co  	ON  co.levelthree 	= uic.companyid ";

            // Create view
            $view = " CREATE OR REPLACE VIEW course_company_user_enrol 
                                        AS ($sql) ";

            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_course_company_user_enrol

    /**
     * Description
     * Add the content to the view
     *
     * @throws          Exception
     *
     * @creationDate    24/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function set_content_users_course_view() {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;

        try {
            // SQL Instruction
            $sql = " SELECT		  CONCAT(c.id,'_',ra.userid,'_',r.id) as 'id',
                                  ra.userid				              as 'userid',
                                  c.id 				  	              as 'course',
                                  c.fullname,
                                  r.shortname 			              as 'role',
                                  ra.timemodified			          as 'enrolleddata',
                                  cc.timecompleted,
                                  c.format,
                                  c.startdate,
                                  c.visible,
                                  cf.value 				              as 'fromto',
                                  cp.value 				              as 'producedby'
                     FROM		  {course}				c
                        -- TIME (FROM - TO)
                        LEFT JOIN {course_format_options}	cf	ON 	cf.courseid   = c.id
                                                                AND	cf.name	 	  = 'time'
                        -- PRODUCED BY
                        LEFT JOIN {course_format_options}	cp	ON	cp.courseid   = c.id
                                                                AND cp.name 	  = 'producedby'
                        -- USERS ENROLLED
                        JOIN	  {context}				    co	ON	co.instanceid = c.id
                        JOIN	  {role_assignments}		ra	ON  ra.contextid  = co.id
                        JOIN	  {role}					r	ON	r.id		  = ra.roleid
                        -- Completion data
                        LEFT JOIN {course_completions}	    cc	ON	cc.course	  = co.instanceid
                                                                AND	cc.userid	  = ra.userid
                     WHERE	c.id != 1 ";

            // View
            $view = " CREATE OR REPLACE VIEW user_course 
                                        AS ($sql) ";
            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_content_users_course_view

    /**
     * Description
     * Get total users course view.
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    24/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_total_users_course_view() {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $today  = null;
        $to     = null;
        $params = null;

        try {
            // Range date - Search criteria
            $today = strtotime("today", usertime( time() ));
            $to   = strtotime(2 * (-1) . ' years', $today);
            // Add criteria
            $params = array();
            $params['from'] = $to;
            $params['to']   = $today;

            // SQL Instruction
            $sql = " SELECT	  count(*) as 'total'
                     FROM	  {course}			  c
                        -- USERS ENROLLED
                        JOIN  {context}			  co  ON  co.instanceid	= c.id
                        JOIN  {role_assignments}  ra  ON  ra.contextid	= co.id
                        JOIN  {role}			  r	  ON  r.id			= ra.roleid
                     WHERE	  c.id != 1
                        AND   c.startdate BETWEEN :from AND :to ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_users_course_view

    /**
     * Description
     * Create an empty view. user_course view
     *
     * @throws          Exception
     *
     * @creationDate    24/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function view_empty_user_course() {
        /* Variables */
        global $DB;
        $view = null;

        try {
            // View
            $view = " CREATE OR REPLACE VIEW user_course
                                AS (
                                    SELECT	NULL as 'id',
                                            NULL as 'userid',
                                            NULL as 'course',
                                            NULL as 'fullname',
                                            NULL as 'role',
                                            NULL as 'enrolleddata',
                                            NULL as 'timecompleted',
                                            NULL as 'format',
                                            NULL as 'startdate',
                                            NULL as 'visible',
                                            NULL as 'fromto',
                                            NULL as 'producedby'
                                    FROM    {counties}
                                   ) ";

            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_empty_user_course

    /**
     * Description
     * Create an empty view. user_profile
     * @throws          Exception
     *
     * @creationDate    24/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function view_empty_profile() {
        /* Variables */
        global $DB;
        $view = null;

        try {
            // View
            $view = " CREATE OR REPLACE VIEW user_profile
                                AS ( 
                                    SELECT	NULL as 'id',
                                            NULL as 'zero',
                                            NULL as 'zeroname',
                                            NULL as 'one',
                                            NULL as 'onename',
                                            NULL as 'two',
                                            NULL as 'twoname',
                                            NULL as 'three',
                                            NULL as 'threename',
                                            NULL as 'industrycode',
                                            NULL as 'userid',
                                            NULL as 'firstname',
                                            NULL as 'lastname',
                                            NULL as 'gender'
                                    FROM	{counties}
                                    WHERE   FALSE 
                                   ) ";

            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_profile

    /**
     * Description
     * Create the view with all users with competence data
     *
     * @param       integer $gender Gender field id
     *
     * @throws              Exception
     *
     * @creationDate    24/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function view_profile($gender) {
        /* Variables */
        global $DB;
        $view       = null;
        $sql        = null;
        $params     = null;
        $sqlGender  = null;
        $jonGender  = null;

        try {
            // Search criteria
            $params = array();
            $params['gender'] = $gender;

            if ($gender) {
                $sqlGender  = ", uid.data 			as 'gender' ";
                $joinGender = "  LEFT JOIN {user_info_data} uid	ON  uid.userid	= u.id
                                                                AND uid.fieldid	= :gender ";
            }//if_gender

            // SQL for the view
            $sql = " SELECT		  co_zero.id		as 'zero',
                                  co_zero.name      as 'zeroname',
                                  co_one.id			as 'one',
                                  co_one.name		as 'onename',
                                  co_two.id			as 'two',
                                  co_two.name		as 'twoname',
                                  co.id 			as 'three',
                                  co.name 			as 'threename',
                                  co.industrycode,
                                  u.id				as 'userid',
                                  u.firstname,
                                  u.lastname
                                  $sqlGender
                    FROM		  {user_info_competence_data}	  uic
                        JOIN	  {user} 						  u		  ON  u.id 		        = uic.userid
                                                                          AND u.deleted         = 0
                        -- Level three
                        JOIN 	  {report_gen_companydata}	      co	  ON  co.id 	        = uic.companyid
                        -- Level Two
                        JOIN	  {report_gen_company_relation}   cr_two  ON  cr_two.companyid  = co.id
                        JOIN	  {report_gen_companydata}	      co_two  ON  co_two.id		    = cr_two.parentid
                        -- Level One
                        JOIN	  {report_gen_company_relation}   cr_one  ON  cr_one.companyid  = co_two.id
                        JOIN	  {report_gen_companydata}	      co_one  ON  co_one.id		    = cr_one.parentid
                        -- Level Zero
                        JOIN	  {report_gen_company_relation}   cr_zero ON  cr_zero.companyid = co_one.id
                        JOIN	  {report_gen_companydata}	      co_zero ON  co_zero.id		= cr_zero.parentid 
                        $joinGender ";

            // Create view
            $view = " CREATE OR REPLACE VIEW user_profile 
                                        AS ($sql) ";

            // Execute
            $DB->execute($view,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_profile

    /**
     * Description
     * Get gender field id.
     *
     * @return          mixed|null
     *
     * @throws          Exception
     *
     * @creationDate    24/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_gender_fieldid() {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['datatype'] = 'gender';

            // Execute
            $rdo = $DB->get_record('user_info_field',$params,'id');

            if ($rdo) {
                return $rdo->id;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_gender_fieldid
}//Manager_cron