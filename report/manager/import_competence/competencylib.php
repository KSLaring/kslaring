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
 * Report Competence Manager - Import Competence Data - Library.
 *
 * @package         report
 * @subpackage      manager/import_competence
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    25/08/2015
 * @author          eFaktor     (fbv)
 */

define('IMP_LOAD_ERROR','csv_load_error');
define('IMP_EMPTY_FILE','csv_empty_file');
define('IMP_CANNOT_READ_TMP_FILE','cannot_read_tmp_file');
define('IMP_FEW_COLUMNS','csv_few_columns');
define('IMP_INVALID_FILE_NAME','invalid_field_name');
define('IMP_DUPLICATE_FIELD_NAME','duplicate_field_name');
define('IMP_NON_ERROR','non_error');

class ImportCompetence {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $error
     * @param           $return
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Notify what kind of error has happened during the import process
     */
    public static function Notify_ImportError($error,$return) {
        /* Variables    */
        global $OUTPUT;

        try {
            switch ($error) {
                case IMP_LOAD_ERROR:
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');
                    echo $OUTPUT->notification(get_string('csv_load_error','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return);
                    echo $OUTPUT->footer();

                    break;
                case IMP_EMPTY_FILE:
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');
                    echo $OUTPUT->notification(get_string('csv_empty_file','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return);
                    echo $OUTPUT->footer();

                    break;
                case IMP_CANNOT_READ_TMP_FILE:
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');
                    echo $OUTPUT->notification(get_string('cannot_read_tmp_file','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return);
                    echo $OUTPUT->footer();

                    break;
                case IMP_FEW_COLUMNS;
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');
                    echo $OUTPUT->notification(get_string('csv_few_columns','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return);
                    echo $OUTPUT->footer();

                    break;
                case IMP_INVALID_FILE_NAME:
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');
                    echo $OUTPUT->notification(get_string('invalid_field_name','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return);
                    echo $OUTPUT->footer();

                    break;
                case IMP_DUPLICATE_FIELD_NAME:
                    echo $OUTPUT->header();
                    echo $OUTPUT->heading_with_help(get_string('header_competence_imp', 'report_manager'), 'header_competence_imp','report_manager');
                    echo $OUTPUT->notification(get_string('duplicate_field_name','report_manager'), 'notifysuccess');
                    echo '<br>';
                    echo $OUTPUT->continue_button($return);
                    echo $OUTPUT->footer();

                    break;
                default:
                    break;
            }//switch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Notify_ImportError

    /**
     * @param           csv_import_reader $cir
     * @param           $stdfields
     * @param           $error
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check that the header columns and columns are right
     */
    public static function ValidateColumns(csv_import_reader $cir, $stdfields, &$error) {
        /* Variables    */
        $columns    = null;
        $processed  = null;
        $field      = null;
        $lcField    = null;
        $newField   = null;

        try {
            /* Get Columns  */
            $columns = $cir->get_columns();

            /* Check Columns    */
            if (empty($columns)) {
                $cir->close();
                $cir->cleanup();
                $error = IMP_CANNOT_READ_TMP_FILE;
            }//if_columns

            // test columns
            $processed = array();
            foreach ($columns as $key=>$unused) {
                $newField   = null;
                $field      = $columns[$key];
                $lcField    = $field;

                if (in_array($field, $stdfields) or in_array($lcField, $stdfields)) {
                    // standard fields are only lowercase
                    $newField = $lcField;
                } else if (preg_match('/^(cohort|course|group|type|role|enrolperiod)\d+$/', $lcField)) {
                    // special fields for enrolments
                    $newField = $lcField;
                } else {
                    $cir->close();
                    $cir->cleanup();
                    $error = IMP_LOAD_ERROR;
                }
                if (in_array($newField, $processed)) {
                    $cir->close();
                    $cir->cleanup();
                    $error = IMP_DUPLICATE_FIELD_NAME;
                }

                $processed[$key] = $newField;
            }//for

            return $processed;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ValidateColumns

    /**
     * @param           $columns
     * @param           $cir
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the content of the file
     */
    public static function GetContentFile($columns, $cir) {
        /* Variables    */
        $contentFile    = null;
        $fieldName      = null;
        $rows           = null;
        $i              = null;

        try {
            /* Content File */
            $contentFile = array();

            /* Validate the file */
            $i = 1;
            $cir->init();
            while ($fields = $cir->next()) {
                /* New row  */
                $rows = array();

                /* Read the row */
                foreach($fields as $key => $field) {
                    $fieldName         = $columns[$key];
                    $rows[$fieldName]  = trim(s($field));
                }//foreach

                /* Save the content */
                $contentFile[$i] = $rows;

                $i += 1;
            }//while

            /* Close file   */
            $cir->close();

            return $contentFile;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ValidateData

    /**
     * @param           $contentFile
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Move the content of the file to a temporary table
     */
    public static function MoveContent($contentFile) {
        /* Variables    */
        global $DB;
        $trans      = null;
        $line       = null;
        $fieldName  = null;
        $record     = null;
        $instance   = null;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Copy content file in temporary table  */
            foreach($contentFile as $line=>$record) {
                /* Instance to move */
                $instance = new stdClass();
                $instance->line         = $line;
                $instance->username     = $record['username'];
                $instance->workplace    = $record['workplace'];
                $instance->workplace_ic = $record['workplace_ic'];
                $instance->sector       = $record['sector'];
                $instance->jobrole      = $record['jobrole'];
                $instance->jobrole_ic   = $record['jobrole_ic'];
                $instance->toimport     = 1;
                /* Generic  */
                if (strtolower($record['generic']) == 'true') {
                    $instance->generic = 1;
                }else if (strtolower($record['generic']) == 'false') {
                    $instance->generic = 0;
                }else {
                    $instance->generic  = 0;
                    $instance->toimport = 0;
                    $instance->error    = 'Impossible to classify job role as generic or not.';
                }//if_generic

                /* Competence Delete    */
                if (strtolower($record['delete']) == 'true') {
                    $instance->todelete = 1;
                }else if (strtolower($record['delete']) == 'false') {
                    $instance->todelete = 0;
                }else {
                    $instance->todelete = 0;
                    $instance->toimport = 0;
                    $instance->error    = 'Impossible to decide which action carry out.';
                }//if_delete

                /* Execute  */
                $DB->insert_record('report_gen_competence_imp',$instance);
            }//for_each_record

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//MoveContent

    /**
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mark how not importable all records connected with non existing users.
     */
    public static function Mark_NonExistingUsers() {
        /* Variables    */
        $nonUsers    = null;
        $error      = null;

        try {
            /* Get Users that do not exist in the system    */
            $nonUsers = self::Get_NonExistingUsers();

            /* Mark these records as not importable */
            if ($nonUsers) {
                $error = get_string('err_imp_user','report_manager');
                self::Mark_NotImportable($nonUsers,$error);
            }//if_nonUsers

            return true;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//Mark_NonExistingUsers


    /**
     * @return          mixed
     * @param           $start
     * @param           $length
     * @throws          Exception
     *
     * @creationDate    26/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check workplaces
     * Get non existing workplaces and their possible matches
     */
    public static function CheckWorkplaces($start,$length) {
        /* Variables    */
        $nonExisting    = null;
        $nonMatches     = null;
        $error          = null;

        try {
            // Checking by industry
            self::get_nonexisting_workplaces_by_industry($nonExisting,$nonMatches,$start,$length);
            // Checking by workplace
            self::get_nonexisting_workplaces_by_industry_workplace($nonExisting,$nonMatches,$start,$length);
            // Checking by sector
            self::get_nonexisting_workplaces_by_industry_sector($nonExisting,$nonMatches,$start,$length);

            // Checking by workplace name
            // Non Matches --> Mark how not importable
            if ($nonMatches) {
                $error = get_string('err_wk_none_match','report_manager');
                self::Mark_NotImportable($nonMatches,$error);
            }//if_nonMatches

            // Update the workplace matched for the existing
            self::UpdateMatch_ExistingWorkplaces($nonExisting);

            /* Non Existing --> Show Form to Match      */
            return $nonExisting;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CheckWorkplaces

    /**
     * @return          mixed
     * @param           $start
     * @param           $length
     * @throws          Exception
     *
     * @creationDate    27/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check job roles
     * Get non existing job roles and their possible matches
     */
    public static function CheckJobRoles($start,$length) {
        /* Variables    */
        $nonExisting    = null;
        $nonMatches     = null;
        $error          = null;

        try {
            // Checking by industry code
            self::get_nonexisting_jobroles_by_industry($nonExisting,$nonMatches,$start,$length);
            // Checking by name
            self::get_nonexisting_jobroles_by_name($nonExisting,$nonMatches,$start,$length);

            // Checking by Organization Structure
            self::Mark_NonExisting_JobRoles_By_Workplace($nonExisting);

            //  Non Matches --> Mark how not importable
            if ($nonMatches) {
                $error = get_string('err_jr_none_match','report_manager');
                self::Mark_NotImportable($nonMatches,$error);
            }//if_nonMatches

            // Update the job roles matched for the existing
            self::UpdateMatch_ExistingJobRoles($nonExisting);

            //  Non Existing --> Show Form to match
            return $nonExisting;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//CheckJobRoles

    /**
     * @param           $workplaces
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    28/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Matching the workplaces
     */
    public static function MatchingWorkplaces($workplaces,$data) {
        /* Variables    */
        global $DB;
        $index      = null;
        $wk         = null;
        $strMatch   = null;
        $match      = array();

        try {
            /* Add match of workplace   */
            foreach ($workplaces as $key=>$toMatch) {
                /* Get the reference    */
                $wk = 'CI_' . $toMatch->id;


                /* Get Workplace and Sector */
                $strMatch   = $data->$wk;
                if ($strMatch) {
                    $index      = strrpos($strMatch,'#MT#');
                    $strMatch   = substr($strMatch,$index+4);
                    /* Extract Workplace && Sector  */
                    $match      = explode('#SE#',$strMatch);

                    /* Update   */
                    $instance = new stdClass();
                    $instance->id = $key;
                    $instance->workplace_match  = $match[0];
                    $instance->sector_match     = $match[1];
                }else {
                    /* Update   */
                    $instance = new stdClass();
                    $instance->id = $key;
                    $instance->toimport = 0;
                    $instance->error = get_string('not_sure','report_manager');
                }//if_match

                /* Execute  */
                $DB->update_record('report_gen_competence_imp',$instance);
            }//for_each

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MatchingWorkplaces

    /**
     * @param           $jobRoles
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    28/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Matching Job Roles
     */
    public static function MatchingJobRoles($jobRoles,$data) {
        /* Variables    */
        global $DB;
        $index      = null;
        $jr         = null;
        $match      = null;

        try {
            /* Add match of workplace   */
            foreach ($jobRoles as $key=>$toMatch) {
                /* Get the reference    */
                $jr = 'CI_' . $toMatch->id;

                /* Get Match && Save */
                $match = $data->$jr;
                if ($match) {
                    $index = strrpos($match,'#');
                    $match = substr($match,$index+1);

                    /* Get Match && Update   */
                    $instance = new stdClass();
                    $instance->id = $key;
                    $instance->jobrole_match = $match;
                }else {
                    /* Update   */
                    $instance = new stdClass();
                    $instance->id = $key;
                    $instance->toimport = 0;
                    $instance->error = get_string('not_sure','report_manager');
                }//if_match

                /* Execute  */
                $DB->update_record('report_gen_competence_imp',$instance);
            }//for_each

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MatchingJobRoles

    /**
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean all competence data that were not imported, before start
     */
    public static function CleanNotImported() {
        /* Variables    */
        global $DB;
        $infoDelete = null;

        try {
            /* Delete Records    */
            $infoDelete = array();
            $infoDelete['toimport'] = 0;

            /* Execute  */
            $DB->delete_records('report_gen_competence_imp',$infoDelete);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanNotImported

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    31/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mark the existing users with their userid
     */
    public static function Mark_ExistingUsers() {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;
        $sql    = null;
        $trans  = null;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Search Criteria  */
            $params = array();
            $params['deleted']  = 0;
            $params['import']   = 1;

            /* SQL Instruction  */
            $sql = " SELECT		ci.id,
                                u.id	as 'userid'
                     FROM		{report_gen_competence_imp}	ci
                        JOIN	{user}						u	ON	u.username  = ci.username
                                                                AND	u.deleted   = :deleted
                     WHERE	ci.toimport = :import ";


            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    /* Update   */
                    $DB->update_record('report_gen_competence_imp',$instance);
                }    //for_rdo
            }//if_Rdo

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Mark_ExistingUsers

    /**
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the competence data
     */
    public static function ProcessCompetenceData() {
        /* Variables    */
        global $DB;
        $field              = null;
        $usersToProcess     = null;
        $totalNotImp        = null;

        try {
            /* Reference Competence Field Profile   */
            $field  = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');

            /* 1.- Import Competence Data   */
            self::ImportCompetenceData($field);

            /* 2.- Delete competence        */
            self::DeleteCompetenceData();

            /* 3.- Clean temporary          */
            self::CleanImported_FromTemporaryTable();

            /* 4.- Get Total Competence Data not Imported */
            $totalNotImp = $DB->count_records('report_gen_competence_imp',array('toimport' => 0));

            return $totalNotImp;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ProcessCompetenceData

    /**
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get some competence data that have not been imported
     */
    public static function CompetenceData_NotImported() {
        /* Variables    */
        $notImported        = null;
        $totalNotImp        = null;
        $tblNotImported     = null;

        try {
            /* Get Competence Data Not Imported - Table */
            $notImported = self::GetCompetence_NotImported();
            if ($notImported) {
                $totalNotImp = count($notImported);
                $tblNotImported = self::GetTable_NotImported($notImported,$totalNotImp);
            }//if_notImported

            return $tblNotImported;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CompetenceData_NotImported

    /**
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download the file with all the competence data have not been imported
     */
    public static function DownloadCompetenceData_NotImported() {
        /* Variables    */
        global $CFG;
        $row            = null;
        $col            = null;
        $time           = null;
        $fileName       = null;
        $export         = null;
        $myXls          = null;
        $strFileName    = null;
        $notImported    = null;

        try {

            $notImported = self::GetCompetence_NotImported();

            /* Library      */
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File and Sheet Name  */
            $strFileName = get_string('icd_file_name','report_manager');

            /* File */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($strFileName. '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            /* Create sheet */
            $myXls = $export->add_worksheet($strFileName);

            /* Add header   */
            $row = 0;
            self::AddHeader_CompetenceSheet($myXls,$row);

            /* Add content  */
            $row ++;
            self::AddContent_CompetenceSheet($notImported,$myXls,$row);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//DownloadCompetenceData_NotImported


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get users that do no exist in the system
     */
    private static function Get_NonExistingUsers() {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $nonExisting    = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT			ci.id
                     FROM			{report_gen_competence_imp}	ci
                        LEFT JOIN	{user}						u	ON 	u.username 	= ci.username
                                                                    AND	u.deleted 	= 0
                     WHERE u.id IS NULL ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Add */
                    $nonExisting[$instance->id] = $instance->id;
                }//for_each
            }//if_rdo

            return $nonExisting;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_NonExistingUsers

    /**
     * Description
     * Get nonexisting by industry code
     *
     * @param           $nonExisting
     * @param           $nonMatches
     * @param           $start
     * @param           $lenght
     *
     * @throws          Exception
     *
     * @creationDate    19/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_nonexisting_workplaces_by_industry(&$nonExisting,&$nonMatches,$start,$lenght) {
        /* Variables */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            // Search criteria

            $params = array();
            $params['level']    = 3;
            $params['import']   = 1;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 	
                                ci.id,
                                ci.workplace,
                                ci.workplace_ic,
                                ci.sector
                     FROM			{report_gen_competence_imp}	ci
                        -- By Industry Code
                        LEFT JOIN	{report_gen_companydata}	ic	ON 	ic.industrycode 	= ci.workplace_ic
                                                                    AND	ic.hierarchylevel 	= :level
                     WHERE	ci.toimport = :import
                        AND	(ci.workplace_match IS NULL OR ci.workplace_match = 0)
                        AND ic.id is NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$lenght);
            if ($rdo) {
                // Save and get possible matches
                foreach ($rdo as $instance) {
                    // info workspace
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->workplace    = $instance->workplace;
                    $info->industry     = $instance->workplace_ic;
                    $info->sector       = $instance->sector;
                    // Get possible matches
                    $info->matches      = self::Get_WorkplacesPossibleMatches($info->workplace,$info->industry);
                    // Get possible matches by Sector
                    self::Get_WorkplacesPossibleMatches_By_Sector($info->workplace,$info->industry,$info->sector,$info->matches);

                    /**
                     * Non existing with possibles matches
                     * Non existing without possibles matches
                     */
                    if ($info->matches) {
                        // Non Existing with possibles matches
                        $nonExisting[$instance->id] = $info;
                    }else {
                        // Non existing without non possible matches
                        $nonMatches[$instance->id] = $instance->id;
                    }//if_else_matches
                }//for_Rdo
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_nonexisting_workplaces_by_industry

    /**
     * Description
     * Get non existing by workplace
     *
     * @param           $nonExisting
     * @param           $nonMatches
     * @param           $start
     * @param           $lenght
     *
     * @throws          Exception
     *
     * @creationDate    19/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_nonexisting_workplaces_by_industry_workplace(&$nonExisting,&$nonMatches,$start,$lenght) {
        /* Variables */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            // Search criteria

            $params = array();
            $params['level']    = 3;
            $params['import']   = 1;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 	
                                ci.id,
                                ci.workplace,
                                ci.workplace_ic,
                                ci.sector
                     FROM			{report_gen_competence_imp}	ci
                        -- By Industry Code
                        LEFT JOIN	{report_gen_companydata}	wk	ON 	wk.industrycode 	= ci.workplace_ic
                                                                    AND wk.name 			= ci.workplace
                                                                    AND	wk.hierarchylevel 	= :level
                     WHERE	ci.toimport = :import
                        AND	(ci.workplace_match IS NULL OR ci.workplace_match = 0)
                        AND wk.id is NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$lenght);
            if ($rdo) {
                // Save and get possible matches
                foreach ($rdo as $instance) {
                    // info workspace
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->workplace    = $instance->workplace;
                    $info->industry     = $instance->workplace_ic;
                    $info->sector       = $instance->sector;
                    // Get possible matches
                    $info->matches      = self::Get_WorkplacesPossibleMatches($info->workplace,$info->industry);
                    // Get possible matches by Sector
                    self::Get_WorkplacesPossibleMatches_By_Sector($info->workplace,$info->industry,$info->sector,$info->matches);

                    /**
                     * Non existing with possibles matches
                     * Non existing without possibles matches
                     */
                    if ($info->matches) {
                        // Non Existing with possibles matches
                        $nonExisting[$instance->id] = $info;
                    }else {
                        // Non existing without non possible matches
                        $nonMatches[$instance->id] = $instance->id;
                    }//if_else_matches
                }//for_Rdo
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_nonexisting_workplaces_by_industry_workplace

    /**
     * Description
     * Get non existing workplaces by sector
     *
     * @param           $nonExisting
     * @param           $nonMatches
     * @param           $start
     * @param           $lenght
     *
     * @throws          Exception
     *
     * @creationDate    19/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_nonexisting_workplaces_by_industry_sector(&$nonExisting,&$nonMatches,$start,$lenght) {
        /* Variables */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            // Search criteria

            $params = array();
            $params['level']    = 2;
            $params['import']   = 1;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 	
                                ci.id,
                                ci.workplace,
                                ci.workplace_ic,
                                ci.sector
                     FROM			{report_gen_competence_imp}	ci
                        -- By Sector
                        LEFT JOIN	{report_gen_companydata}	se 	ON 	se.name 			= ci.sector
                                                                    AND se.industrycode		= ci.workplace_ic
                                                                    AND se.hierarchylevel	= :level
                     WHERE	ci.toimport = :import
                        AND	(ci.workplace_match IS NULL OR ci.workplace_match = 0)
                        AND se.id is NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$lenght);
            if ($rdo) {
                // Save and get possible matches
                foreach ($rdo as $instance) {
                    // info workspace
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->workplace    = $instance->workplace;
                    $info->industry     = $instance->workplace_ic;
                    $info->sector       = $instance->sector;
                    // Get possible matches
                    $info->matches      = self::Get_WorkplacesPossibleMatches($info->workplace,$info->industry);
                    // Get possible matches by Sector
                    self::Get_WorkplacesPossibleMatches_By_Sector($info->workplace,$info->industry,$info->sector,$info->matches);

                    /**
                     * Non existing with possibles matches
                     * Non existing without possibles matches
                     */
                    if ($info->matches) {
                        // Non Existing with possibles matches
                        $nonExisting[$instance->id] = $info;
                    }else {
                        // Non existing without non possible matches
                        $nonMatches[$instance->id] = $instance->id;
                    }//if_else_matches
                }//for_Rdo
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_nonexisting_workplaces_by_industry_sector


    /**
     * @param           $workplace
     * @param           $industry
     * @return          null
     * @throws          Exception
     *
     * @creationDate    26/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get possible matches for one workplace
     */
    private static function Get_WorkplacesPossibleMatches($workplace,$industry) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $matches    = array();
        $infoMatch  = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['level']    = 3;
            $params['level_se'] = 2;

            /* SQL Instruction  */
            $sql = " SELECT		co.id,
                                co.name,
                                co.industrycode,
                                se.name as 'sector',
                                se.id   as 'sector_id'
                     FROM	    {report_gen_companydata}	    co
                     	JOIN	{report_gen_company_relation}	cr	ON 	cr.companyid 		= co.id
                        JOIN	{report_gen_companydata}		se	ON 	se.id 				= cr.parentid
													                AND se.hierarchylevel 	= :level_se
                     WHERE	co.hierarchylevel = :level
                        AND co.industrycode like '%"  . $industry ."%'
                        AND co.name like '%"  . $workplace ."%'
                    ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Match   */
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->industry    = $instance->industrycode;
                    $infoMatch->sector      = $instance->sector;
                    $infoMatch->sectorId    = $instance->sector_id;

                    /* Add */
                    $matches[$instance->id] = $infoMatch;
                }//for_Each_rdo
            }//if_rdo

            return $matches;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_WorkplacesMatches

    /**
     * @param           $workplace
     * @param           $industry
     * @param           $sector
     * @param           $matches
     * @throws          Exception
     *
     * @creationDate    26/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get possibles matches by sector
     */
    private static function Get_WorkplacesPossibleMatches_By_Sector($workplace,$industry,$sector,&$matches) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $notIn      = 0;
        $infoMatch  = null;

        try {
            /* Get Not In   */
            if ($matches) {
                $notIn = implode(',',array_keys($matches));
            }//if_nonExisting

            /* Search criteria  */
            $params = array();
            $params['se_level']     = 2;
            $params['level']        = 3;
            $params['workplace']    = $workplace;
            $params['industry']     = $industry;

            /* SQL Instruction  */
            $sql = " SELECT		co.id,
                                co.name,
                                co.industrycode,
                                se.name as 'sector',
                                se.id   as 'sector_id'
                     FROM		{report_gen_companydata}		co
                        JOIN	{report_gen_company_relation}	cr	ON 	cr.companyid 		= co.id
                        JOIN	{report_gen_companydata}		se	ON 	se.id 				= cr.parentid
                                                                    AND se.hierarchylevel 	= :se_level
                                                                    AND se.name like '%" . $sector . "%'
                     WHERE	co.hierarchylevel   = :level
                        AND	co.name 		    = :workplace
                        AND co.industrycode     = :industry
                        AND co.id NOT IN ($notIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Match   */
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->sector      = $instance->sector;
                    $infoMatch->sectorId    = $instance->sector_id;

                    /* Add */
                    $matches[$instance->id] = $infoMatch;
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_WorkplacesPossibleMatches_By_Sector


    /**
     * @param           $nonExisting
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    26/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the existing workplaces with their match
     */
    private static function UpdateMatch_ExistingWorkplaces($nonExisting) {
        /* Variables    */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $notIn      = 0;
        $trans      = null;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Not In   */
            if ($nonExisting) {
                $notIn = implode(',',array_keys($nonExisting));
            }//if_nonExisting

            /* Search criteria  */
            $params = array();
            $params['level_wk'] = 3;
            $params['level_se'] = 2;
            $params['import']   = 1;

            /* SQL Instruction  */
            $sql = " SELECT		ci.id,
                                co.id as 'workplace_match',
                                se.id as 'sector_match'
                     FROM		{report_gen_competence_imp}	ci
                        -- WORKPALCE
                        JOIN	{report_gen_companydata}		co	ON 	co.name 			= ci.workplace
                                                                    AND co.industrycode 	= ci.workplace_ic
                                                                    AND	co.hierarchylevel 	= :level_wk
                        -- SECTOR
                        JOIN	{report_gen_company_relation}	cr	ON	cr.companyid		= co.id
                        JOIN	{report_gen_companydata}		se  ON	se.id				= cr.parentid
                                                                    AND se.name 			= ci.sector
                                                                    AND se.hierarchylevel	= :level_se

                     WHERE	ci.toimport = :import
                        AND	ci.id NOT IN ($notIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Execute  */
                    $DB->update_record('report_gen_competence_imp',$instance);
                }//for_each
            }//if_rdo

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UpdateMatch_ExistingWorkplaces

    /**
     * Description
     * Non existing jobroles by industry
     *
     * @param           $nonExisting
     * @param           $nonMatches
     * @param           $start
     * @param           $length
     *
     * @throws          Exception
     *
     * @creationDate    19/09/2017
     * @author          eFaktor     (Fbv)
     */
    private static function get_nonexisting_jobroles_by_industry(&$nonExisting,&$nonMatches,$start, $length) {
        /* Variables    */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            // Search criteria
            $params = array();
            $params['import'] = 1;
            $params['three']  = 3;
            $params['two']    = 2;
            $params['one']    = 1;
            $params['zero']   = 0;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 	
                                ci.id,
                                ci.jobrole,
                                ci.jobrole_ic,
                                ci.generic,
                                CONCAT(se.name,'/',wk.name) as 'ref',
                                ci.workplace_match 			as 'three',
                                ci.sector_match 			as 'two',
                                lo.id 						as 'one',
                                lz.id 						as 'zero'
                     FROM		  {report_gen_competence_imp}		ci
                        -- Level Three
                        JOIN	  {report_gen_companydata}			wk		ON	wk.id				= ci.workplace_match
                                                                            AND wk.hierarchylevel	= :three
                        -- Level Two
                        JOIN	  {report_gen_companydata}			se		ON	se.id 				= ci.sector_match
                                                                            AND	se.hierarchylevel 	= :two
                        -- Level One
                        JOIN	  {report_gen_company_relation}		lo_r	ON 	lo_r.companyid 		= ci.sector_match
                        JOIN	  {report_gen_companydata}			lo		ON 	lo.id				= lo_r.parentid
                                                                            AND	lo.hierarchylevel	= :one
                        -- Level Zero
                        JOIN 	  {report_gen_company_relation}		lz_r	ON 	lz_r.companyid 		= lo.id
                        JOIN	  {report_gen_companydata}			lz		ON 	lz.id				= lz_r.parentid
                                                                            AND	lz.hierarchylevel	= :zero
                        -- Job Roles by industry Code 
                        LEFT JOIN {report_gen_jobrole}				jr 		ON 	jr.industrycode 	= ci.jobrole_ic
                     WHERE 	 ci.toimport = :import
                        AND  (ci.jobrole_match IS NULL OR ci.jobrole_match = 0)
                        AND  jr.id IS NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$length);
            if ($rdo) {
                // Save and get possible matches
                foreach ($rdo as $instance) {
                    // Info
                    $info = new stdClass();
                    $info->id       = $instance->id;
                    $info->jobrole  = $instance->jobrole;
                    $info->industry = $instance->jobrole_ic;
                    $info->generic  = $instance->generic;
                    $info->ref      = $instance->ref;
                    // Get possible matches
                    if ($instance->generic) {
                        $info->matches = self::Get_JobRolesGeneric_PossibleMatches($info->jobrole,$info->industry);
                    }else {
                        $info->matches = self::Get_JobRolesPossibleMatches($info->jobrole,$info->industry,$instance->three,$instance->two,$instance->one,$instance->zero);
                    }//if_generic

                    /**
                     * Non existing with possibles matches
                     * Non existing without possibles matches
                     */
                    if ($info->matches) {
                        // Non Existing with possibles matches
                        $nonExisting[$instance->id] = $info;
                    }else {
                        // Non existing without non possible matches
                        $nonMatches[$instance->id] = $instance->id;
                    }//if_else_matches
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_nonexisting_jobroles_byindustry

    /**
     * Description
     * Non existing job roles by name
     *
     * @param           $nonExisting
     * @param           $nonMatches
     * @param           $start
     * @param           $length
     *
     * @throws           Exception
     *
     * @creationDate    19/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_nonexisting_jobroles_by_name(&$nonExisting,&$nonMatches,$start, $length) {
        /* Variables    */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            // Search criteria
            $params = array();
            $params['import'] = 1;
            $params['three']  = 3;
            $params['two']    = 2;
            $params['one']    = 1;
            $params['zero']   = 0;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 	
                                ci.id,
                                ci.jobrole,
                                ci.jobrole_ic,
                                ci.generic,
                                CONCAT(se.name,'/',wk.name) as 'ref',
                                ci.workplace_match 			as 'three',
                                ci.sector_match 			as 'two',
                                lo.id 						as 'one',
                                lz.id 						as 'zero'
                     FROM		  {report_gen_competence_imp}		ci
                        -- Level Three
                        JOIN	  {report_gen_companydata}			wk		ON	wk.id				= ci.workplace_match
                                                                            AND wk.hierarchylevel	= :three
                        -- Level Two
                        JOIN	  {report_gen_companydata}			se		ON	se.id 				= ci.sector_match
                                                                            AND	se.hierarchylevel 	= :two
                        -- Level One
                        JOIN	  {report_gen_company_relation}		lo_r	ON 	lo_r.companyid 		= ci.sector_match
                        JOIN	  {report_gen_companydata}			lo		ON 	lo.id				= lo_r.parentid
                                                                            AND	lo.hierarchylevel	= :one
                        -- Level Zero
                        JOIN 	  {report_gen_company_relation}		lz_r	ON 	lz_r.companyid 		= lo.id
                        JOIN	  {report_gen_companydata}			lz		ON 	lz.id				= lz_r.parentid
                                                                            AND	lz.hierarchylevel	= :zero
                        -- Job Roles by industry Code 
                        LEFT JOIN {report_gen_jobrole}				jr 		ON 	jr.name 		    = ci.jobrole
                     WHERE 	 ci.toimport = :import
                        AND  (ci.jobrole_match IS NULL OR ci.jobrole_match = 0)
                        AND  jr.id IS NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$length);
            if ($rdo) {
                // Save and get possible matches
                foreach ($rdo as $instance) {
                    // Info
                    $info = new stdClass();
                    $info->id       = $instance->id;
                    $info->jobrole  = $instance->jobrole;
                    $info->industry = $instance->jobrole_ic;
                    $info->generic  = $instance->generic;
                    $info->ref      = $instance->ref;
                    // Get possible matches
                    if ($instance->generic) {
                        $info->matches = self::Get_JobRolesGeneric_PossibleMatches($info->jobrole,$info->industry);
                    }else {
                        $info->matches = self::Get_JobRolesPossibleMatches($info->jobrole,$info->industry,$instance->three,$instance->two,$instance->one,$instance->zero);
                    }//if_generic

                    /**
                     * Non existing with possibles matches
                     * Non existing without possibles matches
                     */
                    if ($info->matches) {
                        // Non Existing with possibles matches
                        $nonExisting[$instance->id] = $info;
                    }else {
                        // Non existing without non possible matches
                        $nonMatches[$instance->id] = $instance->id;
                    }//if_else_matches
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_nonexisting_jobroles_by_name

    /**
     * @param           $jobrole
     * @param           $industry
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get possible matches for generic job roles
     */
    private static function Get_JobRolesGeneric_PossibleMatches($jobrole,$industry) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $matches    = array();
        $infoMatch  = null;

        try {
            // SQL Instruction
            $sql = " SELECT		DISTINCT  	jr.id,
                                            jr.name,
                                            jr.industrycode
                     FROM			{report_gen_jobrole}			jr
                        JOIN		{report_gen_jobrole_relation}	jr_rel	ON 	jr_rel.jobroleid 	= jr.id
                                                                            AND	jr_rel.levelzero 	IS NULL
                                                                            AND jr_rel.levelone 	IS NULL
                                                                            AND jr_rel.leveltwo 	IS NULL
                                                                            AND jr_rel.levelthree 	IS NULL
                     WHERE	jr.industrycode like '%" . $industry. "%'
                        AND jr.name like '%" . $jobrole . "%'
                     ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info match
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->industry    = $instance->industrycode;

                    // Add
                    $matches[$instance->id] = $infoMatch;
                }//for_each
            }//if_rdo

            return $matches;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesGeneric_PossibleMatches


    /**
     * @param           $jobrole
     * @param           $industry
     * @param           $levelThree
     * @param           $levelTwo
     * @param           $levelOne
     * @param           $levelZero
     * @return          array
     * @throws          Exception
     *
     * @creationDate    31/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get possible job roles, nut not generics
     */
    private static function Get_JobRolesPossibleMatches($jobrole,$industry,$levelThree,$levelTwo,$levelOne,$levelZero) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $matches    = array();
        $infoMatch  = null;

        try {
            // SQL Instruction
            $sql = " SELECT		DISTINCT  	jr.id,
                                            jr.name,
                                            jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                          JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	jr_rel.jobroleid 	= jr.id
                                                                            AND (
                                                                                 (
                                                                                  jr_rel.levelzero 	= $levelZero
                                                                                  AND
                                                                                  jr_rel.levelone 	IS NULL
                                                                                  AND
                                                                                  jr_rel.leveltwo 	IS NULL
                                                                                  AND
                                                                                  jr_rel.levelthree IS NULL
                                                                                 )
                                                                                 OR
                                                                                 (
                                                                                  jr_rel.levelzero 	= $levelZero
                                                                                  AND
                                                                                  jr_rel.levelone 	= $levelOne
                                                                                  AND
                                                                                  jr_rel.leveltwo 	IS NULL
                                                                                  AND
                                                                                  jr_rel.levelthree IS NULL
                                                                                 )
                                                                                 OR
                                                                                 (
                                                                                  jr_rel.levelzero 	= $levelZero
                                                                                  AND
                                                                                  jr_rel.levelone 	= $levelOne
                                                                                  AND
                                                                                  jr_rel.leveltwo 	= $levelTwo
                                                                                  AND
                                                                                  jr_rel.levelthree IS NULL
                                                                                 )
                                                                                 OR
                                                                                 (
                                                                                  jr_rel.levelzero 	= $levelZero
                                                                                  AND
                                                                                  jr_rel.levelone 	= $levelOne
                                                                                  AND
                                                                                  jr_rel.leveltwo 	= $levelTwo
                                                                                  AND
                                                                                  jr_rel.levelthree = $levelThree
                                                                                 )
                                                                                )
                     WHERE	jr.industrycode like '%" . $industry ."%'
                        AND jr.name like '%" . $jobrole . "%'
                     ";


            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info match
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->industry    = $instance->industrycode;

                    // Add
                    $matches[$instance->id] = $infoMatch;
                }//for_each
            }//if_rdo

            return $matches;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesPossibleMatches

    /**
     * @param           $nonExisting
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    31/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mark jobroles that are not connected with organization structure how not importable
     */
    private static function Mark_NonExisting_JobRoles_By_Workplace($nonExisting) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;
        $notIn  = 0;
        $trans  = null;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Not In   */
            if ($nonExisting) {
                $notIn = implode(',',array_keys($nonExisting));
            }//if_nonExisting

            /* Search Criteria  */
            $params = array();
            $params['level_one']    = 1;
            $params['level_zero']   = 0;
            $params['import']       = 1;
            $params['generic']      = 0;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT 	ci.id,
                                            ci.toimport,
                                            ci.error
                     FROM			{report_gen_competence_imp}		ci
                        -- Level One
                        JOIN		{report_gen_company_relation}	lo_r	ON 	lo_r.companyid 		= ci.sector_match
                        JOIN		{report_gen_companydata}		lo		ON 	lo.id				= lo_r.parentid
                                                                            AND	lo.hierarchylevel	= :level_one
                        -- Level Zero
                        JOIN 		{report_gen_company_relation}	lz_r	ON 	lz_r.companyid 		= lo.id
                        JOIN		{report_gen_companydata}		lz		ON 	lz.id				= lz_r.parentid
                                                                            AND	lz.hierarchylevel	= :level_zero
                        -- JOB ROLE
                        JOIN		{report_gen_jobrole}			jr		ON 	jr.name 			= ci.jobrole
                                                                                AND	jr.industrycode	= ci.jobrole_ic
                        LEFT JOIN	{report_gen_jobrole_relation}	jr_rel	ON	jr_rel.jobroleid	= jr.id
                                                                            AND
                                                                                (
                                                                                 (
                                                                                  jr_rel.levelzero 	= lz.id
                                                                                  AND
                                                                                  jr_rel.levelone 	IS NULL
                                                                                  AND
                                                                                  jr_rel.leveltwo 	IS NULL
                                                                                  AND
                                                                                  jr_rel.levelthree IS NULL
                                                                                 )
                                                                                 OR
                                                                                 (
                                                                                  jr_rel.levelzero 	= lz.id
                                                                                  AND
                                                                                  jr_rel.levelone 	= lo.id
                                                                                  AND
                                                                                  jr_rel.leveltwo 	IS NULL
                                                                                  AND
                                                                                  jr_rel.levelthree IS NULL
                                                                                 )
                                                                                 OR
                                                                                 (
                                                                                  jr_rel.levelzero 	= lz.id
                                                                                  AND
                                                                                  jr_rel.levelone 	= lo.id
                                                                                  AND
                                                                                  jr_rel.leveltwo 	= ci.sector_match
                                                                                  AND
                                                                                  jr_rel.levelthree IS NULL
                                                                                 )
                                                                                 OR
                                                                                 (
                                                                                  jr_rel.levelzero 	= lz.id
                                                                                  AND
                                                                                  jr_rel.levelone 	= lo.id
                                                                                  AND
                                                                                  jr_rel.leveltwo 	= ci.sector_match
                                                                                  AND
                                                                                  jr_rel.levelthree = ci.workplace_match
                                                                                 )
                                                                                )

                     WHERE		ci.toimport = :import
                        AND     ci.generic  = :generic
                        AND		jr_rel.id IS NULL
                        AND		ci.id NOT IN ($notIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                /* Mark Record how Not Importable   */
                foreach ($rdo as $instance) {
                    $instance->toimport = 0;
                    $instance->error    = get_string('err_jr_none_match','report_manager');

                    /* Execute  */
                    $DB->update_record('report_gen_competence_imp',$instance);
                }//for_instance
            }//if_rdo

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Mark_NonExisting_JobRoles_By_Workplace

    /**
     * @param           $nonExisting
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    27/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the existing jobroles with their match
     */
    private static function UpdateMatch_ExistingJobRoles($nonExisting) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;
        $notIn  = 0;
        $trans  = null;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Not In   */
            if ($nonExisting) {
                $notIn = implode(',',array_keys($nonExisting));
            }//if_nonExisting

            /* Search Criteria  */
            $params = array();
            $params['import'] = 1;

            /* SQL Instruction  */
            $sql = " SELECT	ci.id,
                            jr.id as 'jobrole_match'
                     FROM		{report_gen_competence_imp}	ci
                        JOIN	{report_gen_jobrole}		jr	ON 	jr.industrycode = ci.jobrole_ic
                                                                AND	jr.name			= ci.jobrole

                     WHERE	ci.toimport = :import
                        AND	ci.id NOT IN ($notIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Execute  */
                    $DB->update_record('report_gen_competence_imp',$instance);
                }//for_Rdo
            }//if_rdo

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UpdateMatch_ExistingJobRoles

    /**
     * @param           $records
     * @param           $error
     * @throws          Exception
     *
     * @creationDate    25/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mark the records how not importable
     */
    private static function Mark_NotImportable($records,$error) {
        /* Variables    */
        global $DB;
        $trans      = null;
        $instance   = null;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            foreach ($records as $key) {
                /* Mark Record Not Importable   */
                $instance = new stdClass();
                $instance->id       = $key;
                $instance->toimport = 0;
                $instance->error    = $error;

                /* Execute  */
                $DB->update_record('report_gen_competence_imp',$instance);
            }//for_each_Record

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Mark_NotImportable


    /**
     * @param           $field
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/201
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import the competence data that has to be created or updated
     */
    private static function ImportCompetenceData($field) {
        /* Variables    */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $usersToProcess = null;
        $competenceData = null;
        $newJobRoles    = null;
        $jobRolesDiff   = null;
        $myJobRoles     = null;
        $time           = null;
        $trans          = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Get Users to Process */
            $usersToProcess = self::UsersToProcessCompetence($field);

            /* Process Competence Data for the existing users   */
            if ($usersToProcess) {
                /* Search criteria  */
                $params = array();
                $params['import'] = 1;
                $params['delete'] = 0;

                /* SQL Instruction  */
                $sql = " SELECT		ci.workplace_match 	as 'companyid',
                                    GROUP_CONCAT(DISTINCT ci.jobrole_match ORDER BY ci.jobrole_match SEPARATOR ',') as 'new_jobroles',
                                    uicd.id 			as 'uicd',
                                    uicd.jobroles 		as 'present_jobroles'
                         FROM			{report_gen_competence_imp}	ci
                            LEFT JOIN	{user_info_competence_data}	uicd	ON 	uicd.userid 		= ci.userid
                                                                            AND uicd.companyid 		= ci.workplace_match
                                                                            AND uicd.competenceid	= :competence
                         WHERE		ci.toimport = :import
                            AND		ci.todelete = :delete
                            AND		ci.userid	= :user
                         GROUP BY	ci.workplace_match ";

                /* Process the competence for each user  */
                foreach ($usersToProcess as $user => $competence) {
                    /* Search Criteria  */
                    $params['user']         = $user;
                    $params['competence']   = $competence;

                    /* Execute  */
                    $rdo = $DB->get_records_sql($sql,$params);
                    if ($rdo) {
                        foreach ($rdo as $instance) {
                            /* Competence to process    */
                            $competenceData = new stdClass();
                            $competenceData->competenceid   = $competence;
                            $competenceData->userid         = $user;
                            $competenceData->companyid      = $instance->companyid;
                            $competenceData->timemodified   = $time;
                            $competenceData->level          = 3;
                            $competenceData->editable       = 1;
                            $competenceData->approved       = 1;
                            $competenceData->rejected       = 0;
                            $competenceData->token          = 0;

                            /* Check if it's a new competence       */
                            if ($instance->uicd) {
                                /* Add reference record to update   */
                                $competenceData->id = $instance->uicd;

                                /* Update   */
                                /* Check Job Roles  */
                                if ($instance->new_jobroles) {
                                    /* New Job Roles        */
                                    $newJobRoles = explode(',',$instance->new_jobroles);
                                    /* Present Job Roles    */
                                    $myJobRoles       = explode(',',$instance->present_jobroles);

                                    /* Get Job roles to add */
                                    if ($myJobRoles) {
                                        $jobRolesDiff             = array_diff($newJobRoles,$myJobRoles);
                                        if ($jobRolesDiff) {
                                            $competenceData->jobroles = $instance->present_jobroles . ',' . implode(',',$jobRolesDiff);
                                        }//if_jrDiff
                                    }else {
                                        $competenceData->jobroles  = $newJobRoles;
                                    }//if_myJobRoles

                                    /* Execute  */
                                    $DB->update_record('user_info_competence_data',$competenceData);
                                }else {
                                    /* Update to null job roles */
                                    $competenceData->jobroles       = null;

                                    /* Execute  */
                                    $DB->update_record('user_info_competence_data',$competenceData);
                                }//if_jobroles
                            }else {
                                /* New Competence   */
                                $competenceData->jobroles       = $instance->new_jobroles;

                                /* Execute  */
                                $DB->insert_record('user_info_competence_data',$competenceData);
                            }//if_infoCompetenceData
                        }//for_Each_instance
                    }//if_rdo
                }//for_each_user
            }//if_UsersToProcess

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportCompetenceData

    /**
     * @param           $field
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users and their competence reference to process
     */
    private static function UsersToProcessCompetence($field) {
        /* Variables    */
        global $DB;
        $params             = null;
        $sql                = null;
        $rdo                = null;
        $usersToProcess     = array();
        $competenceEntry    = null;
        $competenceId       = null;
        $infoData           = null;
        $trans              = null;
        $time               = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Search Criteria  */
            $params = array();
            $params['import']   = 1;
            $params['delete']   = 0;

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT	ci.userid,
                                        uic.id
                     FROM			{report_gen_competence_imp}   ci
                        LEFT JOIN	{user_info_competence}		  uic	ON uic.userid = ci.userid
                     WHERE	ci.toimport = :import
                        AND ci.todelete = :delete ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Check if the competence data entry has to be created */
                    if (!$instance->id) {
                        /* Create a entry   */
                        $competenceEntry = new stdClass();
                        $competenceEntry->userid            = $instance->userid;
                        $competenceEntry->timemodified      = $time;
                        $competenceId                       = $DB->insert_record('user_info_competence',$competenceEntry);
                    }else {
                        $competenceId = $instance->id;
                    }//if_competence

                    /* Add */
                    $usersToProcess[$instance->userid] = $competenceId;

                    /* Get ID for competence profile field   */
                    /* Update user_info_data                 */
                    $infoData   = $DB->get_record('user_info_data',array('fieldid' => $field->id,'userid' => $instance->userid));
                    if (!$infoData) {
                        $infoData = new stdClass();
                        $infoData->userid  = $instance->userid;
                        $infoData->fieldid = $field->id;
                        $infoData->data    = $competenceId;
                        /* Execute  */
                        $DB->insert_record('user_info_data',$infoData);
                    }else {
                        /* Update   */
                        $infoData->data = $competenceId;
                        $DB->update_record('user_info_data',$infoData);
                    }//create_new_entrance
                }//for_Each_user
            }//if_rdo

            /* Commit   */
            $trans->allow_commit();

            return $usersToProcess;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UsersToProcessCompetence

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    04/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete competence data from user profile
     */
    private static function DeleteCompetenceData() {
        /* Variables    */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $trans      = null;
        $infoDelete = null;
        $infoUpdate = null;
        $myJobRoles = null;
        $time       = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time       */
            $time = time();

            /* Search Criteria  */
            $params = array();
            $params['import']       = 1;
            $params['delete']       = 1;

            /* SQL Instruction  */
            $sql = " SELECT	ci.id,
                            ci.userid,
                            ci.workplace_match,
                            ci.jobrole_match,
                            uicd.id as 'uicd',
                            uicd.jobroles
                     FROM		{report_gen_competence_imp} 	ci
                        JOIN	{user_info_competence_data}		uicd	ON 	uicd.userid 	= ci.userid
                                                                        AND	uicd.companyid 	= ci.workplace_match
                     WHERE	ci.toimport = :import
                        AND	ci.todelete = :delete ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Competence Data  */
                    $infoUpdate = new stdClass();
                    $infoUpdate->id             = $instance->uicd;
                    $infoUpdate->timemodified   = $time;

                    /* Delete Job Role or Company    */
                    if ($instance->jobrole_match) {
                        /* My Job Roles     */
                        $myJobRoles = array_flip(explode(',',$instance->jobroles));
                        /* Delete Job Role  */
                        unset($myJobRoles[$instance->jobrole_match]);

                        /* Update present job roles */
                        if ($myJobRoles) {
                            $infoUpdate->jobroles = implode(',',array_keys($myJobRoles));
                        }else {
                            $infoUpdate->jobroles = null;
                        }//if_myJobRoles

                        /* Execute  */
                        $DB->update_record('user_info_competence_data',$infoUpdate);
                    }else {
                        /* Delete Company  from Competence Data */
                        $infoDelete = array();
                        $infoDelete['id']     = $instance->uicd;
                        $infoDelete['userid'] = $instance->userid;
                        /* Execute  */
                        $DB->delete_records('user_info_competence_data',$infoDelete);
                    }//if_del_company_jr
                }//for_each_record
            }//if_Rdo

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//DeleteCompetenceData


    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all competence data that could not be imported
     */
    private static function GetCompetence_NotImported() {
        /* Variables    */
        global $DB;
        $params         = null;
        $rdo            = null;
        $notImported    = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['toimport'] = 0;

            /* Execute  */
            $rdo = $DB->get_records('report_gen_competence_imp',$params);
            if ($rdo) {
                $notImported = $rdo;
            }//if_rdo

            return $notImported;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompetence_NotImported

    /**
     * @param           $notImported
     * @param           $totalNotImp
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the table which display what competence have not been imported
     */
    private static function GetTable_NotImported($notImported,$totalNotImp){
        /* Variables    */
        $index      = 20;
        $info       = null;
        $tblNotImp  = null;
        $row        = null;

        try {
            /* Maximum records to display */
            if ($totalNotImp <= $index) {
                $index = $totalNotImp;
            }//if_index

            /* Create Table */
            $tblNotImp = new html_table();
            $tblNotImp->id                  = "uupreview";
            $tblNotImp->attributes['class'] = 'generaltable';
            $tblNotImp->attributes['align'] = 'center';

            /* Add Header   */
            $tblNotImp->head  = array(get_string('icd_user','report_manager'),
                                      get_string('icd_wk','report_manager'),
                                      get_string('icd_wk_ic','report_manager'),
                                      get_string('icd_sector','report_manager'),
                                      get_string('icd_jr','report_manager'),
                                      get_string('icd_jr_ic','report_manager'),
                                      get_string('icd_msg','report_manager')
                                     );

            /* Add the content  */
            for ($i = 0; $i<$index; $i++) {
                /* Get Info */
                $info = array_shift($notImported);

                /* New Row */
                $row = array();

                /* Add Info */
                /* Username     */
                $row[]  = $info->username;
                /* Workplace    */
                $row[]  = $info->workplace;
                /* Workplace IC */
                $row[]  = $info->workplace_ic;
                /* Sector       */
                $row[]  = $info->sector;
                /* Job Role     */
                $row[]  = $info->jobrole;
                /* Job Role IC  */
                $row[]  = $info->jobrole_ic;
                /* Reason       */
                $row[]  = $info->error;

                /* Add the row  */
                $tblNotImp->data[] = $row;
            }//for

            /* Add an extra row */
            if ($totalNotImp > $index) {
                /* New Row */
                $row = array();

                /* Username     */
                $row[]  = '...';
                /* Workplace    */
                $row[]  = '...';
                /* Workplace IC */
                $row[]  = '...';
                /* Sector       */
                $row[]  = '...';
                /* Job Role     */
                $row[]  = '...';
                /* Job Role IC  */
                $row[]  = '...';
                /* Reason       */
                $row[]  = '...';

                /* Add the row  */
                $tblNotImp->data[] = $row;
            }//if_total

            return $tblNotImp;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTable_NotImported

    /**
     * @param           $myXls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to the file
     */
    private static function AddHeader_CompetenceSheet(&$myXls,$row) {
        /* Variables    */
        $col        = 0;
        $colUser    = 'username';
        $colWK      = 'workplace';
        $colWKIC    = 'workplace_ic';
        $colSE      = 'sector';
        $colJR      = 'jobrole';
        $colJRIC    = 'jobrole_ic';
        $colGene    = 'generic';
        $colDel     = 'delete';

        try {
            /* Username                 */
            $myXls->write($row, $col, $colUser,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+1);
            $myXls->set_row($row,20);

            /* Workplace                */
            $col += 2;
            $myXls->write($row, $col, $colWK,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+3);
            $myXls->set_row($row,20);

            /* Workplace Industry code  */
            $col += 4;
            $myXls->write($row, $col, $colWKIC,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+1);
            $myXls->set_row($row,20);

            /* Sector                   */
            $col += 2;
            $myXls->write($row, $col, $colSE,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+3);
            $myXls->set_row($row,20);

            /* Jobrole                  */
            $col += 4;
            $myXls->write($row, $col, $colJR,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+3);
            $myXls->set_row($row,20);

            /* Jobrole Industry Code    */
            $col += 4;
            $myXls->write($row, $col, $colJRIC,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+1);
            $myXls->set_row($row,20);

            /* Generic                  */
            $col += 2;
            $myXls->write($row, $col, $colGene,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+1);
            $myXls->set_row($row,20);

            /* Delete                   */
            $col += 2;
            $myXls->write($row, $col, $colDel,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $myXls->merge_cells($row,$col,$row,$col+1);
            $myXls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CompetenceSheet


    /**
     * @param           $notImported
     * @param           $myXls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    09/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content to the file
     */
    private static function AddContent_CompetenceSheet($notImported,&$myXls,&$row) {
        /* Variables    */
        $col        = null;
        $info       = null;
        $strGeneric = null;
        $strDelete  = null;

        try {
            foreach ($notImported as $info) {
                /* Username                 */
                $myXls->write_string($row, $col, $info->username,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+1);
                $myXls->set_row($row,20);

                /* Workplace                */
                $col += 2;
                $myXls->write($row, $col, $info->workplace,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+3);
                $myXls->set_row($row,20);

                /* Workplace Industry Code  */
                $col += 4;
                $myXls->write_string($row, $col, $info->workplace_ic,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+1);
                $myXls->set_row($row,20);

                /* Sector                   */
                $col += 2;
                $myXls->write($row, $col, $info->sector,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+3);
                $myXls->set_row($row,20);

                /* Jobrole                  */
                $col += 4;
                $myXls->write($row, $col, $info->jobrole,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+3);
                $myXls->set_row($row,20);

                /* Jobrole Industry Code    */
                $col += 4;
                $myXls->write_string($row, $col, $info->jobrole_ic,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+1);
                $myXls->set_row($row,20);

                /* Generic                  */
                if ($info->generic) {
                    $strGeneric = 'TRUE';
                }else {
                    $strGeneric = 'FALSE';
                }//if_generic
                $col += 2;
                $myXls->write($row, $col, $strGeneric,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+1);
                $myXls->set_row($row,20);

                /* Delete                   */
                if ($info->todelete) {
                    $strDelete = 'TRUE';
                }else {
                    $strDelete = 'FALSE';
                }//if_todelete
                $col += 2;
                $myXls->write($row, $col, $strDelete,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $myXls->merge_cells($row,$col,$row,$col+1);
                $myXls->set_row($row,20);

                /* Reset for the new row    */
                $col = 0;
                $row++;
            }//for_Each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_CompetenceSheet

    /**
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary table used during the import process
     */
    private static function CleanImported_FromTemporaryTable() {
        /* Variables    */
        global $DB;
        $infoDelete = null;

        try {
            /* Delete Records    */
            $infoDelete = array();
            $infoDelete['toimport'] = 1;

            /* Execute  */
            $DB->delete_records('report_gen_competence_imp',$infoDelete);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanImported_FromTemporaryTable

}//ImportCompetence