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
 * Report Competence Manager  - Library code for the Course Report.
 *
 * @package         report
 * @subpackage      manager/course_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    17/03/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Library for the Course Report
 *
 */

define('COURSE_REPORT_FORMAT_SCREEN', 0);
define('COURSE_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('COURSE_REPORT_FORMAT_LIST', 'report_format_list');
define('MANAGER_COURSE_STRUCTURE_LEVEL','level_');
define('CO_COMPLETED',1);
define('CO_NOT_COMPLETED',2);

class course_report {

    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * Description
     * Initialize javascript
     *
     * @param           $parent
     * @param           $category
     * @param           $course
     * @param           $depth
     *
     * @throws          Exception
     *
     * @creationDate    26/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function ini_data_reports($parent,$category,$course,$depth) {
        /* Variables */
        global $PAGE;
        $name       = null;
        $path       = null;
        $requires   = null;
        $jsmodule   = null;

        try {
            // Initialise variables
            $name       = 'data_rpt';
            $path       = '/report/manager/course_report/js/categoriescourses.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification','datatype-number');

            // Initialise js module
            $jsmodule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires
            );

            // Javascript
            $PAGE->requires->js_init_call('M.core_user.init_managercourse_report',
                array($parent,$category,$course,$depth),
                false,
                $jsmodule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ini_data_reports

    /**
     * Description
     * Get categories by depth
     *
     * @param           $depth
     * @param      null $parent
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/09/2+17
     * @author          eFaktor     (fbv)
     */
    public static function get_my_categories_by_depth($depth,$parent=null) {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;
        $lstcat = array();

        try {
            // First Element of the list
            $lstcat[0] = get_string('selectone', 'report_manager');

            // Search criteria
            $params = array();
            $params['depth'] = $depth;

            // SQL Instruction
            $sql = " SELECT ca.id,
                            ca.name
                     FROM   {course_categories} ca 
                     WHERE  ca.depth = :depth ";


            // Criteria parent
            $dblog = " PARENT : " . $parent . "\n\n";
            if ($parent) {
                $params['parent'] = $parent;

                $sql .= " AND ca.parent = :parent ";
            }//if_parent

            // Execute
            $sql .= " ORDER BY ca.name ";

            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstcat[$instance->id] = $instance->name;
                }//for_rdo
            }//if_rdo

            return $lstcat;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_my_categories_by_depth

    /**
     * Description
     * Get category name
     *
     * @param           $category
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    26/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_category_name($category) {
        // Variables
        global $DB;
        $rdo = null;

        try {
            // Search criteria
            $params = array();
            $params['id'] = $category;

            $rdo = $DB->get_record('course_categories', $params,'name');

            // Gets the category.
            if ($rdo) {
                return $rdo->name;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    /**
     * Description
     * Get all the courses available
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     */
    public static function Get_CoursesList($category=null) {
        /* Variables    */
        global $DB;
        $lstcourses = array();
        $sql        = null;
        $sqlcat     = null;
        $rdo        = null;

        try {
            // First element
            $lstcourses[0] = get_string('selectone', 'report_manager');


            // SQL Instrution - category part
            if ($category) {
                $sqlcat = "  JOIN  {course_categories}	cat ON  cat.id = c.category
                                                            AND (cat.path like '%/$category%' OR cat.path like '%/$category/%')";
            }else {
                $sqlcat = '';

            }

            // SQL Instruction
            $sql = " SELECT   c.id,
                              c.fullname
                     FROM	  {course}    c
                              $sqlcat
                     WHERE	  c.visible = 1 
                        AND   c.id != 1 ";

           $dblog = $sql . "\n";

            // Get courses
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $course) {
                    $dblog .= $course->id . " - " . $course->fullname . "\n\n";

                    $lstcourses[$course->id] =  $course->fullname;
                }//for_rdo
            }//if_Rdo

            global $CFG;
            error_log($dblog, 3, $CFG->dataroot . "/rpt_manager.log");
            return $lstcourses;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesList

    /**
     * @param               $data_form
     * @param               $my_hierarchy
     * @param               $IsReporter
     *
     * @return              null|stdClass
     * @throws              Exception
     *
     * @creationDate        17/03/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get the course report information to display
     *
     * Course Report.
     *      - id
     *      - name
     *      - job_roles.    Array
     *                      [id]    --> industrycode + name
     *      - outcomes.     Array
     *                      [id]
     *                              --> name
     *                              --> expiration
     *      - rpt
     *      - completed_before
     *      - levelZero.    Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> levelOne.   Array
     *
     *      - levelOne. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelTwo.   Array
     *      - levelTwo. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelThree. Array
     *
     *
     *      - levelThree.   Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed.      Array
     *                                              --> name
     *                                              --> completed
     *                          --> not_completed.  Array
     *                                              --> name
     *                          --> not_enrol.      Array
     *                                              --> name
     */
    public static function Get_CourseReportLevel($data_form,$my_hierarchy,$IsReporter) {
        /* Variables    */
        $companies_report   = null;

        $rptcourse      = null;

        $course_id          = null;
        $job_role_list      = null;
        $levelZero          = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $selectorThree      = null;
        $inZero             = null;
        $inOne              = null;
        $inTwo              = null;
        $inThree            = null;
        $selzero            = null;

        try {
            // Course Report - Basic Information
            $course_id  = $data_form[REPORT_MANAGER_COURSE_LIST];
            $rptcourse  = self::Get_CourseBasicInfo($course_id);
            echo "rptcourse : " . $rptcourse->id . "</br>";

            // Get the rest of data to displat
            // Users and status of each user by company
            if ($rptcourse) {
                echo "rptcourse I : " . $rptcourse->id . "</br>";

                $selzero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];

                // Get companies connected with user by level
                if ($IsReporter) {
                    $inOne   = $my_hierarchy->competence[$selzero]->levelone;
                    $inTwo   = $my_hierarchy->competence[$selzero]->leveltwo;
                    $inThree = $my_hierarchy->competence[$selzero]->levelthree;
                }else {
                    list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::get_mycompanies_by_level($my_hierarchy->competence);
                }//if_reporter

                // Job roles selected
                $rptcourse->job_roles = self::Get_JobRolesCourse_Report($data_form);

                //Common for all levels
                $rptcourse->levelzero           = $selzero;
                $rptcourse->zero_name           = CompetenceManager::GetCompany_Name($selzero);
                $rptcourse->rpt                 = $data_form['rpt'];
                $rptcourse->completed_before    = $data_form[REPORT_MANAGER_COMPLETED_LIST];

                // Get level basic info
                switch ($data_form['rpt']) {
                    case 1:
                        // Level one
                        $levelOne = new stdClass();
                        $levelOne->id                           = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                        $levelOne->name                         = CompetenceManager::GetCompany_Name($levelOne->id);
                        $levelOne->leveltwo                     = null;
                        $rptcourse->levelone[$levelOne->id] = $levelOne;

                        break;

                    case 2:
                    case 3:
                        // Level one
                        $levelOne = new stdClass();
                        $levelOne->id                               = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                        $levelOne->name                             = CompetenceManager::GetCompany_Name($levelOne->id);
                        $levelOne->leveltwo                         = null;
                        $rptcourse->levelone[$levelOne->id]     = $levelOne;

                        // Level two
                        $levelTwo = new stdClass();
                        $levelTwo->id                           = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];
                        $levelTwo->name                         = CompetenceManager::GetCompany_Name($levelTwo->id );
                        $levelTwo->levelthree                   = null;
                        $rptcourse->leveltwo[$levelTwo->id] = $levelTwo;

                        break;
                    default:
                        $rptcourse = null;

                        break;
                }//switch_rpt

                echo "rptcourse II : " . $rptcourse->id . "</br>";
            }//if_course_report

            return $rptcourse;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CourseReportLevel

    /**
     * @param           $data
     * @param           $inOne
     * @param           $inTwo
     * @param           $inThree
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    22/04/2016
     * @author          eFaktor     (fbv)
     */
    public static function GetCompaniesEmployees($data,$inOne,$inTwo,$inThree) {
        /* Variables */
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $companies  = null;

        try {
            // Level zero
            $levelZero = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];

            // Rest of the levels
            switch ($data['rpt']) {
                case 0;
                    // Get only companies with employees
                    $companies = CompetenceManager::GetCompanies_WithEmployees($levelZero,$inOne,$inTwo,$inThree);

                    break;
                case 1:
                    $levelOne = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];

                    // Get only companies with employees
                    $companies = CompetenceManager::GetCompanies_WithEmployees($levelZero,$levelOne,$inTwo,$inThree);

                    break;
                case 2:
                    $levelOne = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                    $levelTwo = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];

                    // Get only companies with employees
                    $companies = CompetenceManager::GetCompanies_WithEmployees($levelZero,$levelOne,$levelTwo,$inThree);

                    break;
                case 3:
                    $levelOne   = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                    $levelTwo   = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];

                    if (isset($data[MANAGER_COURSE_STRUCTURE_LEVEL .'3'])) {
                        if (!in_array(0,$data[MANAGER_COURSE_STRUCTURE_LEVEL .'3'])) {
                            $levelThree = $data[MANAGER_COURSE_STRUCTURE_LEVEL .'3'];
                            $inThree    = implode(',',$levelThree);
                        }//if_level_three
                    }//if_levelThree

                    // Get only companies with employees
                    $companies = CompetenceManager::GetCompanies_WithEmployees($levelZero,$levelOne,$levelTwo,$inThree);

                    break;
            }//switch_rpt

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompaniesEmployees

    /**
     * @param       null $courseId
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary table
     */
    public static function CleanTemporary($courseId = null) {
        /* Variables    */
        global $DB;
        $params = array();

        try {
            // Criteria
            $params['manager']  = $_SESSION['USER']->sesskey;
            $params['report']   = 'course';
            if ($courseId) {
                $params['courseid'] = $courseId;
            }//if_outcome

            // Execute
            $DB->delete_records('report_gen_temp',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanTemporary

    /**
     * @param           $course_report
     * @param           $completed_option
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the course report data - Format Screen
     *
     * Course Report.
     *      - id
     *      - name
     *      - job_roles.    Array
     *                      [id]    --> industrycode + name
     *      - outcomes.     Array
     *                      [id]
     *                              --> name
     *                              --> expiration
     *      - rpt
     *      - completed_before
     *      - levelZero.    Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> levelOne.   Array
     *
     *      - levelOne. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelTwo.   Array
     *      - levelTwo. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelThree. Array
     *
     *
     *      - levelThree.   Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed.      Array
     *                                              --> name
     *                                              --> completed
     *                          --> not_completed.  Array
     *                                              --> name
     *                          --> not_enrol.      Array
     *                                              --> name
     *
     */
    public static function Print_CourseReport_Screen($course_report,$completed_option) {
        /* Variables    */
        $out_report = '';

        try {
            // Select the level to display
            switch ($course_report->rpt) {
                case 0:
                    $out_report = self::Print_CourseReport_Screen_LevelZero($course_report,$completed_option);

                    break;
                case 1:
                    $out_report = self::Print_CourseReport_Screen_LevelOne($course_report,$completed_option);

                    break;
                case 2:
                    $out_report = self::Print_CourseReport_Screen_LevelTwo($course_report,$completed_option);

                    break;
                case 3:
                    $out_report = self::Print_CourseReport_Screen_LevelThree($course_report);

                    break;
                default:
                    break;
            }//switch_my_level

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen

    /**
     * @param            $course_report
     * @throws           Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the course report data - Excel Format
     *
     * Course Report.
     *      - id
     *      - name
     *      - job_roles.    Array
     *                      [id]    --> industrycode + name
     *      - outcomes.     Array
     *                      [id]
     *                              --> name
     *                              --> expiration
     *      - rpt
     *      - completed_before
     *      - levelZero.    Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> levelOne.   Array
     *
     *      - levelOne. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelTwo.   Array
     *      - levelTwo. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelThree. Array
     *
     *
     *      - levelThree.   Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed.      Array
     *                                              --> name
     *                                              --> completed
     *                          --> not_completed.  Array
     *                                              --> name
     *                          --> not_enrol.      Array
     *                                              --> name
     */
    public static function Download_CourseReport($course_report) {
        try {
            switch ($course_report->rpt) {
                case 0:
                    self::Download_CourseReport_LevelZero($course_report);

                    break;

                case 1:
                    self::Download_CourseReport_LevelOne($course_report);

                    break;
                case 2:
                    self::Download_CourseReport_LevelTwo($course_report);

                    break;
                case 3:
                    self::Download_CourseReport_LevelThree($course_report);

                    break;
                default:
                    break;
            }//switch_report_level
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * Description
     * Get the information connected to the level one
     *
     * @param           $courserpt
     * @param           $coemployees
     *
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_reportinfo_levelone(&$courserpt,$coemployees) {
        /* Variables */
        $one            = null;
        $two            = null;

        try {
            echo "get_reportinfo_levelone " . "Course : " . $courserpt->id . "</br>";
            // Get information connected with level one
            $one       = CompetenceManager::GetCompaniesInfo($coemployees->levelone);

            // Get level two connected with each one
            foreach ($one as $company) {
                // level two connected
                $two   = self::get_companies_by_level(2,$company->id,$coemployees->leveltwo);

                if ($two) {
                    echo "TWO " . "Course : " . $courserpt->id . "</br>";
                    $two       = self::get_reportinfo_leveltwo($courserpt->id,$two,$coemployees->levelthree);
                    if ($two) {
                        // Info level two
                        $company->leveltwo = $two;

                        // Add
                        $courserpt->levelone[$company->id] = $company;
                    }//if_levelTwo
                }
            }//for_one
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_reportinfo_levelone

    /**
     * Description
     * Get the information connected to the level two
     *
     * @param           $course
     * @param           $two
     * @param           $in
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_reportinfo_leveltwo($course,$two,$in) {
        /* Variables */
        $leveltwo       = null;
        $levelthree     = null;

        try {
            // Get information level two
            foreach ($two as $company) {
                // Get level three connected with
                $three = self::get_companies_by_level(3,$company->id,$in);

                // Level three
                if ($three) {
                    $levelthree = self::get_reportinfo_levelthree_by_two($course,$three);

                    // Add level two
                    if ($levelthree) {
                        // info level three
                        $company->levelthree = $levelthree;

                        // Add level two
                        $leveltwo[$company->id] = $company;
                    }//if_elvelthree
                }//if_$three
            }//for_companies_level_Two

            return $two;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cath
    }//get_reportinfo_leveltwo

    /**
     * Description
     * Get report info level three by two
     *
     * @param           $course
     * @param           $three
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_reportinfo_levelthree_by_two($course,$three) {
        /* variables */
        $levelthree = null;
        $infothree  = null;

        try {
            // level three
            $levelthree = array();

            // Get info level three for level two
            foreach ($three as $infothree) {
                $infothree->completed       = self::get_total_users_course_company($infothree->id,$course,CO_COMPLETED);
                $infothree->not_completed   = self::get_total_users_course_company($infothree->id,$course,CO_NOT_COMPLETED);
                $infothree->not_enrol       = self::get_total_users_noenrol_course_company($infothree->id,$course);

                // Add level three
                if ($infothree->completed || $infothree->not_completed || $infothree->not_enrol) {
                    $levelthree[$infothree->id] = $infothree;
                }//if_users
            }//for three

            return $levelthree;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_reportinfo_levelthree_by_two

    /**
     * Description
     * Get report info by level three
     *
     * @param           $course
     * @param           $three
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_reportinfo_levelthree($course,$three) {
        /* variables */
        $levelthree = null;
        $infothree  = null;

        try {
            // level three
            $levelthree = array();

            // Get info level three for level two
            foreach ($three as $infothree) {
                $infothree->completed       = self::get_users_course_company($infothree->id,$course,CO_COMPLETED);
                $infothree->not_completed   = self::get_users_course_company($infothree->id,$course,CO_NOT_COMPLETED);
                $infothree->not_enrol       = self::get_users_noenrol_course_company($infothree->id,$course);

                // Add level three
                if ($infothree->completed || $infothree->not_completed || $infothree->not_enrol) {
                    $levelthree[$infothree->id] = $infothree;
                }//if_users
            }//for three

            return $levelthree;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cath
    }//get_reportinfo_levelthree

    /**
     * Description
     * Get total users enrolled, completed or nor completed
     *
     * @param           $company
     * @param           $course
     * @param           $type
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_total_users_course_company($company,$course,$type) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['course']   = $course;
            $params['company']  = $company;

            // SQL Instruction
            $sql = " SELECT count(DISTINCT cue.id) as 'total'
                     FROM	course_company_user_enrol cue
                     WHERE 	cue.companyid 	= :company
                        AND cue.courseid 	= :course ";

            // Completed or not completed
            switch ($type) {
                case CO_COMPLETED:
                    $sql .= " AND cue.timecompleted IS NOT NULL 
                              AND cue.timecompleted != 0 ";

                    break;
                case CO_NOT_COMPLETED:
                    $sql .= " AND (cue.timecompleted IS NULL OR cue.timecompleted = 0) ";

                    break;
            }//switch_type

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_users_enrol_course_company

    /**
     * Description
     * get users enrolled by company completed or not
     *
     * @param           $company
     * @param           $course
     * @param           $type
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_users_course_company($company,$course,$type) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['course']   = $course;
            $params['company']  = $company;

            // SQL Instruction
            $sql = " SELECT cue.user,
                            cue.name,
                            cue.timecompleted as 'completed'
                     FROM	course_company_user_enrol cue
                     WHERE 	cue.companyid 	= :company
                        AND cue.courseid 	= :course ";

            // Completed or not completed
            switch ($type) {
                case CO_COMPLETED:
                    $sql .= " AND cue.timecompleted IS NOT NULL 
                              AND cue.timecompleted != 0 ";

                    break;
                case CO_NOT_COMPLETED:
                    $sql .= " AND (cue.timecompleted IS NULL OR cue.timecompleted = 0) ";

                    break;
            }//switch_type

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_users_course_company

    /**
     * Description
     * Get users by company not enrolled in the course
     *
     * @param           $company
     * @param           $course
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_users_noenrol_course_company($company,$course) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['course']   = $course;
            $params['company']  = $company;

            // SQL Instruction
            $sql = " SELECT 	  u.id 								 as 'user',
                                  CONCAT(u.firstname,' ',u.lastname) as 'name',
                                  '0'                                as 'completed'
                     FROM		  {user} 					  u
                        JOIN	  {user_info_competence_data} uic ON  uic.userid 	= u.id
                                                                  AND uic.companyid = :company
                        LEFT JOIN course_company_user_enrol	  cue ON  cue.user 		= uic.userid 
                                                                  AND cue.companyid = uic.companyid
                                                                  AND cue.courseid 	= :course
                     WHERE   u.username != 'guest'
                        AND cue.id IS NULL ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_users_noenrol_course_company

    /**
     * Description
     * Get total users not enrolled
     *
     * @param           $company
     * @param           $course
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_total_users_noenrol_course_company($company,$course) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['course']   = $course;
            $params['company']  = $company;

            // SQL Instruction
            $sql = " SELECT 	  COUNT(DISTINCT u.id) as  'total'
                     FROM		  {user} 						u
                        JOIN	  {user_info_competence_data}	uic	ON 	uic.userid 	  	= u.id
                                                                    AND uic.companyid 	= :company
                        LEFT JOIN course_company_user_enrol		cue ON  cue.user 		= uic.userid 
                                                                    AND cue.companyid 	= uic.companyid
                                                                    AND cue.courseid 	= :course
                     WHERE   u.username != 'guest'
                        AND cue.id IS NULL ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_users_noenrol_course_company


    /**
     * Description
     * Get all companies connected with a specific parent and level
     *
     * @param           $level
     * @param           $parent
     * @param      null $in
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_companies_by_level($level,$parent,$in=null) {
        /* Variables */
        global $DB;
        $sql = null;
        $rdo = null;

        try {
            // Search criteria
            $params = array();
            $params['level']    = $level;

            // SQL Instruction
            $sql = " SELECT	DISTINCT  
                              rcd.id,
                              rcd.name
                     FROM     {report_gen_companydata} 		 rcd 
                        JOIN  {report_gen_company_relation}  rcr ON	rcr.companyid = rcd.id
                                                                 AND rcr.parentid  IN ($parent)
                     WHERE    rcd.hierarchylevel = :level ";

            if ($in) {
                $sql .= " AND   rcd.id IN ($in) ";
            }

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_companies_by_level

    /**
     * @param           $course_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get course basic information.
     * Full name, outcomes connected with ...
     */
    private static function Get_CourseBasicInfo($course_id) {
        /* Variables    */
        global $DB;
        $params         = array();

        try {
            // Search criteria
            $params['course_id'] = $course_id;

            // SQL Instruction
            $sql = " SELECT			DISTINCT 
                                    c.id,
                                    c.fullname as 'name',
                                    GROUP_CONCAT(DISTINCT go.id ORDER BY go.fullname SEPARATOR ',') as 'outcomesid'
                     FROM 			{course}						c
                        LEFT JOIN	{grade_outcomes_courses}		oc		ON 		oc.courseid 	= c.id
                        LEFT JOIN	{grade_outcomes}				go		ON		go.id			= oc.outcomeid
                     WHERE		c.id = :course_id ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                echo "RDO COurse --> " . $rdo->id . "</br>";
                $rdo->job_roles     = null;
                $rdo->levelzero     = null;
                $rdo->levelone      = null;
                $rdo->leveltwo      = null;
                $rdo->levelthree    = null;
                $rdo->outcomes      = null;
                if ($rdo->outcomesid) {
                    $rdo->outcomes   = self::Get_OutcomeDetail($rdo->outcomesid);
                }//if_outcomes
            }

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CourseBasicInfo

    /**
     * @param           $outcomes
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the detail of the outcomes list
     */
    private static function Get_OutcomeDetail($outcomes) {
        /* Variables    */
        global $DB;

        try {
            // SQL Instruction
            $sql = " SELECT			DISTINCT  o.id,
                                              o.fullname          as 'name',
                                              oe.expirationperiod as 'expiration'
                     FROM			{grade_outcomes}		    o
                        LEFT JOIN	{report_gen_outcome_exp}	oe	ON oe.outcomeid = o.id
                     WHERE			o.id IN ($outcomes)
                     ORDER BY		o.fullname ";

            // Execute
            $rdo = $DB->get_records_sql($sql);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeDetail

    /**
     * @param           $data_form
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Job roles connected to the level
     */
    private static function Get_JobRolesCourse_Report($data_form) {
        /* Variables    */
        global $SESSION;
        $job_roles  = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $jr_level   = array();

        try {
            if (!empty($data_form[REPORT_MANAGER_JOB_ROLE_LIST])) {
                $list = join(',',$data_form[REPORT_MANAGER_JOB_ROLE_LIST]);
                $job_roles = CompetenceManager::Get_JobRolesList($list);
                /* Save Job Roles Selected  */
                $SESSION->job_roles = array_keys($job_roles);
            }else {
                /* Job Roles - Outcome          */
                $job_roles = CompetenceManager::Get_JobRolesList();
                $SESSION->job_roles = null;
            }//if_else

            /* Job Roles - Company Level    */
            switch ($data_form['rpt']) {
                case 0:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);

                    break;
                case 1:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);

                    break;
                case 2:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                    $levelTwo  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);

                    break;
                case 3:
                    /* Get Level        */
                    $levelZero  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $levelOne   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                    $levelTwo   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    if (isset($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3']) && ($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'])) {
                        $levelThree = implode(',',$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3']);

                        /* Get Job Roles    */
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,3,$levelZero,$levelOne,$levelTwo,$levelThree);
                    }else {
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);
                    }//if_levelThree

                    break;
            }//switch_level

            if (empty($data_form[REPORT_MANAGER_JOB_ROLE_LIST])) {
                return null;
            }else {
                if (array_intersect_key($job_roles,$jr_level)) {
                    $job_roles = array_intersect_key($job_roles,$jr_level);
                    return $job_roles;
                }else {
                    return $jr_level;
                }//if_intersect
            }//if_selected_levelThree
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesCourse_Report

    /**
     * @param               $course_report
     * @param               $completed_option
     * @return              string
     * @throws              Exception
     *
     * @creationDate        17/03/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get CourseReport Level Zero - Format Screen
     */
    private static function Print_CourseReport_Screen_LevelZero($course_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_one      = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            // Return
            $return_url = new moodle_url('/report/manager/course_report/course_report_level.php',
                                             array('rpt' => $course_report->rpt, 'lz' =>$course_report->levelzero,'c' => $course_report->id));
            $indexUrl   = new moodle_url('/report/manager/index.php');

            // Course report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Course report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // course title
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    // outcomes connected
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $course_report->zero_name  . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    // Expiration before
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Level one
                $levelOne = $course_report->levelone;
                if (!$levelOne) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return selection page
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    // Toogle
                    $url_img  = new moodle_url('/pix/t/expanded.png');
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelOne as $idOne=>$one) {
                            $levelTwo = $one->leveltwo;
                            if ($levelTwo) {
                                $id_toggle_one   = 'YUI_' . $idOne;
                                // Header level one
                                $out_report .= self::Add_CompanyHeader_LevelZero_Screen($one->name,$id_toggle_one,$url_img);

                                // Content level one
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_one_list','id'=> $id_toggle_one . '_div'));
                                    foreach ($levelTwo as $id=>$level) {
                                        $color = 'r0';
                                        $levelThree = $level->levelthree;
                                        if ($levelThree) {
                                            $id_toggle = 'YUI_' . $id;

                                            // Header level two
                                            $out_report .= self::Add_CompanyHeader_Screen($level->name,$id_toggle,$url_img);

                                            // Content level two
                                            $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                                                $out_report .= html_writer::start_tag('div',array('class' => 'company_level'));
                                                    // Header table
                                                    $out_report .= self::Add_HeaderTable_LevelTwo_Screen();
                                                    // Content table
                                                    $out_report .= html_writer::start_tag('table');
                                                        foreach ($levelThree as $id_three=>$company) {
                                                            $url_level_three = new moodle_url('/report/manager/course_report/course_report_level.php',
                                                                                              array('rpt' => '3','co' => $id_three,'lt' => $level->id,'lo'=>$idOne,'opt' => $completed_option));
                                                            $out_report .= self::Add_ContentTable_LevelTwo_Screen($url_level_three,$company,$color);

                                                            // Change color
                                                            if ($color == 'r0') {
                                                                $color = 'r2';
                                                            }else {
                                                                $color = 'r0';
                                                            }
                                                        }//for_level_Three
                                                    $out_report .= html_writer::end_tag('table');
                                                $out_report .= html_writer::end_tag('div');//company_level
                                            $out_report .= html_writer::end_tag('div');//level_two_list
                                        }//if_level_three
                                    }//for_level_two
                                $out_report .= html_writer::end_tag('div');//level_one_list
                            }//if_levelTwo
                        }//for_levelOne
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelOne
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return selection page
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelZero

    /**
     * @param           $course_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Course Report Level One - Format Screen
     */
    private static function Print_CourseReport_Screen_LevelOne($course_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            // return
            $indexUrl   = new moodle_url('/report/manager/index.php');
            $return_url = new moodle_url('/report/manager/course_report/course_report_level.php');

            // Course report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Course report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Course title
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    // Outcomes Connected
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $course_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                        // Level one
                        $levelOne = array_shift($course_report->levelone);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    // Expiration before
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Params to return
                $return_url->params(array('rpt' => $course_report->rpt, 'lz' =>$course_report->levelzero,
                                          'lo' => $levelOne->id,'c' => $course_report->id));

                // Level two
                $levelTwo = $levelOne->leveltwo;
                if (!$levelTwo) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return selection page
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    // Report info
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelTwo as $id=>$level) {
                            $color = 'r0';
                            $levelThree = $level->levelthree;
                            if ($levelThree) {
                                // Toggle
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id;
                                // Header company - level two
                                $out_report .= self::Add_CompanyHeader_Screen($level->name,$id_toggle,$url_img);

                                // Level two list
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                                    $out_report .= html_writer::start_tag('div',array('class' => 'company_level'));
                                        // Header table
                                        $out_report .= self::Add_HeaderTable_LevelTwo_Screen();
                                        // Content table
                                        $out_report .= html_writer::start_tag('table');
                                            foreach ($levelThree as $id_three=>$company) {
                                                $url_level_three = new moodle_url('/report/manager/course_report/course_report_level.php',
                                                                                  array('rpt' => '3','co' => $id_three,'lt' => $level->id,'lo'=>$levelOne->id,'opt' => $completed_option));

                                                // Company header
                                                $out_report .= self::Add_ContentTable_LevelTwo_Screen($url_level_three,$company,$color);

                                                // Change color
                                                if ($color == 'r0') {
                                                    $color = 'r2';
                                                }else {
                                                    $color = 'r0';
                                                }
                                            }//for_level_Three
                                        $out_report .= html_writer::end_tag('table');
                                    $out_report .= html_writer::end_tag('div');//company_level
                                $out_report .= html_writer::end_tag('div');//level_two_list
                            }//if_level_three
                        }//for_level_two
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelTwo
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // return selection page
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelOne

    /**
     * @param           $course_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Course Report Level wo - Format Screen
     */
    private static function Print_CourseReport_Screen_LevelTwo($course_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $color              = null;

        try {
            // Retunr
            $return_url = new moodle_url('/report/manager/course_report/course_report_level.php');
            $indexUrl   = new moodle_url('/report/manager/index.php');

            // Course report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Course name
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    // Outcomes connected
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // Company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $course_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                        // Level one
                        $levelOne = array_shift($course_report->levelone);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        // Level two
                        $levelTwo = array_shift($course_report->leveltwo);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    // Completed last
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Set params
                $return_url->params(array('rpt' => $course_report->rpt, 'lz' =>$course_report->levelzero,
                                          'lo' => $levelOne->id,'lt' => $levelTwo->id,'c' => $course_report->id));

                // Level three
                $levelThree = $levelTwo->levelthree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return selection page
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    // Report info
                    $color = 'r0';
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        $out_report .= html_writer::start_tag('div',array('class' => 'company_level'));
                            // Header table
                            $out_report .= self::Add_HeaderTable_LevelTwo_Screen();
                            // Content table
                            $out_report .= html_writer::start_tag('table');
                                foreach ($levelThree as $id_three=>$company) {
                                    $url_level_three = new moodle_url('/report/manager/course_report/course_report_level.php',
                                                                      array('rpt' => '3','co' => $id_three,'lt' => $levelTwo->id,'lo'=>$levelOne->id,'opt' => $completed_option));
                                    $out_report .= self::Add_ContentTable_LevelTwo_Screen($url_level_three,$company,$color);

                                    // Change color
                                    if ($color == 'r0') {
                                        $color = 'r2';
                                    }else {
                                        $color = 'r0';
                                    }
                                }//for_level_Three
                            $out_report .= html_writer::end_tag('table');
                        $out_report .= html_writer::end_tag('div');//company_level
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelThree
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return selection page
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelTwo


    /**
     * @param           $course_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get Course Report Level Three - Screen Format
     */
    private static function Print_CourseReport_Screen_LevelThree($course_report) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $data               = null;

        try {
            // Return
            $indexUrl   = new moodle_url('/report/manager/index.php');
            $return_url = new moodle_url('/report/manager/course_report/course_report_level.php');

            // Course report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Course report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Course title
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    // Outcomes connected
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // Company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $course_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                        // Level one
                        $levelOne = array_shift($course_report->levelone);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        // Level two
                        $levelTwo = array_shift($course_report->leveltwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    // Expiration before
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Set params
                $return_url->params(array('rpt' => $course_report->rpt,'lz' =>$course_report->levelzero, 'lo' => $levelOne->id,
                                          'lt' => $levelTwo->id,'c' => $course_report->id));

                // Level three
                $levelThree = $course_report->levelthree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return selection page
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    // Report info
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelThree as $id=>$company) {
                            $data = false;
                            if ($company->completed) {
                                // Toggle
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id;
                                $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggle,$url_img);

                                // Info company users
                                $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                                // Header table
                                $out_report .= self::Add_HeaderTable_LevelThree_Screen();
                                // content table
                                $out_report .= self::Add_ContentTable_LevelThree_Screen($company);
                                $out_report .= html_writer::end_tag('div');//courses_list

                                $data = true;
                            }
                        }//for_level_three
                    $out_report .= html_writer::end_tag('div');//outcome_content

                    if (!$data) {
                        $out_report .= '<h3>';
                            $out_report .= get_string('no_completed', 'report_manager',  $options[$course_report->completed_before]);
                        $out_report .= '</h3>';
                    }
                }//if_levelThree
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return selection page
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelThree

    /**
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header for the level zero
     */
    private static function Add_CompanyHeader_LevelZero_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt_levelZero');
            /* Col One  */
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div();//header_col_one

            /* Col Two  */
            $header_company .= html_writer::start_div('header_col_two');
                $header_company .= '<h4>' . $company . '</h4>';
            $header_company .= html_writer::end_div();//header_col_two
        $header_company .= html_writer::end_div();//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add header for level one, two and three
     */
    private static function Add_CompanyHeader_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt');
            /* Col One  */
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div();//header_col_one

            /* Col Two  */
            $header_company .= html_writer::start_div('header_col_two');
                $header_company .= '<h5>' . $company . '</h5>';
            $header_company .= html_writer::end_div();//header_col_two
        $header_company .= html_writer::end_div();//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add table header for level two
     */
    private static function Add_HeaderTable_LevelTwo_Screen() {
        /* Variables    */
        $header_table = null;

        $str_company        = get_string('company','report_manager');
        $str_not_enrol      = get_string('not_start','report_manager');
        $str_not_completed  = get_string('progress','report_manager');
        $str_completed      = get_string('completed','report_manager');
        $str_total          = get_string('count','report_manager');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Empty Col   */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_first'));
                $header_table .= html_writer::end_tag('th');
                /* Company          */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_company'));
                    $header_table .= $str_company;
                $header_table .= html_writer::end_tag('th');
                /* Not Enrol        */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                    $header_table .= $str_not_enrol;
                $header_table .= html_writer::end_tag('th');
                /* Not Completed    */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                    $header_table .= $str_not_completed;
                $header_table .= html_writer::end_tag('th');
                /* Completed        */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                    $header_table .= $str_completed;
                $header_table .= html_writer::end_tag('th');
                /* Total            */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                    $header_table .= $str_total;
                $header_table .= html_writer::end_tag('th');
            $header_table .= html_writer::end_tag('tr');
        $header_table .= html_writer::end_tag('table');

        return $header_table;
    }//Add_HeaderTable_LevelTwo_Screen

    /**
     * @param           $url_level_three
     * @param           $company_info
     * @param           $color
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table for level two
     */
    private static function Add_ContentTable_LevelTwo_Screen($url_level_three,$company_info,$color) {
        /* Variables    */
        $content    = null;
        // Headers
        $str_company        = get_string('company','report_manager');
        $str_not_enrol      = get_string('not_start','report_manager');
        $str_not_completed  = get_string('progress','report_manager');
        $str_completed      = get_string('completed','report_manager');
        $str_total          = get_string('count','report_manager');


        $content .= html_writer::start_tag('tr',array('class' => $color));
            /* Empty Col   */
            $content .= html_writer::start_tag('td',array('class' => 'first'));
            $content .= html_writer::end_tag('td');
            /* Company          */
            $content .= html_writer::start_tag('td',array('class' => 'company','data-th' => $str_company));
                $content .= '<a href="' . $url_level_three . '">' . $company_info->name . '</a>';
            $content .= html_writer::end_tag('td');
            /* Not Enrol        */
            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_not_enrol));
                $content .= $company_info->not_enrol;
            $content .= html_writer::end_tag('td');
            /* Not Completed    */
            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_not_completed));
                $content .= $company_info->not_completed;
            $content .= html_writer::end_tag('td');
            /* Completed        */
            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_completed));
                $content .= $company_info->completed;
            $content .= html_writer::end_tag('td');
            /* Total            */
            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_total));
                $content .= $company_info->completed + $company_info->not_completed + $company_info->not_enrol;
            $content .= html_writer::end_tag('td');
        $content .= html_writer::end_tag('tr');

        return $content;
    }//Add_ContentTable_LevelTwo_Screen

    /**
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the table header for level three
     */
    private static function Add_HeaderTable_LevelThree_Screen() {
        /* Variables    */
        $header_table = null;

        $str_user           = get_string('user');
        $str_state          = get_string('state','local_tracker_manager');
        $str_completion     = get_string('completion_time','local_tracker_manager');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Empty Col   */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_first'));
                $header_table .= html_writer::end_tag('th');

                /* Course Col   */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_course'));
                    $header_table .= $str_user;
                $header_table .= html_writer::end_tag('th');

                /* Status Col   */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                    $header_table .= $str_state;
                $header_table .= html_writer::end_tag('th');

                /* Completion Col   */
                $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                    $header_table .= $str_completion;
                $header_table .= html_writer::end_tag('th');
            $header_table .= html_writer::end_tag('tr');
        $header_table .= html_writer::end_tag('table');

        return $header_table;
    }//Add_HeaderTable_LevelThree_Screen

    /**
     * @param           $company_info
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table for level three
     */
    private static function Add_ContentTable_LevelThree_Screen($company_info) {
        /* Variables    */
        $content        = null;
        $class          = null;
        $label          = null;
        $completed      = null;
        $not_completed  = null;
        $not_enrol      = null;
        // Headers
        $str_user       = get_string('user');
        $str_state      = get_string('state','local_tracker_manager');
        $str_completion = get_string('completion_time','local_tracker_manager');

        $content .= html_writer::start_tag('table');
            /* Completed    */
            $completed = $company_info->completed;
            if ($completed) {
                foreach ($completed as $user) {

                    $content .= html_writer::start_tag('tr',array('class'));
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* User Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $str_user));
                            $content .= $user->name;
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_state));
                            $content .= get_string('outcome_course_finished','local_tracker_manager');;
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_completion));
                            $content .= userdate($user->completed,'%d.%m.%Y', 99, false);
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_completed
            }//if_completed
        $content .= html_writer::end_tag('table');

        return $content;
    }//Add_ContentTable_LevelThree_Screen

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level Zero
     */
    private static function Download_CourseReport_LevelZero($course_report) {
        /* Variables    */
        global $CFG;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* One Sheet By Level two  */
            if ($course_report->levelone) {
                foreach ($course_report->levelone as $levelOne) {
                    foreach ($levelOne->leveltwo as $levelTwo) {
                        $row = 0;
                        // Adding the worksheet
                        $my_xls = $export->add_worksheet($levelTwo->name);

                        /* Add Header - Company Course Report  - Level One */
                        self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,$levelTwo,null,$completed_before,$my_xls,$row);

                        /* Ad Level Two */
                        if ($levelTwo->levelthree) {
                            /* Add Header Table */
                            $row++;
                            self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                            /* Add Content Table    */
                            $row++;
                            foreach ($levelTwo->levelthree as $company) {
                                self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company);

                                $my_xls->merge_cells($row,0,$row,13);
                                $row++;
                            }//for_each_company
                        }//if_level_three
                    }//for_levelTwo
                }//for_levelOne
            }else {
                $row = 0;
                // Adding the worksheet
                $my_xls = $export->add_worksheet($course_report->levelzero);

                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,null,null,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_levelOne


            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelZero

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level One
     */
    private static function Download_CourseReport_LevelOne($course_report) {
        /* Variables    */
        global $CFG;
        $levelOne   = null;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* One Sheet by Level Two   */
            $levelOne = array_shift($course_report->levelone);
            if ($levelOne->leveltwo) {
                foreach ($levelOne->leveltwo as $levelTwo) {
                    $row = 0;
                    // Adding the worksheet
                    $my_xls = $export->add_worksheet($levelTwo->name);

                    /* Add Header - Company Course Report  - Level One */
                    self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,$levelTwo,null,$completed_before,$my_xls,$row);

                    /* Ad Level Two */
                    if ($levelTwo->levelthree) {
                        /* Add Header Table */
                        $row++;
                        self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                        /* Add Content Table    */
                        $row++;
                        foreach ($levelTwo->levelthree as $company) {
                            self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company);

                            $my_xls->merge_cells($row,0,$row,13);
                            $row++;
                        }//for_each_company
                    }//if_level_three
                }//for_levelTwo
            }else {
                $row = 0;
                // Adding the worksheet
                $my_xls = $export->add_worksheet($levelOne->name);

                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,null,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_levelTwo


            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelOne

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Download Course Report - Level Two
     */
    private static function Download_CourseReport_LevelTwo($course_report) {
        /* Variables    */
        global $CFG;
        $levelOne   = null;
        $levelTwo   = null;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Level One   */
            $levelOne = array_shift($course_report->levelone);
            /* Level Two    */
            $levelTwo = array_shift($course_report->leveltwo);

            /* One Sheet by Level Two   */
            $row = 0;
            // Adding the worksheet
            $my_xls    = $export->add_worksheet($levelTwo->name);

            /* Ad Level Two */
            if ($levelTwo->levelthree) {
                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,$levelTwo,null,$completed_before,$my_xls,$row);

                /* Add Header Table */
                $row++;
                self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                /* Add Content Table    */
                $row++;
                foreach ($levelTwo->levelthree as $company) {
                    self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company);

                    $my_xls->merge_cells($row,0,$row,13);
                    $row++;
                }//for_each_company
            }else {
                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelTwo

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level Three
     */
    private static function Download_CourseReport_LevelThree($course_report) {
        /* Variables    */
        global $CFG;
        $levelOne   = null;
        $levelTwo   = null;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            //Sending HTTP headers
            $export->send($file_name);

            /* Level One   */
            $levelOne = array_shift($course_report->levelone);
            /* Level Two    */
            $levelTwo = array_shift($course_report->leveltwo);

            /* Ad Level Two */
            if ($course_report->levelthree) {
                foreach ($course_report->levelthree as $company) {
                    /* One Sheet by Level Three   */
                    $row = 0;
                    // Adding the worksheet
                    $my_xls    = $export->add_worksheet($company->name);

                    /* Add Header - Company Course Report  - Level One */
                    self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,$levelTwo,$company->name,$completed_before,$my_xls,$row);

                    /* Add Header Table     */
                    $row++;
                    self::AddHeader_LevelThree_TableCourse($my_xls,$row);
                    /* Add Content Table    */
                    $row++;
                    self::AddContent_LevelThree_TableCourse($my_xls,$row,$company);

                    $my_xls->merge_cells($row,0,$row,10);
                }//for_each_company
            }else {
                /* One Sheet by Level Three   */
                $row = 0;
                // Adding the worksheet
                $my_xls    = $export->add_worksheet($levelTwo->name);

                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->zero_name,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelThree

    /**
     * @param           $course
     * @param           $outcomes
     * @param           $level_zero
     * @param           $level_one
     * @param           null $level_two
     * @param           null $level_three
     * @param           $completed_before
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Header of the Company Sheet
     */
    private static function AddHeader_CompanySheet($course,$outcomes,$level_zero,$level_one=null,$level_two = null,$level_three = null,$completed_before,&$my_xls,&$row) {
        /* Variables    */
        $col = 0;
        $title_course           = get_string('course');
        $title_outcomes         = get_string('outcomes', 'report_manager');
        $str_outcomes           = array();
        $title_expiration       = str_replace(' ...',' : ',get_string('completed_list','report_manager')) . $completed_before;
        $title_level_zero       = get_string('company_structure_level', 'report_manager', 0) . ': ' . $level_zero;
        $title_level_one        = null;
        if ($level_one) {
            $title_level_one    = get_string('company_structure_level', 'report_manager', 1) . ': ' . $level_one->name;
        }
        $title_level_two        = null;
        if ($level_two) {
            $title_level_two    = get_string('company_structure_level', 'report_manager', 2) . ': ' . $level_two->name;
        }//if_level_two

        try {
            /* Course Title && Course Name*/
            /* Course Name  */
            $my_xls->write($row, $col, $title_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $my_xls->write($row, $col, $course,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Outcome Title && Outcome Names   */
            $row++;
            $my_xls->write($row, $col, $title_outcomes,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            if ($outcomes) {
                foreach ($outcomes as $key=>$outcome) {
                    $str_outcomes[$key] = $outcome->name;
                    $str_outcomes = implode(', ',$str_outcomes);
                }//for_outcomes
            }
            $my_xls->write($row, $col, $str_outcomes,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level Zero    */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_level_zero,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level One    */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_level_one,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level Two    */
            if ($title_level_two) {
                $row++;
                $col = 0;
                $my_xls->write($row, $col, $title_level_two,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }//if_level_two

            /* Level Three  */
            if ($level_three) {
                /* Merge Cells  */
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);

                $row++;
                $col = 0;
                $my_xls->write($row, $col, $level_three,array('size'=>14, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }

            /* Expiration Time */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_expiration,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Merge Cells  */
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CompanySheet

    /**
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table - Level One && Two
     */
    private static function AddHeader_LevelTwo_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_company        = strtoupper(get_string('company','report_manager'));
        $str_not_enrol      = strtoupper(get_string('not_start','report_manager'));
        $str_not_completed  = strtoupper(get_string('progress','report_manager'));
        $str_completed      = strtoupper(get_string('completed','report_manager'));
        $str_total          = strtoupper(get_string('count','report_manager'));
        $col                = 0;

        try {
            /* Company      */
            $my_xls->write($row, $col, $str_company,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Not Enrol    */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_not_enrol,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* In Progress  */
            $col = $col + 2;
            $my_xls->write($row, $col, $str_not_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Completed    */
            $col = $col + 2;
            $my_xls->write($row, $col, $str_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Total        */
            $col = $col + 2;
            $my_xls->write($row, $col, $str_total,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_LevelTwo_TableCourse

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table - Level One && Two
     */
    private static function AddContent_LevelTwo_TableCourse(&$my_xls,&$row,$company_info) {
        /* Variables    */
        $col    = 0;
        $total  = 0;

        try {
            /* Company      */
            $my_xls->write($row, $col, $company_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Not Enrol    */
            $col = $col + 6;
            $my_xls->write($row, $col, $company_info->not_enrol,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* In Progress  */
            $col = $col + 2;
            $my_xls->write($row, $col, $company_info->not_completed,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Completed    */
            $col = $col + 2;
            $my_xls->write($row, $col, $company_info->completed,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Total        */
            $col = $col + 2;
            $total = $company_info->completed + $company_info->not_completed + $company_info->not_enrol;
            $my_xls->write($row, $col, $total,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            $row++;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelTwo_TableCourse

    /**
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table - Level Three
     */
    private static function AddHeader_LevelThree_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_user           = strtoupper(get_string('user'));
        $str_state          = strtoupper(get_string('state','local_tracker_manager'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker_manager'));
        $col                = 0;

        try {
            /* User         */
            $my_xls->write($row, $col, $str_user,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* State        */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_state,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Completion   */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_completion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_LevelThree_TableCourse

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table - Level Three
     */
    private static function AddContent_LevelThree_TableCourse(&$my_xls,&$row,$company_info) {
        /* Variables    */
        $col = null;

        try {
            /* Completed        */
            if ($company_info->completed) {
                foreach ($company_info->completed as $user) {
                    $col = 0;

                    /* User     */
                    $my_xls->write($row, $col, $user->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_finished','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, userdate($user->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row++;
                }//courses_completed
            }//if_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelThree_TableCourse
}//course_report