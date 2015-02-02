<?php
/**
 * Library code for the Company Structure .
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  08/10/2014
 * @author      eFaktor     (fbv)
 *
 */

define('MANAGER_COMPANY_STRUCTURE_LEVEL','level_');

class company_structure {

    /*********************/
    /* PUBLIC FUNCTIONS  */
    /*********************/

    /**
     * @param           $company_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    11/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company name
     */
    public static function Get_CompanyName($company_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company_id),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyName

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
     * @author          eFaktor     (fbv)
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

            /* SQL Instruction */
            $sql_Select = " SELECT     DISTINCT rcd.id,
                                       rcd.name,
                                       rcd.industrycode
                            FROM       {report_gen_companydata} rcd ";
            /* Join */
            $sql_Join = " ";
            if ($parent_id) {
                $sql_Join = " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                        AND   rcr.parentid  IN ($parent_id) ";
            }//if_level

            $sql_Where = " WHERE rcd.hierarchylevel = :level ";
            $sql_Order = " ORDER BY rcd.industrycode, rcd.name ASC ";

            /* SQL */
            $sql = $sql_Select . $sql_Join . $sql_Where . $sql_Order;

            $levels[0] = get_string('select_level_list','report_manager');
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                foreach ($rdo as $field) {
                    $levels[$field->id] = $field->industrycode . ' - '. $field->name;
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
     * @author      eFaktor         (fbv)
     *
     * Description
     * Get a list of all employees who work to a specific company.
     *
     * @updateDate  30/01/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Update to the level zero
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

            /* SQL Instruction      */
            $sql = " SELECT	    DISTINCT  	u.id,
                                            CONCAT(u.firstname,', ',u.lastname) as 'name'
                     FROM		{user}					    u
                        JOIN	{user_info_competence_data}	uicd		ON 		uicd.userid = u.id
                                                                        AND		(
                                                                                 (uicd.companyid = :parent)
                                                                                 OR
                                                                                 (uicd.companyid LIKE '%,"   . $parent . ",%')
                                                                                 OR
                                                                                 (uicd.companyid LIKE '%"    . $parent . ",%')
                                                                                 OR
                                                                                 (uicd.companyid LIKE '%,"   . $parent . "%')
                                                                                )
                     WHERE		u.deleted = 0
                     ORDER BY 	u.lastname, u.firstname ";

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
     * @author          eFaktor     (fbv)
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
            $params['industry_code']    = $company_info['industry_code'];
            $params['level']            = $level;
            $params['parent']           = $parent;
            /* Company Name     */
            if ($company_info['name']) {
                $params['company_name']  = $company_info['name'];
            }else {
                $params['company_name']  = $company_info['other_company'];
            }

            /* SQL Instruction */
            $sql = " SELECT   rgc.id
                     FROM     {report_gen_companydata}  rgc ";


            if ($level) {
                $sql .= " JOIN    {report_gen_company_relation} rgcr  ON  rgc.id        = rgcr.companyid
                                                                      AND rgcr.parentid = :parent ";
            }//if_level_1

            $sql .= " WHERE      rgc.hierarchylevel = :level
                        AND      rgc.name           = :company_name
                        AND      rgc.industrycode   = :industry_code";

            if (isset($company_info['company']) && $company_info['company']) {
                $params['company'] = $company_info['company'];
                $sql .= " AND rgc.id <> :company ";
            }
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
        $company_info = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company'] = $company_id;

            /* SQL Instruction */
            $sql = " SELECT    		rc.id,
                                    rc.name,
                                    rc.industrycode
                     FROM			{report_gen_companydata}	rc
                     WHERE          rc.id = :company ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $company_info = new stdClass();
                $company_info->id           = $rdo->id;
                $company_info->name         = $rdo->name;
                $company_info->industrycode = $rdo->industrycode;
            }//if_rdo

            return $company_info;
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

            /* SQL Instruction   */

            $sql = " SELECT	    count(distinct u.id) as 'count'
                     FROM		{user}					    u
                        JOIN	{user_info_competence_data}	uicd		ON 		uicd.userid = u.id
                                                                        AND		uicd.companyid = :parent

                     WHERE		u.deleted = 0";

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
     * @param           $company_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Count the parents connected with
     */
    public static function Company_CountParents($company_id) {
        /* Variables    */
        global $DB;

        try {
            return $DB->count_records('report_gen_company_relation',array('companyid' => $company_id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Company_CountParents

    /**
     * @static
     * @param           $company_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the parent list connected with
     */
    public static function Company_GetParentList($company_id) {
        /* Variables    */
        global $DB;
        $parent_lst = array();

        try {
            /* First Element    */
            $parent_lst[0] = get_string('select_level_list','report_manager');

            /* Search Criteria  */
            $params = array();
            $params['company_id'] = $company_id;

            /* SQL Instruction  */
            $sql = " SELECT		gr.parentid,
                                c.name
                    FROM		{report_gen_company_relation}	gr
                        JOIN	{report_gen_companydata}		c	ON 	c.id = gr.parentid
                    WHERE		gr.companyid = :company_id
                    ORDER BY 	c.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $parent) {
                    $parent_lst[$parent->parentid] = $parent->name;
                }//for_rdo_parent
            }//if_rdo

            return $parent_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Company_GetParentList

    /**
     * @static
     * @param           $data
     * @param           $parents
     * @param           $level
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new Company Level. Insert a new one or link.
     */
    public static function Add_CompanyLevel($data,$parents,$level) {
        /* Variables    */
        $instance = null;
        $index    = null;

        try {
            /* Company Info */
            $instance = new stdClass();
            $instance->hierarchylevel   = $level;
            $instance->modified         = time();
            $instance->industrycode     = $data->industry_code;

            switch ($level) {
                case 0:
                    /* Create a new Company */
                    $instance->name     = $data->name;
                    self::Insert_CompanyLevel($instance);

                    break;
                default:
                    /* New Company or Link Company  */
                    if ($data->name) {
                        /* New Company  */
                        $instance->name     = $data->name;
                        self::Insert_CompanyLevel($instance,$parents[$level-1]);
                    }else {
                       /* Link Company  */
                        self::Link_CompanyLevel($data->other_company,$parents[$level-1]);
                    }//if_else_name

                    break;
            }//switch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_CompanyLevel

    /**
     * @static
     * @param           $company_id
     * @param           $parent_id
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unlink Company and Parent
     */
    public static function Unlink_Company($company_id,$parent_id) {
        /* Variables    */
        global $DB;

        try {
            /* Delete company relation between Company and Parent   */
            /* Criteria    */
            $params = array();
            $params['companyid'] = $company_id;
            $params['parentid']  = $parent_id;
            $DB->delete_records('report_gen_company_relation',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Unlink_Company

    /**
     * @static
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    10/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update company data.
     */
    public static function Update_CompanyLevel($data) {
        /* Variables    */
        global $DB;
        $instance   = null;
        $index      = null;

        try {
            /* Company Info */
            $instance = new stdClass();
            $instance->id               = $data->company;
            $instance->name             = $data->name;
            $instance->modified         = time();
            $instance->industrycode     = $data->industry_code;

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

    /************/
    /* PRIVATE  */
    /************/

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
    private static function Insert_CompanyLevel($instance,$parent = null) {
        /* Variables    */
        global $DB;
        $company_relation = null;

        try {
            if ($id = $DB->insert_record('report_gen_companydata',$instance)) {
                if (!is_null($parent)) {
                    $company_relation = new stdClass();
                    $company_relation->companyid   = $id;
                    $company_relation->parentid    = $parent;
                    $company_relation->modified    = $instance->modified;

                    $company_relation->id = $DB->insert_record('report_gen_company_relation',$company_relation);
                }//if_parent
            }//if

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Insert_CompanyLevel

    /**
     * @static
     * @param           $company_to_link
     * @param           $parent
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Link a company
     */
    private static function Link_CompanyLevel($company_to_link,$parent) {
        /* Variables    */
        global $DB;
        $company_relation = null;

        try {
            $company_relation = new stdClass();
            $company_relation->companyid = $company_to_link;
            $company_relation->parentid = $parent;
            $company_relation->modified = time();

            $company_relation->id = $DB->insert_record('report_gen_company_relation',$company_relation);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Link_CompanyLevel
}//class_company_structure

