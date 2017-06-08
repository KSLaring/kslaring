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
 * Library code for the Company Report Competence Manager.
 *
 * @package         report
 * @subpackage      manager/company_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/04/2015
 * @author          eFaktor     (fbv)
 *
 */

define('COMPANY_REPORT_FORMAT_SCREEN', 0);
define('COMPANY_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('COMPANY_REPORT_FORMAT_LIST', 'report_format_list');
define('COMPANY_REPORT_STRUCTURE_LEVEL','level_');

class CompanyReport {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $ufiltering
     * @return          array
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users based on the search criteria
     */
    public static function GetSelection_Filter($ufiltering) {
        global $SESSION, $DB, $CFG;

        // get the SQL filter
        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $acount = $DB->count_records_select('user', $sqlwhere, $params);
        $scount = count($SESSION->bulk_users);

        $userlist = array('acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total);
        $userlist['ausers'] = $DB->get_records_select_menu('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_BULK_USERS);

        if ($scount) {
            if ($scount < MAX_BULK_USERS) {
                $in = implode(',', $SESSION->bulk_users);
            } else {
                $bulkusers = array_slice($SESSION->bulk_users, 0, MAX_BULK_USERS, true);
                $in = implode(',', $bulkusers);
            }

            $userlist['susers'] = $DB->get_records_select_menu('user', "id in ($in) ", null, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        }

        return $userlist;
    }//GetSelection_Filter


    /**
     * @param           $ufiltering
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users from the selector
     */
    public static function AddAll_SelectionFilter($ufiltering) {
        global $SESSION, $DB, $CFG;

        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        foreach ($rs as $user) {
            if (!isset($SESSION->bulk_users[$user->id])) {
                $SESSION->bulk_users[$user->id] = $user->id;
            }
        }
        $rs->close();
    }//AddAll_SelectionFilter

    /**
     * @param           $my_companies
     * @param           $my_level
     * @return          array
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get my real companies depending on my level
     */
    public static function Get_MyCompanies($my_companies,$my_level) {
        /* Variables    */
        $myCompanies = array();
        $levelThree = array();
        $levelTwo   = array();
        $levelOne   = array();
        $levelZero  = array();

        try {
            foreach ($my_companies as $company) {
                $levelZero  = array_flip(explode(',',$company->levelZero));
                $levelOne   = array_flip(explode(',',$company->levelOne));
                $levelTwo   = array_flip(explode(',',$company->levelTwo));
                $levelThree[$company->levelThree]   = $company->levelThree;
            }


            switch ($my_level) {
                case 0:
                    /* Level Three  */
                    $myCompanies = CompetenceManager::GetCompanies_LevelList(3);
                    unset($myCompanies[0]);

                    break;
                case 1:
                    /* Level Zero   */
                    $parents = implode(',',array_keys($levelZero));
                    /* Level One    */
                    $levelOne = CompetenceManager::GetCompanies_LevelList(1,$parents);
                    unset($levelOne[0]);
                    $levelOne = implode(',',array_keys($levelOne));
                    /* Level Two    */
                    $levelTwo = CompetenceManager::GetCompanies_LevelList(2,$levelOne);
                    unset($levelTwo[0]);
                    $parents = implode(',',array_keys($levelTwo));

                    /* Level Three  */
                    $myCompanies = CompetenceManager::GetCompanies_LevelList(3,$parents);
                    unset($myCompanies[0]);

                    break;
                case 2:
                    /* Level One    */
                    $parents = implode(',',array_keys($levelOne));
                    /* Level Two    */
                    $levelTwo = CompetenceManager::GetCompanies_LevelList(2,$parents);
                    unset($levelTwo[0]);
                    $parents = implode(',',array_keys($levelTwo));

                    /* Level Three  */
                    $myCompanies = CompetenceManager::GetCompanies_LevelList(3,$parents);
                    unset($myCompanies[0]);

                    break;
                case 3:
                    /* Level Two   */
                    $parents = implode(',',array_keys($levelTwo));

                    /* Level Three  */
                    $myCompanies = CompetenceManager::GetCompanies_LevelList(3,$parents);
                    unset($myCompanies[0]);

                    break;
                case 4:
                    $myCompanies = array_keys($levelThree);

                    break;
            }//my_level

            return $myCompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompanies

    /**
     * @param           $company
     * @param           $users_selected
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company info tracker
     *
     * Company Tracker
     *                  --> levelThree
     *                  --> name
     *                  --> outcomes.   Array
     *                  --> users.      Array
     *                          --> id
     *                          --> job_roles
     *                          --> job_roles_names
     *                          --> outcomes.       Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed.      Array
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                      --> not_completed.  Array
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                      --> not_enrol.      Array
     *                          --> completed.      Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *                          --> not_completed.  Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     */
    public static function Get_CompanyTracker($company,$users_selected) {
        /* Variables    */
        $companyTracker     = null;
        $job_roles          = array();
        $jr_keys            = null;
        $usersCompany       = null;
        $outcomes           = null;

        try {
            /* Company Tracker Info */
            $companyTracker = new stdClass();
            $companyTracker->levelThree         = $company->levelThree;
            $companyTracker->name               = CompetenceManager::GetCompany_Name($company->levelThree);
            $companyTracker->users              = null;
            $companyTracker->outcomes           = null;

            /* Job Roles connected to the level Three    */
            if (CompetenceManager::IsPublic($company->levelThree)) {
                CompetenceManager::GetJobRoles_Generics($job_roles);
            }//if_isPublic
            CompetenceManager::GetJobRoles_Hierarchy($job_roles,3,$company->levelZero,$company->levelOne,$company->levelTwo,$company->levelThree);

            /* Outcome Courses  */
            if ($job_roles) {
                $jr_keys  = implode(',',array_keys($job_roles));
                $outcomes = self::GetOutcomesCourses_CompanyTracker($jr_keys);
            }//if_job_roles

            /* Users Info       */
            $companyTracker->users  = self::GetUsers_CompanyTracker($company->levelThree,$users_selected);

            /* Save Outcomes    */
            $companyTracker->outcomes = $outcomes;

            return $companyTracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyTracker

    /**
     * @param           $companyTracker
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the Company Tracker - Screen Format
     *
     * Company Tracker
     *                  --> levelThree
     *                  --> name
     *                  --> outcomes.
     *                  --> users.      Array
     *                          --> id
     *                          --> job_roles
     *                          --> job_roles_names
     *                          --> outcomes.       Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed.      Array
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                      --> not_completed.  Array
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                      --> not_enrol.      Array
     *                          --> completed.      Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *                          --> not_completed.  Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     */
    public static function PrintReport_CompanyTracker($companyTracker) {
        /* Variables    */
        global $SESSION;
        $out_report         = null;
        $url_img            = null;
        $toggleUser         = null;
        $toggleOutcome      = null;
        $toggleIndividual   = null;
        $return_url         = null;

        try {
            /* Url to Back  */
            $return_url = new moodle_url('/report/manager/company_report/company_report.php');
            if ($SESSION->user_filtering) {
                $return_url->param('advanced',1);
            }

            /* Company Tracker Report   */
            $out_report .= html_writer::start_div('company_rpt_div');
                /* Header Report    */
                $out_report .= html_writer::start_div('company_detail_rpt');
                    /* Company Name */
                    $out_report .= '<h3>';
                        $out_report .=  $companyTracker->name;
                    $out_report .= '</h3>';
                $out_report .= html_writer::end_div();//company_detail_rpt

                /* Not Users    */
                if (!$companyTracker->users) {
                    $out_report .= '<h5>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h5>';
                }else {
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($return_url,get_string('employee_return_to_selection','report_manager'),array('class' => 'link_return'));
                }//if_not_users

                if ($companyTracker->users) {
                    /* Toggle   */
                    $url_img  = new moodle_url('/pix/t/expanded.png');
                    $total = count($companyTracker->users);
                    $i = 0;

                        foreach ($companyTracker->users as $id=>$user) {
                            $out_report .= html_writer::start_tag('div',array('class' => 'company_rpt_div'));

                            /* User Header - Toogle               */
                            $toggleUser = 'YUI_' . $id;
                            $out_report .= self::Add_UserHeader_Screen($user->name,$toggleUser,$url_img);

                            /* User Job Roles - Header  */
                            $out_report .= self::Add_UserJobRoles_Header_Screen($user->job_roles_names);

                            /* User List    */
                            $out_report .= html_writer::start_div('tracker_list',array('id' => $toggleUser . '_div'));
                                /* Outcome Courses      */
                                if ($user->outcomes) {
                                    foreach ($user->outcomes as $outcome) {
                                        $toggleOutcome = $toggleUser . '_' . $outcome->id;
                                        $out_report .= self::Add_OutcomesCourses($toggleOutcome,$url_img,$outcome);
                                    }//for_outcomes
                                }//if_outcomes

                                /* Individual Courses   */
                                if ($user->completed || $user->not_completed) {
                                    $toggleIndividual = $toggleUser . '_table';
                                    $out_report .= self::Add_IndividualCourses($toggleIndividual,$url_img,$user->completed,$user->not_completed);
                                }//if_individualcourses

                                /* Break line   */
                                $i ++;
                                if ($i < $total) {
                                    $out_report .= '<hr class="line_rpt">';
                                }//if_total
                            $out_report .= html_writer::end_div();//user_list


                            $out_report .= html_writer::end_tag('div');//company_content
                        }//for_Each_user

                }//if_users
            $out_report .= html_writer::end_div();//company_rpt_div

            /* Return To Selection Page */
            $out_report .= html_writer::link($return_url,get_string('employee_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//PrintReport_CompanyTracker

    /**
     * @param           $companyTracker
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Download the company tracker report - Excel Format
     *
     * Company Tracker
     *                  --> levelThree
     *                  --> name
     *                  --> outcomes.
     *                  --> users.      Array
     *                          --> id
     *                          --> job_roles
     *                          --> job_roles_names
     *                          --> outcomes.       Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed.      Array
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                      --> not_completed.  Array
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                      --> not_enrol.      Array
     *                          --> completed.      Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *                          --> not_completed.  Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     */
    public static function DownloadReport_CompanyTracker($companyTracker) {
        /* Variables    */
        global $CFG;
        $row        = null;
        $my_xls     = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($companyTracker->name . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Outcome Courses      */
            if ($companyTracker->outcomes) {
                foreach ($companyTracker->outcomes as $outcome) {
                    if ($companyTracker->users) {
                        self::AddSheet_OutcomeCourses($export,$my_xls,$companyTracker->name,$companyTracker->users,$outcome);
                   }//if_users
                }//for_Each_outcome
            }//if_outcomes


            /* Individual Courses   */
            self::AddSheet_IndividualCourses($export,$my_xls,$companyTracker->name,$companyTracker->users);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DownloadReport_CompanyTracker

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $job_roles
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the outcomes connected with the job roles
     *
     * Outcomes Courses
     *              [id]
     *                  --> id
     *                  --> name
     *                  --> expiration
     *                  --> courses
     *                  --> users
     */
    private static function GetOutcomesCourses_CompanyTracker($job_roles) {
        /* Variables    */
        global $DB;
        $outcomes           = array();
        $info               = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT			go.id,
                                    go.fullname,
                                    GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as 'courses',
                                    oe.expirationperiod
                     FROM		   	{grade_outcomes}			    go
                        JOIN	    {grade_outcomes_courses}	    goc	    ON 		goc.outcomeid 	= go.id
                        JOIN	    {course}					    c	    ON		c.id 			= goc.courseid
                                                                            AND		c.visible 		= 1
                        LEFT JOIN 	{report_gen_outcome_exp}	    oe	    ON		oe.outcomeid	= go.id
                        JOIN		{report_gen_outcome_jobrole}	jro		ON		jro.outcomeid	= go.id
                                                                            AND		jro.jobroleid   IN ($job_roles)
                     GROUP BY	go.id
                     ORDER BY	go.fullname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Outcome info */
                    if ($instance->courses) {
                        $info = new stdClass();
                        $info->id           = $instance->id;
                        $info->name         = $instance->fullname;
                        $info->expiration   = $instance->expirationperiod;
                        $info->courses      = $instance->courses;
                        $info->users        = array();

                        $outcomes[$instance->id] = $info;
                    }//if_courses
                }//for_each_outcome
            }//if_rdo

            return $outcomes;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetOutcomesCourses_CompanyTracker

    /**
     * @param           $company
     * @param           $users_selected
     * @param           $outcomes
     * @param           $job_roles
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users are connected to the company
     *

     */

    /**
     * @param           $company
     * @param           $users_selected
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get tracker info of the users connected with the company
     *
     * Users Tracker
     *          [id]
     *              --> id
     *              --> name
     *              --> job_roles
     *              --> outcomes.   Array
     *                          --> id
     *                          --> name
     *                          --> expiration
     *                          --> completed.      Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *                          --> not_completed.  Array
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *                          --> not_enrol.      Array
     *                                      --> id
     *                                      --> name
     *              --> completed.      Array
     *                          --> id
     *                          --> name
     *                          --> completed
     *              --> not_completed.  Array
     *                          --> id
     *                          --> name
     *                          --> completed
     *
     * @updateDate      17/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Show users without jobrole. Not jobrole --> Courses are considered as Individual courses
     */
    private static function GetUsers_CompanyTracker($company,$users_selected) {
        /* Variables    */
        global $DB;
        $usersIn            = implode(',',$users_selected);
        $usersTracker       = array();
        $info               = null;
        $jr_users           = null;
        $outcomesCourses    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company']  = $company;

            /* SQL Instruction  */
            $sql = "  SELECT		DISTINCT 	u.id,
                                                CONCAT(u.firstname, ' ', u.lastname)  as 'name',
                                                IF(uicd.jobroles,uicd.jobroles,0)     as 'jobroles'
                      FROM			{user}	    					u
                        JOIN		{user_info_competence_data}	  	uicd 	ON  	uicd.userid    	= u.id
                                                                            AND 	uicd.companyid  = :company
                      WHERE		u.deleted 	 = 0
                        AND     u.id 		IN ($usersIn)
                        AND     u.username 	!=  'guest'
                      ORDER BY   u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* User Info    */
                    $info = new stdClass();
                    $info->id                               = $instance->id;
                    $info->name                             = $instance->name;
                    $info->job_roles                        = $instance->jobroles;
                    $info->job_roles_names                  = self::Get_JobRolesNames($instance->jobroles);
                    /* Courses Connected with my Job roles  */
                    list($info->outcomes,$outcomesCourses)  = self::GetInfoOutcomes_JobRoles($instance->id,$instance->jobroles);
                    /* Individual Courses                   */
                    $info->completed                        = null;
                    $info->not_completed                    = null;

                    /* Get Info Individual Courses  */
                    list($info->completed,$info->not_completed) = self::GetInfo_IndividualCoursesEnrol($instance->id,$outcomesCourses);

                    /* Add User */
                    if ($info->outcomes || $info->completed || $info->not_completed) {
                        $usersTracker[$instance->id] = $info;
                    }//if_outcomes_completed_notCompleted
                }//for_Rdo
            }//if_rdo

            return $usersTracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_CompanyTracker


    /**
     * @param           $userId
     * @param           $jr_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      17/09/2015
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Get the outcomes courses connected with the user
     *
     * Outcomes
     *          [id]
     *              --> id
     *              --> name
     *              --> expiration
     *              --> completed.      Array
     *                                      [id]
     *                                          --> id
     *                                          --> name
     *                                          --> completed
     *              --> not_completed.  Array
     *                                      [id]
     *                                          --> id
     *                                          --> name
     *                                          --> completed
     *              --> not_enrol.      Array
     *                                      [id]
     *                                          --> id
     *                                          --> name
     *
     *
     */
    private static function GetInfoOutcomes_JobRoles($userId,$jr_lst) {
        /* Variables    */
        global $DB;
        $info               = null;
        $coursesEnrol       = null;
        $outcomes           = array();
        $coursesOutcomes    = 0;

        try {
            /* SQL Instruction  */
            $sql = " SELECT	    o.id,
                                o.fullname,
                                GROUP_CONCAT(DISTINCT oucu.courseid ORDER BY oucu.courseid SEPARATOR ',') as 'courses',
                                rgo.expirationperiod
                     FROM		{grade_outcomes}              o
                        JOIN 	{grade_outcomes_courses}      oucu	    ON 	  	oucu.outcomeid  = o.id
                        JOIN 	{report_gen_outcome_exp}      rgo	  	ON 	  	rgo.outcomeid   = oucu.outcomeid
                        JOIN 	{report_gen_outcome_jobrole}  oj	  	ON 	  	oj.outcomeid    = rgo.outcomeid
                        JOIN 	{report_gen_jobrole}          jr	  	ON 	  	jr.id 		  	= oj.jobroleid
                                                                        AND   	jr.id 		    IN ($jr_lst)
                     GROUP BY 	o.id
                     ORDER BY   o.fullname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Outcome Info */
                    $info = new stdClass();
                    $info->id               = $instance->id;
                    $info->name             = $instance->fullname;
                    $info->expiration       = $instance->expirationperiod;
                    /* Get Completed - Not Completed Courses    */
                    list($info->completed,$info->not_completed) = self::GetInfo_CoursesEnrol($userId,$instance->courses);
                    /* Get Not Enrol    */
                    if ($info->completed && $info->not_completed) {
                        $coursesEnrol   = implode(',',array_keys($info->completed));
                        $coursesEnrol  .= ',' . implode(',',array_keys($info->not_completed));
                    }else {
                        if ($info->completed) {
                            $coursesEnrol = implode(',',array_keys($info->completed));
                        }else {
                            $coursesEnrol = implode(',',array_keys($info->not_completed));
                        }//if_completed
                    }//if_completed_not_completed
                    $info->not_enrol = self::GetInfo_CoursesNotEnrol($instance->courses,$coursesEnrol);

                    /* Add outcome  */
                    if ($info->completed || $info->not_completed || $info->not_enrol)  {
                        $outcomes[$instance->id] = $info;
                        $coursesOutcomes .= ',' . $instance->courses;
                    }//if_courses
                }//for_instance_outcome
            }//if_rdo

            return array($outcomes,$coursesOutcomes);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoOutcomes_JobRoles


    /**
     * @param           $job_roles
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the names of job roles
     */
    private static function Get_JobRolesNames($job_roles) {
        /* Variables    */
        global $DB;
        $jr_names = null;

        try {
            if ($job_roles) {
                /* SQL Instruction  */
                $sql = " SELECT		id,
                                CONCAT(industrycode,' - ',name) as 'name'
                     FROM		{report_gen_jobrole}
                     WHERE		id IN ($job_roles)
                     ORDER BY	industrycode, name ";

                /* Execute  */
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        if ($jr_names) {
                            $jr_names .= ', ' . $instance->name;
                        }else {
                            $jr_names = $instance->name;
                        }//if_jr_names

                    }//for_each_job_role
                }//if_rdo
            }//if_job_roles

            return $jr_names;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesNames

    /**
     * @param           $user_id
     * @param           $courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the courses that the user has completed and not completed
     *
     * Completed / Not Completed
     *                          [id]
     *                              --> id
     *                              --> name
     *                              --> completed
     */
    private static function GetInfo_CoursesEnrol($user_id,$courses) {
        /* Variables    */
        global $DB;
        $completed      = array();
        $not_completed  = array();
        $info           = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT c.id,
                                     c.fullname,
                                    IF (cc.timecompleted,cc.timecompleted,0) as 'completed'
                     FROM			{course}				c
                        JOIN		{enrol}				    e	ON		e.courseid 	= c.id
                                                                AND		e.status	= 0
                        JOIN		{user_enrolments}		ue	ON 		ue.enrolid	= e.id
                                                                AND		ue.userid	= :user
                        LEFT JOIN	{course_completions}	cc	ON	    cc.course 	= e.courseid
                                                                AND		cc.userid	= ue.userid
                     WHERE		c.id IN ($courses)
                     ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->completed    = $instance->completed;

                    /* Add Course   */
                    if ($info->completed) {
                        /* Completed    */
                        $completed[$instance->id] = $info;
                    }else {
                        /* Not Completed    */
                        $not_completed[$instance->id] = $info;
                    }//if_completed
                }//for_each_course
            }//if_rdo

            return array($completed,$not_completed);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfo_CoursesEnrol

    /**
     * @param           $courses
     * @param           $coursesEnrol
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the courses that the user is not enrolled
     *
     * Not Enrol
     *          [id]
     *              --> id
     *              --> name
     */
    private static function GetInfo_CoursesNotEnrol($courses,$coursesEnrol) {
        /* Variables    */
        global $DB;
        $not_enrol  = array();
        $info       = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		c.id,
                                c.fullname
                    FROM		{course}		  c
                    WHERE		c.id IN ($courses) ";

            /* Courses Enrol    */
            if ($coursesEnrol) {
                $sql .= " AND c.id NOT IN ($coursesEnrol) ";
            }//if_coursesEnrol

            /* Order    */
            $sql .= " ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;

                    /* Add course   */
                    $not_enrol[$instance->id] = $info;
                }//for_instance
            }//if_rdo

            return $not_enrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfo_CoursesNotEnrol


    /**
     * @param           $user_id
     * @param           $courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the individual courses connected with the user
     */
    private static function GetInfo_IndividualCoursesEnrol($user_id,$courses) {
        /* Variables    */
        global $DB;
        $completed      = array();
        $not_completed  = array();
        $info           = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT	    DISTINCT c.id,
                                c.fullname,
                                IF (cc.timecompleted,cc.timecompleted,0) as 'completed'
                     FROM			{course}				c
                        JOIN		{enrol}				    e	ON		e.courseid 	= c.id
                                                                AND		e.status	= 0
                        JOIN		{user_enrolments}		ue	ON 		ue.enrolid	= e.id
                                                                AND		ue.userid	= :user
                        LEFT JOIN	{course_completions}	cc	ON	    cc.course 	= e.courseid
                                                                AND		cc.userid	= ue.userid
                     WHERE		c.id NOT IN ($courses)
                     ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->completed    = $instance->completed;

                    /* Add Course   */
                    if ($info->completed) {
                        /* Completed    */
                        $completed[$instance->id] = $info;
                    }else {
                        /* Not Completed    */
                        $not_completed[$instance->id] = $info;
                    }//if_completed
                }//for_each_course
            }//if_rdo

            return array($completed,$not_completed);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfo_IndividualCoursesEnrol

    /**
     * @param           $user
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    09/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the user header to the company tracker report
     */
    private static function Add_UserHeader_Screen($user,$toogle,$img) {
        /* Variables    */
        $header     = '';

        $header .= html_writer::start_div('header_user_rpt');
            /* Col One  */
            $header .= html_writer::start_div('col_one');
                $header .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header .= html_writer::end_div();//col_one

            /* Col Two  */
            $header .= html_writer::start_div('col_two');
                $header .= '<h5>' . $user . '</h5>';
            $header .= html_writer::end_div();//col_two
        $header .= html_writer::end_div();//header_user_rpt

        return $header;
    }//Add_CompanyHeader_Screen

    /**
     * @param           $job_roles
     * @return          string
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the job roles header, connected with the user, to the company tracker report
     */
    private static function Add_UserJobRoles_Header_Screen($job_roles) {
        /* Variables    */
        $header     = '';

        $header .= html_writer::start_div('header_user_job_roles_rpt');
            /* Col One  */
            $header .= html_writer::start_div('col_one');
            $header .= html_writer::end_div();//col_one

            /* Col Two  */
            $header .= html_writer::start_div('col_two');
                $header .= '<h6>' . $job_roles . '</h6>';
            $header .= html_writer::end_div();//col_two
        $header .= html_writer::end_div();//header_user_rpt

        return $header;
    }//Add_UserJobRoles_Header_Screen

    /**
     * @param           $toggleIndividual
     * @param           $img
     * @param           $completed
     * @param           $not_completed
     * @return          string
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the individual courses to the company tracker report
     */
    private static function Add_IndividualCourses($toggleIndividual,$img,$completed,$not_completed) {
        /* Variables    */
        $out_individual = '';

        /* Header   */
        $out_individual .= self::Add_IndividualOutcome_Header_Screen(get_string('individual_courses','local_tracker_manager'));

        /* Header Table         */
        $out_individual .= self::AddHeader_CoursesTable($toggleIndividual,$img,true);
        /* Content Table        */
        $out_individual .= html_writer::start_tag('div',array('class' => 'course_list','id' => $toggleIndividual . '_div'));
            $out_individual .= self::AddContent_IndividualCoursesTable($completed,$not_completed);
        $out_individual .= html_writer::end_div();//course_list

        return $out_individual;
    }//Add_IndividualCourses

    /**
     * @param           $toggleOutcome
     * @param           $img
     * @param           $outcome
     * @return          string
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the outcomes courses to the company tracker
     */
    private static function Add_OutcomesCourses($toggleOutcome,$img,$outcome) {
        /* Variables    */
        $out_outcome = '';

        /* Header   */
        $out_outcome .= self::Add_IndividualOutcome_Header_Screen($outcome->name);

        /* Header Table         */
        $out_outcome .= self::AddHeader_CoursesTable($toggleOutcome,$img);
        /* Content Table        */
        $out_outcome .= html_writer::start_tag('div',array('class' => 'course_list','id' => $toggleOutcome . '_div'));
            $out_outcome .= self::AddContent_OutcomesCoursesTable($outcome);
        $out_outcome .= html_writer::end_div();//course_list

        return $out_outcome;
    }//Add_OutcomesCourses

    /**
     * @param           $title
     * @return          string
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Individual/Outcome header to the report
     */
    private static function Add_IndividualOutcome_Header_Screen($title) {
        /* Variables    */
        $header = '';

        $header .= html_writer::start_tag('div',array('class' => 'header_individual_outcome_rpt'));
            /* Col One  */
            $header .= html_writer::start_tag('div',array('class' => 'col_one'));
            $header .= html_writer::end_tag('div');//col_one
            /* Col Two  */
            $header .= html_writer::start_tag('div',array('class' => 'col_two'));
                $header .= '<h5>'. $title . '</h5>';
            $header .= html_writer::end_tag('div');//col_two
        $header .= html_writer::end_tag('div');

        return $header;
    }//Add_IndividualHeader_Screen

    /**
     * @param           $toogle
     * @param           $img
     * @param      bool $individual
     * @return          string
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to the course table
     */
    private static function AddHeader_CoursesTable($toogle,$img,$individual=false) {
        /* Variables    */
        $header = '';
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strValid          = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        $header .= html_writer::start_tag('div',array('class' => 'course_list'));
            $header .= html_writer::start_tag('table');
                $header .= html_writer::start_tag('tr',array('class' => 'head'));
                    /* Empty Col   */
                    $header .= html_writer::start_tag('th',array('class' => 'head_first'));
                        $header .= '<button class="toggle" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
                    $header .= html_writer::end_tag('th');
                    /* Course           */
                    $header .= html_writer::start_tag('th',array('class' => 'head_course'));
                        $header .= $strCourse;
                    $header .= html_writer::end_tag('th');
                    /* Status        */
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strState;
                    $header .= html_writer::end_tag('th');
                    /* Completion    */
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strCompletion;
                    $header .= html_writer::end_tag('th');
                    /* Valid        */
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        if (!$individual) {
                            $header .= $strValid;
                        }//if_not_individual
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('table');
        $header .= html_writer::end_tag('div');//course_list

        return $header;
    }//AddHeader_CoursesTable

    /**
     * @param           $outcome
     * @return          string
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the outcomes courses table content
     */
    private static function AddContent_OutcomesCoursesTable($outcome) {
        /* Variables    */
        $content        = '';
        $url            = null;
        $strUrl         = null;
        $not_completed  = null;
        $completed      = null;
        $not_enrol      = null;
        $class          = null;
        $label          = null;
        // Headers
        $strCourse      = get_string('course');
        $strState       = get_string('state','local_tracker_manager');
        $strValid       = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion  = get_string('completion_time','local_tracker_manager');

        try {
            $content .= html_writer::start_tag('table');
                /* Not Completed    */
                if ($outcome->not_completed) {
                    $not_completed = $outcome->not_completed;
                    foreach ($not_completed as $course) {
                        $content .= html_writer::start_tag('tr');
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strState));
                                $content .= get_string('outcome_course_started','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                                $content .= '&nbsp;';
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_not_completed
                }//if_not_completed


                /* Not Enrol        */
                if ($outcome->not_enrol) {
                    $not_enrol = $outcome->not_enrol;
                    foreach ($not_enrol as $course) {
                        $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status not_enroll','data-th' => $strState));
                                $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                                $content .= '&nbsp;';
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_not_enrol
                }//if_not_enrol

                /* Completed        */
                if ($outcome->completed) {
                    $completed = $outcome->completed;
                    foreach ($completed as $course) {
                        $ts = strtotime($outcome->expiration  . ' month', $course->completed);
                        if ($ts < time()) {
                            $class = 'expired';
                            $label = get_string('outcome_course_expired','local_tracker_manager');
                        }else {
                            $class = 'completed';
                            $label = get_string('outcome_course_finished','local_tracker_manager');
                        }

                        $content .= html_writer::start_tag('tr',array('class' => $class));
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status ' . $class,'data-th' => $strState));
                                $content .= $label;
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= userdate($course->completed,'%d.%m.%Y', 99, false);
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                                $content .= userdate($ts,'%d.%m.%Y', 99, false);
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_completed
                }//if_completed
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_OutcomesCoursesTable

    /**
     * @param           $completed
     * @param           $not_completed
     * @return          string
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Individual courses table content
     */
    private static function AddContent_IndividualCoursesTable($completed,$not_completed) {
        /* Variables    */
        $content    = '';
        $url        = null;
        $strUrl     = null;
        // Headers
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strValid          = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        try {
            $content .= html_writer::start_tag('table');
                /* Not Completed    */
                if ($not_completed) {
                    foreach ($not_completed as $course) {
                        $content .= html_writer::start_tag('tr');
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strState));
                                $content .= get_string('outcome_course_started','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                                $content .= '&nbsp;';
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_not_completed
                }//if_not_completed

                /* Completed        */
                if ($completed) {
                    foreach ($completed as $course) {
                        $content .= html_writer::start_tag('tr',array('class' => 'completed'));
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status completed','data-th' => $strState));
                                $content .= get_string('outcome_course_finished','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= userdate($course->completed,'%d.%m.%Y', 99, false);;
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                                $content .= '&nbsp;';
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_completed
                }//if_completed
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_IndividualCoursesTable

    /**
     * @param           $excel
     * @param           $my_xls
     * @param           $company
     * @param           $trackerUsers
     * @param           $outcome
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new sheet for one outcome
     */
    private static function AddSheet_OutcomeCourses(&$excel,&$my_xls,$company,$trackerUsers,$outcome) {
        /* Variables    */
        $row = 0;

        try {
            // Adding the worksheet
            $my_xls = $excel->add_worksheet($outcome->name);

            /* Add Header    */
            self::AddHeaderSheet_CompanyTracker($my_xls,$row,$company,$outcome->name,false);

            /* Add Content  */
            foreach ($trackerUsers as $user) {
                if (isset($user->outcomes) && isset($user->outcomes[$outcome->id])) {
                    self::AddContentSheet_OutcomeCourses($my_xls,$row,$user->name,$user->outcomes[$outcome->id]);

                    $my_xls->merge_cells($row,0,$row,17);
                    $row ++;
                }//if_outcome
            }//for_Each_user
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSheet_OutcomeCourses

    /**
     * @param           $excel
     * @param           $my_xls
     * @param           $company
     * @param           $users
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new sheet for all individual courses
     */
    private static function AddSheet_IndividualCourses(&$excel,&$my_xls,$company,$users) {
        /* Variables    */
        $row = 0;

        try {
            // Adding the worksheet
            $my_xls = $excel->add_worksheet(get_string('individual_courses','local_tracker_manager'));

            /* Add Header    */
            self::AddHeaderSheet_CompanyTracker($my_xls,$row,$company,null,true);

            /* Add Content  */
            foreach ($users as $user) {
                if ($user->completed || $user->not_completed) {
                    self::AddContentSheet_IndividualCourses($my_xls,$row,$user->name,$user->completed,$user->not_completed);

                    $my_xls->merge_cells($row,0,$row,14);
                    $row ++;
                }//if_completed_notCompleted
            }//for_Each_user
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSheet_IndividualCourses

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $company
     * @param      null $outcome
     * @param      bool $individual
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header report to the sheet
     */
    private static function AddHeaderSheet_CompanyTracker(&$my_xls,&$row,$company,$outcome=null,$individual = false) {
        /* Variables    */
        $str_user           = strtoupper(get_string('user'));
        $str_course         = strtoupper(get_string('course'));
        $str_state          = strtoupper(get_string('state','local_tracker_manager'));
        $str_valid          = strtoupper(get_string('outcome_valid_until','local_tracker_manager'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker_manager'));

        try {
            /* Company Name  */
            $col = 0;
            $my_xls->write($row, $col, $company,array('size'=>14, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Outcome Name */
            if ($outcome) {
                $row++;
                $title_out          = get_string('outcome', 'report_manager')  . ' - ' . $outcome;
                $my_xls->write($row, $col, $title_out,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }//if_outcome

            /* Merge Cells  */
            $row ++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row ++;

            /* User     */
            $my_xls->write($row, $col, $str_user,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Course   */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* State    */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_state,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Completion   */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_completion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Valid Until  */
            if (!$individual) {
                $col = $col + 3;
                $my_xls->write($row, $col, $str_valid,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);
            }

            $row ++;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeaderSheet_CompanyTracker

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $user
     * @param           $outcome
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the outcome courses content to the sheet
     */
    private static function AddContentSheet_OutcomeCourses(&$my_xls,&$row,$user,$outcome) {
        /* Variables    */
        $not_completed  = null;
        $completed      = null;
        $not_enrol      = null;
        $bg_color       = null;
        $state          = null;
        $col            = null;

        try {
            /* Not Completed    */
            if (isset($outcome->not_completed)) {
                if ($outcome->not_completed) {
                    $state          = get_string('outcome_course_started','local_tracker_manager');
                    $not_completed  = $outcome->not_completed;
                    foreach ($not_completed as $course) {
                        $col = 0;
                        /* User     */
                        $my_xls->write($row, $col, $user,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Course   */
                        $col = $col + 3;
                        $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+5);
                        $my_xls->set_row($row,20);

                        /* State    */
                        $col = $col + 6;
                        $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Completion   */
                        $col = $col + 3;
                        $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Valid Until  */
                        $col = $col + 3;
                        $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        $row ++;
                    }//for_course
                }//if_not_completed
            }//if_not_completed


            /* Not Enrol        */
            if (isset($outcome->not_enrol)) {
                if ($outcome->not_enrol) {
                    $state      = get_string('outcome_course_not_enrolled','local_tracker_manager');
                    $bg_color   = '#fcf8e3';
                    $not_enrol  = $outcome->not_enrol;
                    foreach ($not_enrol as $course) {
                        $col = 0;
                        /* User     */
                        $my_xls->write($row, $col, $user,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Course   */
                        $col = $col + 3;
                        $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+5);
                        $my_xls->set_row($row,20);

                        /* State    */
                        $col = $col + 6;
                        $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Completion   */
                        $col = $col + 3;
                        $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Valid Until  */
                        $col = $col + 3;
                        $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        $row ++;
                    }//for_Each_not_enrol
                }//if_not_enrol
            }//if_not_enrol

            /* Completed        */
            if (isset($outcome->completed)) {
                if ($outcome->completed) {
                    $completed = $outcome->completed;
                    foreach ($completed as $course) {
                        $col = 0;

                        $ts = strtotime($outcome->expiration  . ' month', $course->completed);
                        if ($ts < time()) {
                            $bg_color = '#f2dede';
                            $state = get_string('outcome_course_expired','local_tracker_manager');
                        }else {
                            $bg_color = '#dff0d8';
                            $state = get_string('outcome_course_finished','local_tracker_manager');
                        }

                        $col = 0;
                        /* User     */
                        $my_xls->write($row, $col, $user,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Course   */
                        $col = $col + 3;
                        $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+5);
                        $my_xls->set_row($row,20);

                        /* State    */
                        $col = $col + 6;
                        $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Completion   */
                        $col = $col + 3;
                        $my_xls->write($row, $col, userdate($course->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        /* Valid Until  */
                        $col = $col + 3;
                        $my_xls->write($row, $col, userdate($ts,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                        $my_xls->merge_cells($row,$col,$row,$col+2);
                        $my_xls->set_row($row,20);

                        $row ++;
                    }//for_Each_completed
                }//if_completed
            }//if_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContentSheet_OutcomeCourses

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $user
     * @param           $completed
     * @param           $not_completed
     * @throws          Exception
     *
     * @creationDate    10/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the individual courses content to the sheet
     */
    private static function AddContentSheet_IndividualCourses(&$my_xls,&$row,$user,$completed,$not_completed) {
        /* Variables    */
        $col        = null;
        $state      = null;
        $bg_color   = '#dff0d8';

        try {
            /* Not Completed    */
            if ($not_completed) {
                foreach ($not_completed as $course) {
                    $col = 0;
                    /* User     */
                    $my_xls->write($row, $col, $user,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Course   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State    */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_started','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row ++;
                }//for_not_completed
            }//if_not_completed

            /* Completed        */
            if ($completed) {
                foreach ($completed as $course) {
                    $col = 0;
                    /* User     */
                    $my_xls->write($row, $col, $user,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Course   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State    */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_started','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row ++;
                }//for_completed
            }//if_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContentSheet_IndividualCourses
}//CompanyReport