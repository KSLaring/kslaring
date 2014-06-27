<?php
/**
 * Library code for the Company Structure .
 *
 * @package     report
 * @subpackage  generator/company_structure
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  25/03/2014
 * @author      eFaktor     (fbv)
 *
 */

/**
 * @param           $level.         Hierarchy level of company.
 * @param           $company.       Company Identity.
 * @param   int     $parent.        Company's parent identity.
 * @return          bool
 *
 * @creationDate    07/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return if one company already exists to a specific level and parent.
 */
function report_generator_exists_company($level, $company,$parent=0) {
    global $DB;

    /* SQL Instruction */
    $sql = " SELECT   id
             FROM     {report_gen_companydata}
             WHERE    name = :company
                AND   hierarchylevel = :level ";

    if ($level > 1) {
        $sql = " SELECT     rgc.id
                 FROM       {report_gen_companydata}      rgc
                    JOIN    {report_gen_company_relation} rgcr  ON  rgc.id = rgcr.companyid
                                                                AND rgcr.parentid = :parent
                 WHERE      rgc.hierarchylevel = :level
                    AND     rgc.name = :company ";
    }

    /* Search Criteria */
    $params = array();
    $params['company']  = $company;
    $params['level']    = $level;
    $params['parent']   = $parent;

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        return true;
    }else {
        return false;
    }
}//report_generator_exists_company

/**
 * @param           $instance.      Company data.
 * @param   null    $parent.        Company's parent identity.
 *
 * @creationDate    10/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Insert a new company into database.
 */
function report_generator_insert_company_level($instance,$parent = null) {
    global $DB;

    if ($instance->id = $DB->insert_record('report_gen_companydata',$instance)) {
        if (!is_null($parent)) {
            $instance_relation = new stdClass();
            $instance_relation->companyid   = $instance->id;
            $instance_relation->parentid    = $parent;
            $instance_relation->modified    = $instance->modified;

            $instance_relation->id = $DB->insert_record('report_gen_company_relation',$instance_relation);
        }
    }//if
}//report_generator_report_insert_company_level

/**
 * @param           $instance.      Company data.
 *
 * @creationDate    10/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update company data.
 */
function report_generator_update_company_level($instance) {
    global $DB;

    $DB->update_record('report_gen_companydata',$instance);
}//report_generator_update_company_level

/**
 * @param           $company_id.    Company Identity.
 * @return          bool
 *
 * @updateDate      12/09/2012.
 * @author          eFaktor     (fbv)
 *
 * Description
 * Remove the company from database.
 */
function report_generator_delete_company($company_id) {
    global $DB;

    $DB->delete_records('report_gen_companydata',array('id'=>$company_id));

    if ($rdo = $DB->get_record('report_gen_company_relation',array('companyid'=>$company_id))) {
        $DB->delete_records('report_gen_company_relation',array('id'=>$rdo->id));
    }//if

    return true;
}//report_generator_delete_company


/**
 * @param           $company_id.        Company Identity
 * @return          int                 Number of employees.
 *
 * @creationDate    13/09/2012
 * @author          eFaktor     (fbV)
 *
 * Description
 * Check if one company has employees.
 */
function report_generator_company_has_employees($company_id) {
    global $DB;

    $count = 0;
    /* SQL Instruction   */
    $sql = " SELECT 	count(distinct u.id) count
             FROM		{user} 			  u
                JOIN	{user_info_data}  uid	ON 	u.id 		 	= uid.userid
                                                AND uid.data 	 	= :parent
                JOIN	{user_info_field} uif	ON	uid.fieldid 	= uif.id
                                                AND uif.datatype 	= :dtotype";

    /* Research Criteria */
    $params = array();
    $params['parent']   = $company_id;
    $params['dtotype']  = 'rgcompany';

    /* Execute */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        $count = $rdo->count;
    }
    return $count;
}//report_generator_company_has_employees

/**
 * @param           $company_id.        Company Identity
 * @return          int.                Number of children.
 *
 * @creationDate    11/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Check if there are another companies under it.
 */
function report_generator_company_has_child($company_id){
    global $DB;

    $count = 0;
    /* SQL Instruction */
    $sql = " SELECT     count(distinct rgcr.parentid) count
             FROM       {report_gen_companydata}      rgc
                JOIN    {report_gen_company_relation} rgcr  ON rgc.id = rgcr.parentid
             WHERE      rgc.id = :company ";
    /* Researh Criteria */
    $params = array();
    $params['company']  = $company_id;

    /* Execute */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        $count = $rdo->count;
    }
    return $count;
}//report_generator_company_has_child