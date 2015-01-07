<?php
/**
 * Library code for the Import Structure Report generator.
 *
 * @package     report
 * @subpackage  generator/import_structure
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  10/09/2014
 * @author      eFaktor     (fbv)
 *
 */
class Import_Companies {
    /**
     * @param           csv_import_reader $cir
     * @param           $stdfields
     * @param           $error
     * @return          array
     *
     * @creationDate    18/11/2013
     * @author          eFaktor     (fbv)
     *
     * Description
     * Checks the columns from the CSV file
     */
    public static function ValidateColumns(csv_import_reader $cir, $stdfields, &$error) {
        $columns = $cir->get_columns();
        $error = NON_ERROR;

        if (empty($columns)) {
            $cir->close();
            $cir->cleanup();
            $error = CANNOT_READ_TMP_FILE;
        }

        // test columns
        $processed = array();
        foreach ($columns as $key=>$unused) {
            $field = $columns[$key];
            $lcfield = $field;
            if (in_array($field, $stdfields) or in_array($lcfield, $stdfields)) {
                // standard fields are only lowercase
                $newfield = $lcfield;
            } else if (preg_match('/^(cohort|course|group|type|role|enrolperiod)\d+$/', $lcfield)) {
                // special fields for enrolments
                $newfield = $lcfield;
            } else {
                $cir->close();
                $cir->cleanup();
                $error = CSV_LOAD_ERROR;
            }
            if (in_array($newfield, $processed)) {
                $cir->close();
                $cir->cleanup();
                $error = DUPLICATE_FIELD_NAME;
            }
            $processed[$key] = $newfield;
        }//for

        return $processed;
    }//ValidateColumns

    /**
     * @param           $columns
     * @param           $cir
     * @return          stdClass
     *
     * @creationDate    18/11/2013
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validates the content of the CSV file
     */
    public static function ValidateData($columns, $cir) {
        global $DB;

        /* Records File */
        $records_file               = new stdClass();
        $records_file->errors       = array();
        $records_file->info         = array();

        /* Validate the file */
        $i = 0;
        $cir->init();
        while ($fields = $cir->next()) {
            $status = '';
            foreach($fields as $key => $field) {
                $field_name         = $columns[$key];
                $rows[$field_name]  = trim(s($field));

                /* Check that doesn't exist another company with the same name */
                $data = trim(s($field));
                if ($DB->get_record('report_gen_companydata',array('name' => $data),'id')) {
                    $status .= get_string('err_company','report_generator') . '<br/>';
                }//if_exist
            }//foreach

            if ($status != '') {
                $records_file->errors[$i] = $i;
            }//if_error
            $rows['status'] = $status;
            $records_file->info[$i] = $rows;

            $i += 1;
        }//while

        $cir->close();

        return $records_file;
    }//ValidateData

    /**
     * @param           $records_file
     * @param           $level
     * @param           $level_parent
     * @return          bool
     *
     * @creationDate    18/11/2013
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import the company structure for a specific level
     */
    public static function ImportStructure($records_file,$level,$level_parent) {
        global $DB;

        /* Import Company Structure */
        $errors         = $records_file->errors;
        $info_records   = $records_file->info;

        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();
        try {
            foreach($info_records as $line=>$record) {
                if (!array_key_exists($line,$errors)) {
                    $record = $info_records[$line];

                    /* Insert the new company  */
                    $company = new stdClass();
                    $company->name              = $record['company'];
                    $company->hierarchylevel    = $level;
                    $company->idcounty          = $record['county'];
                    $company->idmuni            = $record['municipality'];
                    $company->industrycode      = $record['industry'];
                    $company->modified          = time();

                    $company->id = $DB->insert_record('report_gen_companydata',$company);
                    if ($level_parent) {
                        $parent = new stdClass();
                        $parent->companyid  = $company->id;
                        $parent->parentid   = $level_parent;
                        $parent->modified   = time();

                        $DB->insert_record('report_gen_company_relation',$parent);
                    }//if_parent
                }//if_line_error
            }//for

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch(Exception $ex){
            /* Rollback */
            $trans->rollback($ex);

            return false;
        }//try_catch
    }//ImportStructure

    /**
     * @param           $records_file
     * @param           $per_page
     * @param           $total_not_imported
     * @return          html_table
     *
     * @creationDate    18/11/2013
     * @author          eFaktor     (fbv)
     *
     * Description
     * Creates the table which shows all the records have not been imported
     */
    public static function ImportNotImported($records_file,$per_page,$total_not_imported) {
        /* Table Not Imported   */
        $table_not_imported = self::HeaderNotImported();

        /* Data */
        $errors         = $records_file->errors;
        $info_records   = $records_file->info;

        /* Records to show  */
        if ($total_not_imported <= $per_page) {
            $index = $total_not_imported;
        }else {
            $index = $per_page;
        }//if_total_not_imported

        for ($i = 0; $i<$index; $i++) {
            /* Info */
            $err_line = array_shift($errors);
            $info = $info_records[$err_line];

            /* New Row  */
            $row = array();

            /* Line Row     */
            $row[] = $err_line;
            /* Company Row  */
            $row[]  = $info['company'];
            /* County Row   */
            $row[]  = $info['county'];
            /* Municipality Row */
            $row[]  = $info['municipality'];
            /* Industry Code Row    */
            $row[]  = $info['industry'];
            /* Status Row   */
            $row[] = $info['status'];

            $table_not_imported->data[] = $row;
        }//for_index

        if ($total_not_imported > $per_page) {
            /* Empty Row    */
            $row = array();

            /* Line Row     */
            $row[] = '...';
            /* Company Row  */
            $row[] = '';
            /* County Row   */
            $row[] = '';
            /* Municipality Row  */
            $row[] = '';
            /* Industry Code Row  */
            $row[] = '';
            /* Status Row   */
            $row[] = '';

            $table_not_imported->data[] = $row;
        }//if_empty_row

        return $table_not_imported;
    }//ImportNotImported

    /**
     * @return          array
     *
     * @creationDate    18/11/2013
     * @author          eFaktor (fbv)
     *
     * Description
     * Gets the levels list for the 'Import Structure Companies' function
     */
    public static function GetLevel_To_Import() {
        /* Level to Import  */
        $level_to_import = array();

        $level_to_import[0] = get_string('sel_level','report_generator');
        $level_to_import[REPORT_GENERATOR_IMPORT_1] = get_string('level_1','report_generator');
        $level_to_import[REPORT_GENERATOR_IMPORT_2] = get_string('level_2','report_generator');
        $level_to_import[REPORT_GENERATOR_IMPORT_3] = get_string('level_3','report_generator');

        return $level_to_import;
    }//GetLevel_To_Import

    /**
     * @param           $level
     * @param           $parent
     * @return          array
     * @throws          Exception
     *
     * @creationDate    18/11/2013
     * @author          eFaktor (fbv)
     *
     * Description
     * Gets children list for one level.
     */
    public static function GetParentList_ToImport($level, $parent=null) {
        global $DB;

        /* parent_import */
        $parent_import = array();
        $parent_import[0] = get_string('sel_parent','report_generator');

        try {
            if ($level > 1) {
                /* Search Criteria  */
                $params = array();
                $params['hierarchylevel'] = $level-1;

                if (!$parent) {
                    $rdo = $DB->get_records('report_gen_companydata',$params,'name ASC','id, name');
                }else {
                    /* Search Criteria  */
                    $params = array();
                    $params['parent'] = $parent;

                    /* SQL Instruction   */
                    $sql = " SELECT     rcd.id,
                                    rcd.name
                         FROM       {report_gen_companydata}       rcd
                            JOIN    {report_gen_company_relation}  rcr ON    rcr.companyid = rcd.id
                                                                       AND   rcr.parentid  = :parent ";

                    $rdo = $DB->get_records_sql($sql,$params);
                }

                if ($rdo) {
                    foreach ($rdo as $instance) {
                        $parent_import[$instance->id] = $instance->name;
                    }//for_rdo
                }//if_rdo
            }//if_level

            return $parent_import;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetParentList_ToImport


    /* PRIVATE FUNCTIONS */
    /**
     * @return          html_table
     *
     * @creationDate    18/11/2013
     * @author          eFaktor     (fbv)
     *
     * Description
     * Creates the header of the table.
     */
    private static function HeaderNotImported() {
        /* Table */
        $table = new html_table();
        $table->id                  = "uupreview";
        $table->attributes['class'] = 'generaltable';
        $table->attributes['align'] = 'center';

        /* Header */
        $table->head                = array(get_string('csv_line','report_Generator'),
            get_string('company','report_Generator'),
            get_string('status','report_Generator'));

        return $table;
    }//HeaderNotImported
}//Import_Companies
