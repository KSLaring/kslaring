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
 * Library code for the Outcome Report Competence Manager.
 *
 * @package         report
 * @subpackage      manager/outcome_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/03/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Library for the Outcome Report
 */

define('OUTCOME_REPORT_FORMAT_SCREEN', 0);
define('OUTCOME_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('OUTCOME_REPORT_FORMAT_LIST', 'report_format_list');
define('MANAGER_OUTCOME_STRUCTURE_LEVEL','level_');
define('CO_COMPLETED',1);
define('CO_NOT_COMPLETED',2);


class outcome_report {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * Description
     * Initialize the organization structure selectors for outcome report
     *
     * @param           $selector
     * @param           $jrSelector
     * @param           $outSelector
     * @param           $rptLevel
     *
     * @throws          Exception
     *
     * @creationDate    27/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function init_organization_structure_outcome_report($selector,$jrSelector,$outSelector,$rptLevel) {
        /* Variables    */
        global $PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $sp         = null;

        try {
            // Initialise variables
            $name       = 'level_structure';
            $path       = '/report/manager/outcome_report/js/organization.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            // Initialise js module
            $jsModule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires,
                'strings'     => $strings
            );

            $PAGE->requires->js_init_call('M.core_user.init_organization',
                array($selector,$jrSelector,$outSelector,$rptLevel),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//init_organization_structure_outcome_report


    /**
     * Description
     * Get the outcomes list
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     */
    public static function Get_OutcomesList() {
        /* Variables    */
        global $DB;
        $outcome_list = null;

        try {
            // First element
        $outcome_list = array();
            $outcome_list[0] = get_string('select') . '...';

            // SQL Instruction
            $sql = " SELECT     id,
                                fullname
                     FROM       {grade_outcomes}
                     ORDER BY   fullname ASC ";

            // Execute
            if ($rdo = $DB->get_records_sql($sql)) {

                foreach ($rdo as $field) {
                    $outcome_list[$field->id] = $field->fullname;
                }
            }//if_Rdo

            return $outcome_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomesList

    /**
     * @param           $outcome_id
     * @param           null $list
     * @return          array
     * @throws          Exception
     *
     * @updateDate      26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return all job roles connected with a specific outcome.
     */
    public static function Outcome_JobRole_List($outcome_id, $list = null) {
        global $DB;

        /* Job Roles & Course */
        $job_role_list = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome_id'] = $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT		jr.id,
                                jr.name,
                                jr.industrycode
                     FROM		{report_gen_jobrole} 			jr
                        JOIN	{report_gen_outcome_jobrole}	jro		ON  	jro.jobroleid 	= jr.id
                                                                      AND		jro.outcomeid	= :outcome_id
                     ";
            if ($list) {
                $sql = $sql . "WHERE		jr.id IN ({$list}) ";
            }
            $sql = $sql . " ORDER BY 	jr.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $job_role) {
                    $job_role_list[$job_role->id] = $job_role->industrycode . ' - '. $job_role->name;
                }//
            }//if_rdo

            return $job_role_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Outcome_JobRole_List


    /**
     * @param           $data_form
     * @param           $myhierarchy
     * @param           $IsReporter
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcome report information to display
     *
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - courses.      Array
     *                              --> [id]     --> name
     *          - job_roles     Array
     *                              --> [id]    --> name
     *          - levelZero.    Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelOne.     Array.
     *          - levelOne.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelTwo.     Array
     *          - levelTwo.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelThree.   Array
     *          - levelThree.   Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - courses.      Array
     *                                                          [id]
     *                                                              - name
     *                                                              - completed.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                                                          - completed
     *                                                              - not_completed.    Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                              - not_enrol.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *
     *
     * @updateDate      16/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Companies connected with my level and/or my competence
     *
     */
    public static function Get_OutcomeReportLevel($data_form,$myhierarchy,$IsReporter) {
        /* Variables    */
        global $USER;
        $companies_report   = null;
        $outcome_report     = null;
        $outcome_id         = null;
        $job_role_list      = null;
        $selzero            = null;
        $levelZero          = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $selectorThree      = null;
        $inZero             = null;
        $inOne              = null;
        $inTwo              = null;
        $inThree            = null;
        $coemployees        = null;

        try {
            // Outcome report - basic information
            $outcome_id     = $data_form[REPORT_MANAGER_OUTCOME_LIST];
            $outcome_report = self::Get_OutcomeBasicInfo($outcome_id);

            if ($outcome_report) {
                $selzero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];

                //Common for all levels
                $outcome_report->rpt                = $data_form['rpt'];
                $outcome_report->completed_before   = $data_form[REPORT_MANAGER_COMPLETED_LIST];
                $outcome_report->levelzero          = $selzero;
                $outcome_report->zero_name          = CompetenceManager::get_company_name($selzero);


                // Get level basic info
                switch ($data_form['rpt']) {
                    case 1:
                        // Level one
                        $levelOne = new stdClass();
                        $levelOne->id                       = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                        $levelOne->name                     = CompetenceManager::get_company_name($levelOne->id);
                        $levelOne->leveltwo                 = null;
                        $outcome_report->levelone[$levelOne->id] = $levelOne;

                        if ($IsReporter) {
                            list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::extract_reporter_competence_by_level($myhierarchy,$data_form['rpt'],$selzero,$levelOne->id);
                        }

                        break;

                    case 2:
                    case 3:
                        // Level one
                        $levelOne = new stdClass();
                        $levelOne->id                           = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                        $levelOne->name                         = CompetenceManager::get_company_name($levelOne->id);
                        $levelOne->leveltwo                     = null;
                        $outcome_report->levelone[$levelOne->id]     = $levelOne;

                        // Level two
                        $levelTwo = new stdClass();
                        $levelTwo->id                           = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];
                        $levelTwo->name                         = CompetenceManager::get_company_name($levelTwo->id );
                        $levelTwo->levelthree                   = null;
                        $outcome_report->leveltwo[$levelTwo->id]     = $levelTwo;


                if ($IsReporter) {
                            list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::extract_reporter_competence_by_level($myhierarchy,$data_form['rpt'],
                                $selzero,$levelOne->id,$levelTwo->id);
                        }

                        break;
                }//switch_rpt

                if (!$IsReporter) {
                    list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::get_my_companies_by_level($myhierarchy->competence);
                }

                // Job roles selected
                $outcome_report->job_roles = self::Get_JobRolesOutcome_Report($outcome_id,$data_form);
                // Check if there are job roles
                if ($outcome_report->job_roles) {
                    // Companies with employees
                    if ($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']) {
                        $inThree = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'];
                        if (is_array($inThree)) {
                            $inThree = implode(',',$inThree);
                        }
                    }
                    $coemployees = self::GetCompaniesEmployees($data_form,$inOne,$inTwo,$inThree);
                    if ($coemployees) {
                        // Get info courses
                        if ($outcome_report->courses) {
                            // Courses
                            $courses = implode(',',array_keys($outcome_report->courses));

                            // Check level
                            switch ($data_form['rpt']) {
                                case 0:
                                    // Get info connected with level zero
                                    if ($coemployees->levelone) {
                                        self::get_reportinfo_levelone($outcome_report,$coemployees);
                                    }else {
                                        $outcome_report->levelone = null;
                                    }//if_levelOne

                                    break;
                                case 1:
                                    // Get info connected with level one
                                    if ($coemployees->leveltwo) {
                                        $levelTwo   = CompetenceManager::get_companies_info($coemployees->leveltwo);
                                        if ($levelTwo) {
                                            // Get info connected with level two
                                            $levelOne->leveltwo      = self::get_reportinfo_leveltwo($outcome_report,$levelTwo,$coemployees->levelthree);
                                            if ($levelOne->leveltwo) {
                                                $outcome_report->levelone[$levelOne->id]  = $levelOne;
                                            }else {
                                                $levelOne->leveltwo = null;
                                                $outcome_report->levelone[$levelOne->id]  = $levelOne;
                                            }
                                        }else {
                                            $levelOne->leveltwo = null;
                                            $outcome_report->levelone[$levelOne->id] = $levelOne;
                                        }//if_level_two_companies
                                    }else {
                                        $levelOne->leveltwo = null;
                                        $outcome_report->levelone[$levelOne->id] = $levelOne;
                                    }//if_employeees_level_two
                                    break;
                                case 2:
                                    // Get info connected with level two
                                    if ($coemployees->levelthree) {
                                        $levelThree   = CompetenceManager::get_companies_info($coemployees->levelthree);
                                        if ($levelThree) {
                                            // Get info connected with leel three
                                            $levelTwo->levelthree      = self::get_reportinfo_levelthree_by_two($outcome_report,$levelThree);
                                            if ($levelTwo->levelthree) {
                                                $outcome_report->levelTwo[$levelTwo->id] = $levelTwo;
                                            }else {
                                                $levelTwo->levelthree = null;
                                                $outcome_report->leveltwo[$levelTwo->id] = $levelTwo;
                                            }
                                        }else {
                                            $levelTwo->levelthree = null;
                                            $outcome_report->leveltwo[$levelTwo->id] = $levelTwo;
                                        }//if_level_two_companies
                                    }else {
                                        $levelTwo->levelthree = null;
                                        $outcome_report->leveltwo[$levelTwo->id] = $levelTwo;
                                    }//if_employeees_levelthree


                                    break;
                                case 3:
                                    // Get info connected with level three
                                    if ($coemployees->levelthree) {
                                        $levelThree   = CompetenceManager::get_companies_info($coemployees->levelthree);

                                        // Level three
                                        if ($levelThree) {
                                            $three = self::get_reportinfo_levelthree($outcome_report,$levelThree);
                                            if ($three) {
                                                $outcome_report->levelthree = $three;
                                            }else {
                                                $outcome_report->levelthree = null;
                                            }
                                        }else {
                                            $outcome_report->levelthree = null;
                                        }//if_levelThree
                                    }else {
                                        $outcome_report->levelthree = null;
                                    }//if_employees_levelthree

                                    break;
                                default:
                                    break;
                            }//switch_report
                        }//if_courses
                    }//if_coemployees
                }//if_job_roles
            }//if_outcome_report

            return $outcome_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeReportLevel

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
            $levelZero = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];

            // Rest of the levels
            switch ($data['rpt']) {
                case 0;
                    // Get only companies with employees
                    $companies = CompetenceManager::get_Companies_with_employees($levelZero,$inOne,$inTwo,$inThree);

                    break;
                case 1:
                    $levelOne = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];

                    // Get only companies with employees
                    $companies = CompetenceManager::get_Companies_with_employees($levelZero,$levelOne,$inTwo,$inThree);

                    break;
                case 2:
                    $levelOne = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                    $levelTwo = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];

                    // Get only companies with employees
                    $companies = CompetenceManager::get_Companies_with_employees($levelZero,$levelOne,$levelTwo,$inThree);

                    break;
                case 3:
                    $levelOne   = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                    $levelTwo   = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];

                    if (isset($data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'])) {
                        if (!in_array(0,$data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'])) {
                            $levelThree = $data[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'];
                            $inThree    = implode(',',$levelThree);
                        }//if_level_three
                    }//if_levelThree

                    // Get only companies with employees
                    $companies = CompetenceManager::get_Companies_with_employees($levelZero,$levelOne,$levelTwo,$inThree);

                    break;
            }//switch_rpt

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompaniesEmployees

    /**
     * @param       null $outcomeId
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary table
     */
    public static function CleanTemporary($outcomeId = null) {
        /* Variables    */
        global $DB;
        $params = array();

        try {
            // Search criteria
            $params['manager']  = $_SESSION['USER']->sesskey;
            $params['report']   = 'outcome';
            if ($outcomeId) {
                $params['outcomeid'] = $outcomeId;
            }//if_outcome

            // Execute
            $DB->delete_records('report_gen_temp',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanTemporary

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the outcome report data - Format Screen
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - courses.      Array
     *                              --> [id]     --> name
     *          - job_roles     Array
     *                              --> [id]    --> name
     *          - levelZero.    Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelOne.     Array.
     *          - levelOne.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelTwo.     Array
     *          - levelTwo.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelThree.   Array
     *          - levelThree.   Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - courses.      Array
     *                                                          [id]
     *                                                              - name
     *                                                              - completed.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                                                          - completed
     *                                                              - not_completed.    Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                              - not_enrol.        Array
     *                                                                                      [id]
     *                                                                                          - name
     */
    public static function Print_OutcomeReport_Screen($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';

        try {
            switch ($outcome_report->rpt) {
                case 0:
                    $out_report = self::Print_OutcomeReport_Screen_LevelZero($outcome_report,$completed_option);

                    break;
                case 1:
                    $out_report = self::Print_OutcomeReport_Screen_LevelOne($outcome_report,$completed_option);

                    break;
                case 2:
                    $out_report = self::Print_OutcomeReport_Screen_LevelTwo($outcome_report,$completed_option);

                    break;
                case 3:
                    $out_report = self::Print_OutcomeReport_Screen_LevelThree($outcome_report);

                    break;
                default:
                    break;
            }//switch_my_level

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen

    /**
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the outcome report data - Format Excel
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - courses.      Array
     *                              --> [id]     --> name
     *          - job_roles     Array
     *                              --> [id]    --> name
     *          - levelZero.    Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelOne.     Array.
     *          - levelOne.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelTwo.     Array
     *          - levelTwo.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelThree.   Array
     *          - levelThree.   Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - courses.      Array
     *                                                          [id]
     *                                                              - name
     *                                                              - completed.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                                                          - completed
     *                                                              - not_completed.    Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                              - not_enrol.        Array
     *                                                                                      [id]
     *                                                                                          - name
     */
    public static function Download_OutcomeReport($outcome_report) {
        try {
            switch ($outcome_report->rpt) {
                case 0:
                    self::Download_OutcomeReport_LevelZero($outcome_report);

                    break;
                case 1:
                    self::Download_OutcomeReport_LevelOne($outcome_report);

                    break;
                case 2:
                    self::Download_OutcomeReport_LevelTwo($outcome_report);

                    break;
                case 3:
                    self::Download_OutcomeReport_LevelThree($outcome_report);

                    break;
                default:
                    break;
            }//switch_report_level
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    private static function get_reportinfo_levelone(&$outcomerpt,$coemployees) {
        /* Variables    */
        $one            = null;
        $two            = null;

        try {
            // Get information connected with level one
            $one       = CompetenceManager::get_companies_info($coemployees->levelone);

            // Get level two connected with each one
            foreach ($one as $company) {
                // level two connected
                $two   = self::get_companies_by_level(2,$company->id,$coemployees->leveltwo);

                if ($two) {
                    $two       = self::get_reportinfo_leveltwo($outcomerpt,$two,$coemployees->levelthree);
                    if ($two) {
                        // Info level two
                        $company->leveltwo = $two;

                        // Add
                        $outcomerpt->levelone[$company->id] = $company;
                    }//if_levelTwo
                }
            }//for_one
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_reportinfo_levelone

    private static function get_reportinfo_leveltwo($outcomerpt,$two,$in) {
        /* Variables    */
        $leveltwo       = null;
        $levelthree     = null;

        try {
            // Get information level two
            foreach ($two as $company) {
                // Get level three connected with
                $three = self::get_companies_by_level(3,$company->id,$in);

                // Level three
                if ($three) {
                    $levelthree = self::get_reportinfo_levelthree_by_two($outcomerpt,$three);

                    // Add level two
                    if ($levelthree) {
                        // info level three
                        $company->levelthree = $levelthree;

                        // Add level two
                        $leveltwo[$company->id] = $company;
                    }//if_elvelthree
                }//if_$three
            }//for_companies_level_Two

            return $leveltwo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cath
    }//get_reportinfo_leveltwo

    /**
     * Description
     * Get report info level three by two
     *
     * @param           $outcomerpt
     * @param           $three
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_reportinfo_levelthree_by_two($outcomerpt,$three) {
        /* variables */
        $levelthree = null;
        $infothree  = null;

        try {
            // level three
            $levelthree = array();

            // Get info level three for level two
            foreach ($three as $infothree) {
                // Company info
                $company = new stdClass();
                $company->name       = $company;
                $company->id         = $infothree->id;
                $company->courses    = array();
                $coinfo              = null;

                // Get info courses
                foreach ($outcomerpt->courses as $id_course=>$course) {
                    // Course info
                    $coinfo = new stdClass();
                    $coinfo->name            = $course;
                    $coinfo->completed       = self::get_total_users_course_company($infothree->id,$course,CO_COMPLETED,$outcomerpt->completed_before,$outcomerpt->expiration);
                    $coinfo->not_completed   = self::get_total_users_course_company($infothree->id,$course,CO_NOT_COMPLETED,$outcomerpt->completed_before,$outcomerpt->expiration);
                    $coinfo->not_enrol       = self::get_total_users_noenrol_course_company($infothree->id,$course);

                    // Add course info
                    if ($coinfo->completed || $coinfo->not_completed || $coinfo->not_enrol) {
                        $company->courses[$id_course] = $coinfo;;
                    }//if_uses
                }//for_courses

                // Add level three
                if ($company->courses) {
                    $levelthree[$infothree->id] = $company;
                }//if_courses
            }//for three

            return $levelthree;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_reportinfo_levelthree_by_two

    private static function get_reportinfo_levelthree($outcomerpt,$three) {
        /* variables */
        $levelthree = null;
        $infothree  = null;

        try {
            // level three
            $levelthree = array();

            // Get info level three for level two
            foreach ($three as $infothree) {
                // Company info
                $company = new stdClass();
                $company->name       = $company;
                $company->id         = $infothree->id;
                $company->courses    = array();
                $coinfo              = null;

                // Get info courses
                foreach ($outcomerpt->courses as $id_course=>$course) {
                    // Course info
                    $coinfo = new stdClass();
                    $coinfo->name            = $course;
                    $coinfo->completed       = self::get_users_course_company($infothree->id,$course,CO_COMPLETED,$outcomerpt->completed_before,$outcomerpt->expiration);
                    $coinfo->not_completed   = self::get_users_course_company($infothree->id,$course,CO_NOT_COMPLETED,$outcomerpt->completed_before,$outcomerpt->expiration);
                    $coinfo->not_enrol       = self::get_users_noenrol_course_company($infothree->id,$course);

                    // Add course info
                    if ($coinfo->completed || $coinfo->not_completed || $coinfo->not_enrol) {
                        $company->courses[$id_course] = $coinfo;;
                    }//if_uses
                }//for_courses

                // Add level three
                if ($company->courses) {
                    $levelthree[$infothree->id] = $company;
                }//if_courses
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
     * @param           $completednext
     * @param           $expiration
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_total_users_course_company($company,$course,$type,$completednext,$expiration) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $timeIni        = null;
        $timeFin        = null;

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
                    // Get range of dates
                    list($timeIni,$timeFin) = self::GetExpirationIntervalsTime($completednext);
                    $params['ini'] = $timeIni;
                    $params['end'] = $timeFin;

                    $sql .= " AND cue.timecompleted IS NOT NULL 
                              AND cue.timecompleted != 0 
                              AND date_add(FROM_UNIXTIME(timecompleted), INTERVAL $expiration MONTH) BETWEEN FROM_UNIXTIME(:ini) AND FROM_UNIXTIME(:end) ";

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
     * @param           $completednext
     * @param           $expiration
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_users_course_company($company,$course,$type,$completednext,$expiration) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $timeIni    = null;
        $timeFin    = null;

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
                    // Get range of dates
                    list($timeIni,$timeFin) = self::GetExpirationIntervalsTime($completednext);
                    $params['ini'] = $timeIni;
                    $params['end'] = $timeFin;

                    $sql .= " AND cue.timecompleted IS NOT NULL 
                              AND cue.timecompleted != 0 
                              AND date_add(FROM_UNIXTIME(timecompleted), INTERVAL $expiration MONTH) BETWEEN FROM_UNIXTIME(:ini) AND FROM_UNIXTIME(:end) ";

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
        /* Variables    */
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
            $rdo = $DB->get_records_sql($sql,$params);

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
     * @param           $outcome_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected to the outcome
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - course.       Array
     *                  -> [id] --> name
     *          - job_roles
     *          - levelZero
     *          - levelOne
     *          - levelTwo
     *          - levelThree
     */
    private static function Get_OutcomeBasicInfo($outcome_id) {
        /* Variables    */
        global $DB;
        $outcome_report  = null;

        try {
            // Search criteria
            $params = array();
            $params['outcome'] =  $outcome_id;

            // SQL instruction - get outcomes information
            $sql = " SELECT		    o.id,
                                    o.fullname,
                                    o.description,
                                    IF(oe.expirationperiod,oe.expirationperiod,0) as 'expiration',
                                    GROUP_CONCAT(DISTINCT oc.courseid ORDER BY oc.courseid SEPARATOR ',') as 'coursesid'
                     FROM			{grade_outcomes}			    o
                        JOIN		{grade_outcomes_courses}	    oc	ON  oc.outcomeid    = o.id
                        LEFT JOIN	{report_gen_outcome_exp}	    oe	ON  oe.outcomeid    = oc.outcomeid
                     WHERE			o.id = :outcome ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Outcome report
                $outcome_report = new stdClass();
                $outcome_report->id             = $rdo->id;
                $outcome_report->name           = $rdo->fullname;
                $outcome_report->description    = $rdo->description;
                $outcome_report->expiration     = $rdo->expiration;
                $outcome_report->courses        = self::Get_CourseDetail($rdo->coursesid);
                $outcome_report->job_roles      = null;
                $outcome_report->levelzero      = null;
                $outcome_report->levelone       = null;
                $outcome_report->leveltwo       = null;
                $outcome_report->levelthree     = null;
            }//if_rdo

            return $outcome_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeBasicInfo


    /**
     * @param           $courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the detail of the courses
     */
    private static function Get_CourseDetail($courses) {
        /* Variables    */
        global $DB;
        $courses_lst    = array();
        $course_info    = null;

        try {
            if ($courses) {
                // SQL instruction - Course detail
                $sql = " SELECT		c.id,
                                    c.fullname
                         FROM		{course}			        c
                         WHERE		c.visible = 1
                            AND     c.id IN ($courses)
                         ORDER BY 	c.fullname ";

                // Execute
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    foreach ($rdo as $course) {
                        $courses_lst[$course->id] = $course->fullname;
                    }//for_Rdo_course
                }//if_rdo
            }//if_courses

            return $courses_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CourseDetail

    /**
     * @param           $outcome_id
     * @param           $data_form
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected to the outcome and the company level
     */
    private static function Get_JobRolesOutcome_Report($outcome_id,$data_form) {
        /* Variables    */
        global $SESSION;
        $job_roles  = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $jr_level   = array();

        try {
            // Get job roles connected with
            if (!empty($data_form[REPORT_MANAGER_JOB_ROLE_LIST])) {
                // Selected
                $list = join(',',$data_form[REPORT_MANAGER_JOB_ROLE_LIST]);
                $job_roles = self::Outcome_JobRole_List($outcome_id,$list);
            }else {
                // all connected
                $job_roles = self::Outcome_JobRole_List($outcome_id);
            }//if_else

            // Save job roles
            $SESSION->job_roles = array_keys($job_roles);

            // Job roles by level
            switch ($data_form['rpt']) {
                case 0:
                    // Get level
                    $levelZero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];

                    // Jobroles generics
                    if (CompetenceManager::is_public($levelZero)) {
                        CompetenceManager::get_jobroles_generics($jr_level);
                    }//if_public

                    //job roles connected with level
                    CompetenceManager::get_jobroles_hierarchy($jr_level,0,$levelZero);

                    break;
                case 1:
                    // Get level
                    $levelZero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];

                    // Job roles generics
                    if (CompetenceManager::is_public($levelZero)) {
                        CompetenceManager::get_jobroles_generics($jr_level);
                    }//if_public

                    // job roles connected with level
                    CompetenceManager::get_jobroles_hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::get_jobroles_hierarchy($jr_level,1,$levelZero,$levelOne);

                    break;
                case 2:
                    // Get level
                    $levelZero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                    $levelTwo  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];

                    // Job roles generics
                    if (CompetenceManager::is_public($levelZero)) {

                        CompetenceManager::get_jobroles_generics($jr_level);
                    }//if_public

                    // Job roles connected with level
                    CompetenceManager::get_jobroles_hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::get_jobroles_hierarchy($jr_level,1,$levelZero,$levelOne);
                    CompetenceManager::get_jobroles_hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);

                    break;
                case 3:
                    // Get level
                    $levelZero  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                    $levelOne   = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                    $levelTwo   = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];

                    // Job roles generics
                    if (CompetenceManager::is_public($levelZero)) {
                        CompetenceManager::get_jobroles_generics($jr_level);
                    }//if_public

                    // Job roles connected with level
                    if (isset($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']) && ($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'])) {
                        // level selected
                        $levelThree = implode(',',$data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']);
                        CompetenceManager::get_jobroles_hierarchy($jr_level,3,$levelZero,$levelOne,$levelTwo,$levelThree);
                    }else {
                        // All level three
                        CompetenceManager::get_jobroles_hierarchy($jr_level,0,$levelZero);
                        CompetenceManager::get_jobroles_hierarchy($jr_level,1,$levelZero,$levelOne);
                        CompetenceManager::get_jobroles_hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);
                    }//if_levelThree

                    break;
            }//switch_level

            if (array_intersect_key($job_roles,$jr_level)) {
                $job_roles = array_intersect_key($job_roles,$jr_level);
                return $job_roles;
            }else {
                return $job_roles;
            }//if_intersect
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesOutcome_Report

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
     * @param           $index
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    15/04/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get expiration intervals dates
     */
    private static function GetExpirationIntervalsTime($index) {
        /* Variables    */
        $timeIni    = null;
        $timeFin    = null;
        $time       = null;

        try {
            // Local time
            $time = usertime(time());

            // Start and end time
            $timeIni = strtotime('today', $time);

            switch ($index) {
                case 0:
                    // Expired in one day
                    $timeFin = strtotime(1  . ' day', $timeIni);

                    break;
                case 1:
                    // Expired in 1 week
                    $timeFin = strtotime(1  . ' week', $timeIni);

                    break;
                case 2:
                    // Expired in two weeks
                    $timeFin = strtotime(2  . ' weeks', $timeIni);

                    break;
                case 3:
                    // Expried in 3 weeks
                    $timeFin = strtotime(3  . ' weeks', $timeIni);


                    break;
                case 4:
                    // Expired 1 month
                    $timeFin = strtotime(1  . ' month', $timeIni);

                    break;
                case 5:
                    // Expired in 2 months
                    $timeFin = strtotime(2  . ' month', $timeIni);

                    break;
                case 6:
                    // Expired in 3 months
                    $timeFin = strtotime(3  . ' month', $timeIni);

                    break;
                case 7:
                    // Expired in 4 months
                    $timeFin = strtotime(4  . ' month', $timeIni);

                    break;
                case 8:
                    // Expired in 5 months
                    $timeFin = strtotime(5  . ' month', $timeIni);

                    break;
                case 9:
                    // Expired in 6 months
                    $timeFin = strtotime(6  . ' month', $timeIni);

                    break;
                case 10:
                    // Expired next year
                    $timeFin = strtotime(1  . ' year', $timeIni);

                    break;
                case 11:
                    // Expired in 2 years
                    $timeFin = strtotime(2  . ' years', $timeIni);

                    break;
                default:
                    $timeIni    = 0;
                    $timeFin    = 0;
            }//index

            return array($timeIni,$timeFin);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetExpirationIntervalsTime

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome report - Level Zero - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelZero($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggleOne       = null;
        $id_toggleTwo       = null;
        $return_url         = null;
        $indexUrl           = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $urlthree           = null;

        try {
            // Return
            $return_url = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',
                array('rpt' => $outcome_report->rpt, 'lz' =>$outcome_report->levelzero,'ot' => $outcome_report->id));
            $indexUrl   = new moodle_url('/report/manager/index.php');


            // Outcome report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Outcome title
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    // Outcome description
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // Company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $outcome_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';
                    // Expiration before
                    $options = CompetenceManager::get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Level one
                $levelOne = $outcome_report->levelone;
                if (!$levelOne) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return selection page
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    $urlthree = new moodle_url('/report/manager/outcome_report/outcome_report_level.php');

                    // Report info - toogle
                    $url_img  = new moodle_url('/pix/t/expanded.png');
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelOne as $idOne=>$one) {
                            // Level two
                            $levelTwo = $one->leveltwo;
                            if ($levelTwo) {
                                $id_toggle   = 'YUI_' . $idOne;
                                $out_report .= self::Add_CompanyHeader_LevelZero_Screen($one->name,$id_toggle,$url_img);
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_one_list','id'=> $id_toggle . '_div'));
                                    foreach ($levelTwo as $idTwo=>$companyTwo) {
                                        if ($companyTwo->levelthree) {
                                            // Toogle
                                            $id_toggleOne = $id_toggle . '_' . $idTwo;
                                            $out_report .= self::Add_CompanyHeader_LevelOne_Screen($companyTwo->name,$id_toggleOne,$url_img);

                                            // Three
                                            $levelThree = $companyTwo->levelthree;
                                            $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggleOne . '_div'));
                                                foreach ($levelThree as $id_Three=>$company) {
                                                    if ($company->courses) {
                                                        // Toogle
                                                        $id_toggleThree = $id_toggleOne . '_'. $id_Three;

                                                        // Header company
                                                        $urlthree->remove_params();
                                                        $urlthree->params(array('rpt' => '3','co' => $id_Three,'lt' => $idTwo,'lo'=>$idOne,
                                                                                'lz' =>$outcome_report->levelzero, 'ot' => $outcome_report->id,
                                                                                'opt' => $completed_option));
                                                        $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggleThree,$url_img,$urlthree);

                                                        // Info company courses
                                                        $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggleThree . '_div'));
                                                            $out_report .= html_writer::start_tag('table');
                                                                $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                                                $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($company->courses);
                                                            $out_report .= html_writer::end_tag('table');
                                                        $out_report .= html_writer::end_tag('div');//courses_list
                                                    }//if_courses
                                            }//for_levelThree
                                            $out_report .= html_writer::end_tag('div');//level_two_list
                                        }//if_levelThree
                                    }//for_level_two
                                $out_report .= html_writer::end_tag('div');//level_one_list
                            }//if_levelTwo
                        }//for_levelOne
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelOne

            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return to selection page
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelZero

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome report - Level One - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelOne($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggleThree     = null;
        $return_url         = null;
        $urlthree           = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            // Return
            $return_url = new moodle_url('/report/manager/course_report/outcome_report_level.php');
            $indexUrl   = new moodle_url('/report/manager/index.php');

            // Outcome report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Outcome title
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    // Description
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // Company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $outcome_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                        // Level  one
                        $levelOne = array_shift($outcome_report->levelone);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    // Expiration before
                    $options = CompetenceManager::get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Params to return
                $return_url->params(array('rpt' => $outcome_report->rpt, 'lz' =>$outcome_report->levelzero,
                                          'lo' => $levelOne->id,'ot' => $outcome_report->id));

                // Level two
                $levelTwo = $levelOne->leveltwo;
                if (!$levelTwo) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    $urlthree = new moodle_url('/report/manager/outcome_report/outcome_report_level.php');

                    // Report info
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelTwo as $id_Two=>$companyTwo) {
                            // Level three
                            $levelThree = $companyTwo->levelthree;
                            if ($levelThree) {
                                // Toogle
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id_Two;
                                // Header - level two
                                $out_report .= self::Add_CompanyHeader_LevelZero_Screen($companyTwo->name,$id_toggle,$url_img);

                                // Add companies
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                                    foreach ($levelThree as $id_Three=>$company) {
                                        if ($company->courses) {
                                            // Toogle
                                            $id_toggleThree = $id_toggle . '_'. $id_Three;

                                            // Header - Three
                                            $urlthree->remove_params();
                                            $urlthree->params(array('rpt' => '3','co' => $id_Three,'lt' => $id_Two,'lo'=>$levelOne->id,
                                                                    'lz' =>$outcome_report->levelzero, 'ot' => $outcome_report->id,
                                                                    'opt' => $completed_option));
                                            $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggleThree,$url_img,$urlthree);

                                            // Info company courses
                                            $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggleThree . '_div'));
                                                $out_report .= html_writer::start_tag('table');
                                                    $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                                    $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($company->courses);
                                                $out_report .= html_writer::end_tag('table');
                                            $out_report .= html_writer::end_tag('div');//courses_list
                                        }//if_courses
                                    }//for_levelThree
                                $out_report .= html_writer::end_tag('div');//level_two_list
                            }//if_levelThree
                        }//for_levelTwo
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelTwo
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelOne

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report - Level Two - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelTwo($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $return_url         = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $url_levelThree     = null;

        try {
            // Return
            $return_url = new moodle_url('/report/manager/course_report/outcome_report_level.php');
            $indexUrl   = new moodle_url('/report/manager/index.php');

            // Outcome report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Outcome title
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    // Outcome description
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // Company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $outcome_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                        // Level one
                        $levelOne = array_shift($outcome_report->levelone);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        // Level two
                        $levelTwo = array_shift($outcome_report->leveltwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    // Expiration before
                    $options = CompetenceManager::get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Set params
                $return_url->params(array('rpt' => $outcome_report->rpt, 'lz' =>$outcome_report->levelzero,
                                          'lo' => $levelOne->id,'lt' => $levelTwo->id,'ot' => $outcome_report->id));

                // Level three
                $levelThree = $levelTwo->levelthree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    // Report info
                    if ($levelThree) {
                        $urlthree = new moodle_url('/report/manager/outcome_report/outcome_report_level.php');

                        $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                            foreach ($levelThree as $id=>$company) {
                                if ($company->courses) {
                                    // Toogle
                                    $url_img  = new moodle_url('/pix/t/expanded.png');
                                    $id_toggle = 'YUI_' . $id;

                                    // Header three
                                    $urlthree->remove_params();
                                    $urlthree->params(array('rpt' => '3','co' => $id,'lt' => $levelTwo->id,'lo'=>$levelOne->id,
                                                            'lz' =>$outcome_report->levelzero, 'ot' => $outcome_report->id,
                                                            'opt' => $completed_option));
                                    $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggle,$url_img,$urlthree);

                                    // Info company courses
                                    $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                                        $out_report .= html_writer::start_tag('table');
                                            $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                            $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($company->courses);
                                        $out_report .= html_writer::end_tag('table');
                                    $out_report .= html_writer::end_tag('div');//courses_list
                                }//if_courses
                            }//for_level_three
                        $out_report .= html_writer::end_tag('div');//outcome_content
                    }//if_level_three
                }//if_levelTwo
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelTwo

    /**
     * @param           $outcome_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report - Level Three - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelThree($outcome_report) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_course   = null;
        $return_url         = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $courses            = null;
        $data               = false;

        try {
            // Return
            $return_url = new moodle_url('/report/manager/course_report/outcome_report_level.php');
            $indexUrl   = new moodle_url('/report/manager/index.php');

            // Outcome report
            $out_report .= html_writer::start_div('outcome_rpt_div');
                // Report header
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    // Outcome title
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    // Outcome description
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    // Job roles
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    // Company levels
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        // Level zero
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . $outcome_report->zero_name . '</h3>';
                        $out_report .= '</li>';
                        // Level one
                        $levelOne = array_shift($outcome_report->levelone);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        // Level two
                        $levelTwo = array_shift($outcome_report->leveltwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    // Expiration before
                    $options = CompetenceManager::get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                // Set params
                $return_url->params(array('rpt' => $outcome_report->rpt, 'lz' =>$outcome_report->levelzero,
                                          'lo' => $levelOne->id,'lt' => $levelTwo->id,'ot' => $outcome_report->id));

                // Level three
                $levelThree = $outcome_report->levelthree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    // Return
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
                    $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

                    // Report info
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelThree as $id=>$company) {
                            // Company info
                            if ($company->courses) {
                                // Toogle
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id;

                                // Header - two
                                $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggle,$url_img);

                                // Info company users
                                $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                                    $courses = $company->courses;
                                    foreach ($courses as $id_course=>$course) {
                                        if ($course->completed) {
                                            $id_toggle_course = $id_toggle . '_'. $id_course;

                                            // Header table
                                            $out_report .= self::Add_CourseHeader_Screen($course->name,$id_toggle_course,$url_img);

                                            // Users table
                                            $out_report .= html_writer::start_tag('div',array('class' => 'user_list','id'=> $id_toggle_course . '_div'));
                                                $out_report .= html_writer::start_tag('table');
                                                    $out_report .= self::Add_HeaderTable_LevelThree_Screen();
                                                    $out_report .= self::Add_ContentTable_LevelThree_Screen($course,$outcome_report->expiration);
                                                $out_report .= html_writer::end_tag('table');
                                            $out_report .= html_writer::end_tag('div');//user_list

                                            $data = true;
                                        }
                                    }//for_courses
                                $out_report .= html_writer::end_tag('div');//courses_list
                            }//if_courses
                        }//for_level_three
                    $out_report .= html_writer::end_tag('div');//company_content

                    if (!$data) {
                        $out_report .= '<h3>';
                            $out_report .= get_string('no_out_completed', 'report_manager',  $options[$outcome_report->completed_before]);
                        $out_report .= '</h3>';
                    }
                }//if_levelThree
            $out_report .= html_writer::end_div();//outcome_rpt_div

            // Return
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));
            $out_report .= html_writer::link($indexUrl,get_string('return_main_report','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelThree


    /**
     * Description
     * Add the header for the level Zero
     *
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_CompanyHeader_LevelZero_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt_levelZero');
            // Col one
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div();//header_col_one

            // Col two
            $header_company .= html_writer::start_div('header_col_two');
                $header_company .= '<h4>' . $company . '</h4>';
            $header_company .= html_writer::end_div();//header_col_two
        $header_company .= html_writer::end_div();//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * Description
     * Add the header for the level One
     *
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_CompanyHeader_LevelOne_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt_levelOne');
            // Col one
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div();//header_col_one

            // Col two
            $header_company .= html_writer::start_div('header_col_two');
                $header_company .= '<h4>' . $company . '</h4>';
            $header_company .= html_writer::end_div();//header_col_two
        $header_company .= html_writer::end_div();//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * Description
     * Add the company header
     *
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @param       null $url_levelThree
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_CompanyHeader_Screen($company,$toogle,$img,$url_levelThree = null) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt');
            // Col one
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div();//header_col_one

            // Col two
            $header_company .= html_writer::start_div('header_col_two');
                if ($url_levelThree) {
                    echo $url_levelThree . "</br>";
                    $header_company .= '<a href="' . $url_levelThree . '">' . '<h5>' . $company . '</h5>' . '</a>';
                }else {
                    $header_company .= '<h5>' . $company . '</h5>';
                }//if_levelThree

            $header_company .= html_writer::end_div();//header_col_two
        $header_company .= html_writer::end_div();//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * Description
     * Add the course header
     *
     * @param           $course
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_CourseHeader_Screen($course,$toogle,$img) {
        /* Variables    */
        $header_course     = null;
        $title_company     = null;

        $header_course .= html_writer::start_div('header_outcome_company_rpt_levelCourse');
            // col one
            $header_course .= html_writer::start_div('header_col_one');
                $header_course .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_course .= html_writer::end_div();//header_col_one

            // Col two
            $header_course .= html_writer::start_div('header_col_two');
                $header_course .= '<h5>' . $course . '</h5>';
            $header_course .= html_writer::end_div();//header_col_two
        $header_course .= html_writer::end_div();//header_outcome_company_rpt

        return $header_course;
    }//Add_CompanyHeader_Screen

    /**
     * Description
     * Add the header table for the level Two
     *
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_HeaderCourseTable_LevelTwo_Screen() {
        /* Variables    */
        $header_table = null;

        $str_course         = get_string('course');
        $str_not_enrol      = get_string('not_start','report_manager');
        $str_not_completed  = get_string('progress','report_manager');
        $str_completed      = get_string('completed','report_manager');
        $str_total          = get_string('count','report_manager');

        $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
            // Empty col
            $header_table .= html_writer::start_tag('th',array('class' => 'head_first'));
            $header_table .= html_writer::end_tag('th');
            // Course
            $header_table .= html_writer::start_tag('th',array('class' => 'head_course'));
                $header_table .= $str_course;
            $header_table .= html_writer::end_tag('th');
            // Not enrol
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_not_enrol;
            $header_table .= html_writer::end_tag('th');
            // Not completed
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_not_completed;
            $header_table .= html_writer::end_tag('th');
            // Completed
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_completed;
            $header_table .= html_writer::end_tag('th');
            // Total
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_total;
            $header_table .= html_writer::end_tag('th');
        $header_table .= html_writer::end_tag('tr');

        return $header_table;
    }//Add_HeaderCourseTable_LevelTwo_Screen

    /**
     * Description
     * Add the content for the level Two
     *
     * @param           $courses_lst
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_ContentCourseTable_LevelTwo_Screen($courses_lst) {
        /* Variables    */
        $content    = null;
        // Headers
        $str_course         = get_string('course');
        $str_not_enrol      = get_string('not_start','report_manager');
        $str_not_completed  = get_string('progress','report_manager');
        $str_completed      = get_string('completed','report_manager');
        $str_total          = get_string('count','report_manager');

        foreach ($courses_lst as $id=>$course) {
            $content .= html_writer::start_tag('tr');
                // Empty col
                $content .= html_writer::start_tag('td',array('class' => 'first'));
                $content .= html_writer::end_tag('td');
                // Course
                $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $str_course));
                    $content .= $course->name;
                $content .= html_writer::end_tag('td');
                // Not enrol
                $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_not_enrol));
                    $content .= $course->not_enrol;
                $content .= html_writer::end_tag('td');
                // Not completed
                $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_not_completed));
                    $content .= $course->not_completed;
                $content .= html_writer::end_tag('td');
                // Completed
                $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_completed));
                    $content .= $course->completed;
                $content .= html_writer::end_tag('td');
                // Total
                $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_total));
                    $content .= $course->not_enrol + $course->not_completed + $course->completed;
                $content .= html_writer::end_tag('td');
            $content .= html_writer::end_tag('tr');
        }

        return $content;
    }//Add_ContentCourseTable_LevelTwo_Screen

    /**
     * Description
     * Add the header for the level three
     *
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Add_HeaderTable_LevelThree_Screen() {
        /* Variables    */
        $header_table = null;

        $str_user           = get_string('user');
        $str_state          = get_string('state','local_tracker_manager');
        $str_completion     = get_string('completion_time','local_tracker_manager');
        $str_valid          = get_string('outcome_valid_until','local_tracker_manager');

        $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
            // Empty col
            $header_table .= html_writer::start_tag('th',array('class' => 'head_first'));
            $header_table .= html_writer::end_tag('th');

            // Course col
            $header_table .= html_writer::start_tag('th',array('class' => 'head_course'));
                $header_table .= $str_user;
            $header_table .= html_writer::end_tag('th');

            // Status col
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_state;
            $header_table .= html_writer::end_tag('th');

            // Completion col
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_completion;
            $header_table .= html_writer::end_tag('th');

            // Valid until
            $header_table .= html_writer::start_tag('th',array('class' => 'head_status'));
                $header_table .= $str_valid;
            $header_table .= html_writer::end_tag('th');
        $header_table .= html_writer::end_tag('tr');

        return $header_table;
    }//Add_HeaderTable_LevelThree_Screen

    /**
     * @param           $course_info
     * @param           $expiration
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content for the level three
     */
    private static function Add_ContentTable_LevelThree_Screen($course_info,$expiration) {
        /* Variables    */
        $content        = null;
        $class          = null;
        $label          = null;
        $completed      = null;
        $not_completed  = null;
        $not_enrol      = null;
        // Headers
        $str_user           = get_string('user');
        $str_state          = get_string('state','local_tracker_manager');
        $str_completion     = get_string('completion_time','local_tracker_manager');
        $str_valid          = get_string('outcome_valid_until','local_tracker_manager');

        // Completed
        $completed = $course_info->completed;
        if ($completed) {
            foreach ($completed as $user) {

                $ts = strtotime($expiration  . ' month', $user->completed);
                if ($ts < time()) {
                    $class = 'expired';
                    $label = get_string('outcome_course_expired','local_tracker_manager');
                }else {
                    $class = 'completed';
                    $label = get_string('outcome_course_finished','local_tracker_manager');
                }

                $content .= html_writer::start_tag('tr',array('class' => $class));
                    // Empty col
                    $content .= html_writer::start_tag('td',array('class' => 'first'));
                    $content .= html_writer::end_tag('td');
                    // User col
                    $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $str_user));
                        $content .= $user->name;
                    $content .= html_writer::end_tag('td');
                    // Status col
                    $content .= html_writer::start_tag('td',array('class' => 'status ' . $class,'data-th' => $str_state));
                        $content .= $label;
                    $content .= html_writer::end_tag('td');

                    // Completion col
                    $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_completion));
                        $content .= userdate($user->completed,'%d.%m.%Y', 99, false);
                    $content .= html_writer::end_tag('td');

                    // Valid until
                    $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $str_valid));
                        $content .= userdate($ts,'%d.%m.%Y', 99, false);
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_completed
        }//if_completed

        return $content;
    }//Add_ContentTable_LevelThree_Screen

    /**
     * Description
     * Download Outcome Report - Level Zero
     *
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Download_OutcomeReport_LevelZero($outcome_report) {
        /* Variables    */
        global $CFG;
        $row                = null;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $export             = null;
        $myXls              = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            // File name
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Expiration perion
            $options            = CompetenceManager::get_completed_list();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            // One sheet by level two
            if ($outcome_report->levelone) {
                foreach ($outcome_report->levelone as $levelOne) {
                    foreach ($levelOne->leveltwo as $levelTwo) {
                        $row = 0;
                        // Adding the worksheet
                        $myXls = $export->add_worksheet($levelTwo->name);

                        // Header - company outcome report - level one
                        self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,$levelTwo,null,$completedBefore,$myXls,$row);
                        // Add level two
                        if ($levelTwo->levelthree) {
                            // Header
                            $row++;
                            self::AddHeader_LevelTwo_TableCourse($myXls,$row);

                            // Content table
                            $row++;
                            foreach ($levelTwo->levelThree as $company) {
                                if ($company->courses) {
                                    self::AddContent_LevelTwo_TableCourse($myXls,$row,$company);

                                    $myXls->merge_cells($row,0,$row,13);
                                    $row++;
                                }//if_courses
                            }//for_each_company
                        }//if_level_three
                    }//for_levelTwo
                }//for_elvel_one
            }else {
                $row = 0;
                // Adding the worksheet
                $myXls = $export->add_worksheet($outcome_report->levelzero);

                // Header - company outcome report - level one
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,null,null,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_levelOne

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelZero

    /**
     * Description
     * Download Outcome Report - Level One
     *
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Download_OutcomeReport_LevelOne($outcome_report) {
        /* Variables    */
        global $CFG;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $row                = null;
        $export             = null;
        $myXls              = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            // File name
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Expiration period
            $options            = CompetenceManager::get_completed_list();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            // One sheet by level two
            $levelOne = array_shift($outcome_report->levelone);
            if ($levelOne->leveltwo) {
                foreach ($levelOne->leveltwo as $levelTwo) {
                    $row = 0;
                    // Adding the worksheet
                    $myXls = $export->add_worksheet($levelTwo->name);

                    // Header - company outcome report - level one
                    self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,$levelTwo,null,$completedBefore,$myXls,$row);

                    // Add three
                    if ($levelTwo->levelthree) {
                        // Header
                        $row++;
                        self::AddHeader_LevelTwo_TableCourse($myXls,$row);

                        // Content
                        $row++;
                        foreach ($levelTwo->levelthree as $company) {
                            if ($company->courses) {
                                self::AddContent_LevelTwo_TableCourse($myXls,$row,$company);

                                $myXls->merge_cells($row,0,$row,13);
                                $row++;
                            }//if_courses
                        }//for_each_company
                    }//if_level_three
                }//for_levelTwo
            }else {
                $row = 0;
                // Adding the worksheet
                $myXls = $export->add_worksheet($levelOne->name);

                // Header - company outcome report - level one
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,null,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_levelTwo


            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelOne

    /**
     * Description
     * Download Course Report - Level Two
     *
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Download_OutcomeReport_LevelTwo($outcome_report) {
        /* Variables    */
        global $CFG;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $export             = null;
        $myXls              = null;
        $row                = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            // File name
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Expiration period
            $options            = CompetenceManager::get_completed_list();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            // Level one
            $levelOne = array_shift($outcome_report->levelone);
            // Level two
            $levelTwo = array_shift($outcome_report->leveltwo);

            // One sheet by level two
            $row = 0;
            // Adding the worksheet
            $myXls    = $export->add_worksheet($levelTwo->name);


            // Level three
            if ($levelTwo->levelthree) {
                // Header - Company outcome report - level one
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,$levelTwo,null,$completedBefore,$myXls,$row);

                // Header
                $row++;
                self::AddHeader_LevelTwo_TableCourse($myXls,$row);

                // Content
                $row++;
                foreach ($levelTwo->levelthree as $company) {
                    if ($company->courses) {
                        self::AddContent_LevelTwo_TableCourse($myXls,$row,$company);

                        $myXls->merge_cells($row,0,$row,13);
                        $row++;
                    }//if_courses
                }//for_each_company
            }else {
                // Header - Company outcome report - level one
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelTwo

    /**
     * Description
     * Download Outcome Report - Level Three
     *
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function Download_OutcomeReport_LevelThree($outcome_report) {
        /* Variables    */
        global $CFG;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $row                = null;
        $export             = null;
        $myXls              = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            // File name
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Expiration period
            $options            = CompetenceManager::get_completed_list();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            // Level one
            $levelOne = array_shift($outcome_report->levelone);
            // Level two
            $levelTwo = array_shift($outcome_report->leveltwo);

            // Level three
            if ($outcome_report->levelthree) {
                foreach ($outcome_report->levelthree as $company) {
                    // Onse sheet by level
                    $row = 0;
                    // Adding the worksheet
                    $myXls    = $export->add_worksheet($company->name);

                    // Header - company outcome report - level one
                    self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,$levelTwo,$company->name,$completedBefore,$myXls,$row);

                    // Header
                    $row++;
                    self::AddHeader_LevelThree_TableCourse($myXls,$row);
                    // Content
                    $row++;
                    self::AddContent_LevelThree_TableCourse($myXls,$row,$company,$outcome_report->expiration);

                    $myXls->merge_cells($row,0,$row,16);
                }//for_each_company
            }else {
                // One sheet by level
                $row = 0;
                // Adding the worksheet
                $myXls    = $export->add_worksheet($levelTwo->name);

                // Header - company outcome report - level one
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->zero_name,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelThree

    /**
     * Description
     * Add the Company Header
     *
     * @param           $out_name
     * @param           $out_desc
     * @param           $job_roles
     * @param           $levelZero
     * @param       null $levelOne
     * @param       null $levelTwo
     * @param       null $levelThree
     * @param           $completed_before
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function AddHeader_CompanySheet($out_name,$out_desc,$job_roles,$levelZero,$levelOne=null,$levelTwo=null,$levelThree=null,$completed_before,&$my_xls,&$row) {
        /* Variables    */
        $col = 0;
        $title_out              = get_string('outcome', 'report_manager')  . ' - ' . $out_name;
        $title_jr               = get_string('job_roles', 'report_manager');
        $str_job_roles          = null;
        $title_expiration       = get_string('expired_next', 'report_manager') . $completed_before;
        $title_level_zero       = get_string('company_structure_level', 'report_manager', 0) . ': ' . $levelZero;
        $title_level_one        = null;
        if ($levelOne) {
            $title_level_one    = get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name;
        }
        $title_level_two        = null;
        if ($levelTwo) {
            $title_level_two    = get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name;
        }//if_level_two

        try {
            // Outcome Name && Description
            $my_xls->write($row, $col, $title_out,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $my_xls->write($row, $col, $out_desc,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            // Job roles
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_jr,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $str_job_roles = implode(', ',$job_roles);
            $my_xls->write($row, $col, $str_job_roles ,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            // Level zero
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_level_zero,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            // Level one
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_level_one,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            // Level two
            if ($title_level_two) {
                $row++;
                $col = 0;
                $my_xls->write($row, $col, $title_level_two,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }//if_level_two

            // Level three
            if ($levelThree) {
                // Merge cells
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);

                $row++;
                $col = 0;
                $my_xls->write($row, $col, $levelThree,array('size'=>14, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }

            // Expiration time
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_expiration,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            // Merge cells
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CompanySheet

    /**
     * Description
     * Add the header table for the levels zero, one and two
     *
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function AddHeader_LevelTwo_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_company        = strtoupper(get_string('company','report_manager'));
        $str_course         = strtoupper(get_string('course'));
        $str_not_enrol      = strtoupper(get_string('not_start','report_manager'));
        $str_not_completed  = strtoupper(get_string('progress','report_manager'));
        $str_completed      = strtoupper(get_string('completed','report_manager'));
        $str_total          = strtoupper(get_string('count','report_manager'));
        $col                = 0;

        try {
            // Company
            $my_xls->write($row, $col, $str_company,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Course
            $col = $col + 6;
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Not enrol
            $col = $col + 6;
            $my_xls->write($row, $col, $str_not_enrol,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // In progress
            $col = $col + 2;
            $my_xls->write($row, $col, $str_not_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Completed
            $col = $col + 2;
            $my_xls->write($row, $col, $str_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Total
            $col = $col + 2;
            $my_xls->write($row, $col, $str_total,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_LevelTwo_TableCourse

    /**
     * Description
     * Add the content of the table for the levels zero, one and two
     *
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function AddContent_LevelTwo_TableCourse(&$my_xls,&$row,$company_info) {
        /* Variables    */
        $col    = 0;
        $total  = 0;

        try {
            foreach ($company_info->courses as $id=>$course) {
                // Company
                $my_xls->write($row, $col, $company_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                // Courses
                $col = $col + 6;
                $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                // Not enrol
                $col = $col + 6;
                $my_xls->write($row, $col, $course->not_enrol,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // In progress
                $col = $col + 2;
                $my_xls->write($row, $col, $course->not_completed,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Completed
                $col = $col + 2;
                $my_xls->write($row, $col, $course->completed,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                // Total
                $col = $col + 2;
                $total = $course->not_enrol + $course->not_completed + $course->completed;
                $my_xls->write($row, $col, $total,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                $row++;
                $col = 0;
            }//for_course
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelTwo_TableCourse

    /**
     * Description
     * Add the header of the table for the level three
     *
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function AddHeader_LevelThree_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_course         = strtoupper(get_string('course'));
        $str_user           = strtoupper(get_string('user'));
        $str_state          = strtoupper(get_string('state','local_tracker_manager'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker_manager'));
        $col                = 0;

        try {
            // Course
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // User
            $col = $col + 6;
            $my_xls->write($row, $col, $str_user,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // State
            $col = $col + 6;
            $my_xls->write($row, $col, $str_state,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            // Completion
            $col = $col + 3;
            $my_xls->write($row, $col, $str_completion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_LevelThree_TableCourse

    /**
     * Description
     * Add the content of the table for the level three
     *
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @param           $expiration
     * @throws          Exception
     *
     * @creationDate    300/03/2015
     * @author          eFaktor     (fbv)
     */
    private static function AddContent_LevelThree_TableCourse(&$my_xls,&$row,$company_info,$expiration) {
        /* Variables    */
        $col        = null;
        $courses    = null;

        try {
            $courses = $company_info->courses;
            if ($courses) {
                foreach ($courses as $course) {
                    // Completed
                    if ($course->completed) {
                        foreach ($course->completed as $id=>$user_info) {
                            $col = 0;
                            $ts = strtotime($expiration  . ' month', $user_info->completed);
                            if ($ts < time()) {
                                $bg_color = '#f2dede';
                                $label = get_string('outcome_course_expired','local_tracker_manager');
                            }else {
                                $bg_color = '#dff0d8';
                                $label = get_string('outcome_course_finished','local_tracker_manager');
                            }

                            // Course
                            $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+5);
                            $my_xls->set_row($row,20);

                            // User
                            $col = $col + 6;
                            $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+5);
                            $my_xls->set_row($row,20);

                            // State
                            $col = $col + 6;
                            $my_xls->write($row, $col, $label,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+2);
                            $my_xls->set_row($row,20);

                            // Completion
                            $col = $col + 3;
                            $my_xls->write($row, $col, userdate($user_info->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+2);
                            $my_xls->set_row($row,20);

                            $row++;
                        }//courses_completed
                    }//if_completed

                    $my_xls->merge_cells($row,0,$row,16);
                    $row ++;
                }//for_courses
            }//if_courses
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelThree_TableCourse
}//outcome_report