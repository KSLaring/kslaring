<?php
/**
 * Library code for the Company Structure .
 *
 * @package     report
 * @subpackage  generator/company_structure
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  08/10/2014
 * @author      eFaktor     (fbv)
 *
 */

class company_structure {

    /*********************/
    /* PUBLIC FUNCTIONS  */
    /*********************/

    /**
     * @static
     * @param       array   $data.      Form data.
     * @return      array               Action and level.
     *
     * @updateDate  08/10/2014.
     * @author      eFaktor     (fbv)
     *
     * Description
     * Return the action that the user want to carry out and the level.
     */
    public static function Get_ActionLevel($data = array()) {
        /* Variables    */
        $action = null;
        $level = 0;

        if ($data) {
            foreach ($data as $key => $value) {
                if (strpos($key, 'submitbutton') !== false) {
                    $action = 'submit';
                    $level = -1;
                } else if (strpos($key, 'btn-') !== false) {
                    $action = substr($key, 4, -1);
                    $level = (int)substr($key, -1);
                }
            }//for
        }//if_data

        return array($action, $level);
    }//Get_ActionLevel

    /**
     * @static
     * @param       $level          Hierarchy level of the company
     * @param       int $parent_id  Company's parent
     * @return      array           Company list
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get a list of all the companies are connected a specific level.
     */
    public static function Get_Companies_LevelList($level, $parent_id = 0) {
        /* Variables */
        global $DB;
        $levels = array();

        try {
            /* Research Criteria */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction   */
            $sql_Select = " SELECT     DISTINCT rcd.id,
                                       rcd.name
                            FROM       {report_gen_companydata} rcd ";
            /* Join */
            $sql_Join = " ";
            if ($level > 1) {
                $sql_Join = " JOIN  {report_gen_company_relation} rcr ON    rcr.companyid = rcd.id
                                                                 AND   rcr.parentid  IN ($parent_id) ";
            }//if_level

            $sql_Where = " WHERE rcd.hierarchylevel = :level ";
            $sql_Order = " ORDER BY rcd.name ASC ";

            /* SQL */
            $sql = $sql_Select . $sql_Join . $sql_Where . $sql_Order;

            $levels[0] = get_string('select_level_list','report_generator');
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                foreach ($rdo as $field) {
                    $levels[$field->id] = $field->name;
                }//foreach
            }//if_rdo

            return $levels;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Companies_LevelList

    /**
     * @static
     * @param       $parent
     * @return      array
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @auhtor      eFaktor         (fbv)
     *
     * Description
     * Get a list of all employees who work to a specific company.
     */
    public static function Get_EmployeeLevel($parent) {
        /* Variables    */
        global $DB;
        $employee_list = array();

        try {
            /* Research Criteria */
            $params = array();
            $params['parent']   = $parent;
            $params['dtotype']  = 'rgcompany';

            /* SQL Instruction   */
            $sql = " SELECT 	DISTINCT  u.id,
                                          CONCAT(u.firstname,' ',u.lastname) as 'name'
                     FROM		{user} 			  u
                        JOIN	{user_info_data}  uid	ON 	u.id 		 	= uid.userid
                                                        AND uid.data 	 	= :parent
                        JOIN	{user_info_field} uif	ON	uid.fieldid 	= uif.id
                                                        AND uif.datatype 	= :dtotype
                     ORDER BY	u.lastname ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                foreach ($rdo as $field) {
                    $employee_list[$field->id] = $field->name;
                }//for
            }//if_rdo

            return $employee_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_EmployeeLevel

    /**
     * @static
     * @param       $level
     * @param       $parent
     * @return      null/string
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get the parent's company name
     */
    public static function Get_Company_ParentName($level, $parent) {
        /* Variables    */
        global $DB;

        try {
            /* SQL Instruction   */
            $sql = " SELECT     name
                     FROM       {report_gen_companydata}
                     WHERE      id = :parent
                        AND     hierarchylevel = :level ";

            /* Research Criteria */
            $params = array();
            $params['level']    = $level;
            $params['parent']   = $parent;

            /* Execute */
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Company_ParentName

    /**
     * @static
     * @param           $level              Hierarchy level of company.
     * @param           $company_info       Company Identity.
     * @param           int $parent         Company's parent identity.
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Return if one company already exists to a specific level and parent.
     */
    public static function Exists_Company($level, $company_info,$parent=0) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria */
            $params = array();
            if ($company_info['name']) {
                $params['company']  = $company_info['name'];
            }else {
                $params['company']  = $company_info['other_company'];
            }

            $params['level']    = $level;
            $params['parent']   = $parent;

            /* SQL Instruction */
            $sql = " SELECT   rgc.id
                     FROM     {report_gen_companydata}  rgc ";


            if ($level > 1) {
                $sql .= " JOIN    {report_gen_company_relation} rgcr  ON  rgc.id        = rgcr.companyid
                                                                      AND rgcr.parentid = :parent ";
            }//if_level_1

            $sql .= " WHERE      rgc.hierarchylevel = :level
                        AND      rgc.name = :company ";

            if ($level == 3) {
                if (isset($company_info['county']) && $company_info['county']) {
                    $params['county']   = $company_info['county'];
                    $sql .= " AND rgc.idcounty = :county ";
                }//if_county
                if (isset($company_info['municipality_id']) && $company_info['municipality_id']) {
                    $params['muni']   = $company_info['municipality_id'];
                    $sql .= " AND rgc.idmuni = :muni ";
                }//if_muni
            }//if_level_3

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                return true;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Exists_Company

    /**
     * @static
     * @param           $company_id
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    02/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all details of the company
     */
    public static function Get_CompanyInfo($company_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company_id),'id,name,idcounty,idmuni');
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyInfo

    /**
     * @static
     * @param           $company_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    13/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Check if one company has employees.
     */
    public static function Company_HasEmployees($company_id) {
        /* Variables    */
        global $DB;
        $count = 0;

        try {
            /* Research Criteria */
            $params = array();
            $params['parent']   = $company_id;
            $params['dtotype']  = 'rgcompany';

            /* SQL Instruction   */
            $sql = " SELECT 	count(distinct u.id) as 'count'
                     FROM		{user} 			  u
                        JOIN	{user_info_data}  uid	ON 	u.id 		 	= uid.userid
                                                        AND uid.data 	 	= :parent
                        JOIN	{user_info_field} uif	ON	uid.fieldid 	= uif.id
                                                        AND uif.datatype 	= :dtotype ";

            /* Execute */
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                $count = $rdo->count;
            }
            return $count;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Company_HasEmployees

    /**
     * @static
     * @param           $company_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    11/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are another companies under it.
     */
    public static function Company_HasChildren($company_id){
        /* Variables    */
        global $DB;
        $count = 0;

        try {
            /* Search Criteria */
            $params = array();
            $params['company']  = $company_id;

            /* SQL Instruction */
            $sql = " SELECT     count(distinct rgcr.parentid) as 'count'
                     FROM       {report_gen_companydata}      rgc
                        JOIN    {report_gen_company_relation} rgcr  ON rgc.id = rgcr.parentid
                     WHERE      rgc.id = :company ";

            /* Execute */
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                $count = $rdo->count;
            }
            return $count;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Company_HasChildren

    /**
     * @static
     * @param           $instance
     * @param           null $parent
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    10/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Insert a new company
     */
    public static function Insert_CompanyLevel($instance,$parent = null) {
        /* Variables    */
        global $DB;

        try {
            if ($instance->id = $DB->insert_record('report_gen_companydata',$instance)) {
                if (!is_null($parent)) {
                    $instance_relation = new stdClass();
                    $instance_relation->companyid   = $instance->id;
                    $instance_relation->parentid    = $parent;
                    $instance_relation->modified    = $instance->modified;

                    $instance_relation->id = $DB->insert_record('report_gen_company_relation',$instance_relation);
                }//if_parent
            }//if

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Insert_CompanyLevel

    /**
     * @static
     * @param           $instance
     * @throws          Exception
     *
     * @creationDate    10/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update company data.
     */
    public static function Update_CompanyLevel($instance) {
        /* Variables    */
        global $DB;

        try {
            $DB->update_record('report_gen_companydata',$instance);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_CompanyLevel

    /**
     * @static
     * @param           $company_id
     * @return          bool
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove the company
     */
    public static function Delete_Company($company_id) {
        /* Variables    */
        global $DB;

        try {
            $DB->delete_records('report_gen_companydata',array('id'=>$company_id));

            if ($rdo = $DB->get_record('report_gen_company_relation',array('companyid'=>$company_id))) {
                $DB->delete_records('report_gen_company_relation',array('id'=>$rdo->id));
            }//if

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_Company
}//class_company_structure

