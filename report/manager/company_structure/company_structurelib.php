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
                                            CONCAT(u.firstname,' ',u.lastname) as 'name'
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
                                    rc.industrycode,
                                    rc.public
                     FROM			{report_gen_companydata}	rc
                     WHERE          rc.id = :company ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $company_info = new stdClass();
                $company_info->id           = $rdo->id;
                $company_info->name         = $rdo->name;
                $company_info->industrycode = $rdo->industrycode;
                $company_info->public       = $rdo->public;
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
                                c.name,
                                c.industrycode
                    FROM		{report_gen_company_relation}	gr
                        JOIN	{report_gen_companydata}		c	ON 	c.id = gr.parentid
                    WHERE		gr.companyid = :company_id
                    ORDER BY 	c.industrycode, c.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $parent) {
                    $parent_lst[$parent->parentid] = $parent->industrycode . ' - ' . $parent->name;
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
                    if (isset($data->public)) {
                        $instance->public = $data->public;
                    }else {
                        $instance->public = 0;
                    }//if_public
                    self::Insert_CompanyLevel($instance);

                    break;
                default:
                    $instance->public = $data->public_parent;
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
     * @param           $level
     * @throws          Exception
     *
     * @creationDate    10/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update company data.
     */
    public static function Update_CompanyLevel($data,$level) {
        /* Variables    */
        global $DB;
        $instance   = null;
        $index      = null;
        $hierarchyLevelZero = null;
        $toUpdate           = '';

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Company Info */
            $instance = new stdClass();
            $instance->id               = $data->company;
            $instance->name             = $data->name;
            $instance->modified         = time();
            $instance->industrycode     = $data->industry_code;
            if ($level == 0) {
                if (isset($data->public)) {
                    $instance->public = $data->public;
                }else {
                    $instance->public = 0;
                }//if_public
            }//if_levelZero

            /* First Update Company Data */
            $DB->update_record('report_gen_companydata',$instance);
            /* Second Update the status company for the hierarchy of level Zero */
            if ($level == 0) {
                /* Get My Hierarchy */
                $hierarchyLevelZero = self::GetHierarchy_LevelZero($data->company);
                if ($hierarchyLevelZero) {
                    $toUpdate = $instance->id;

                    /* Add Level One    */
                    if ($hierarchyLevelZero->levelOne) {
                        $toUpdate .= ',' . $hierarchyLevelZero->levelOne;
                    }//if_levelOne

                    /* Add Level Two    */
                    if ($hierarchyLevelZero->levelTwo) {
                        $toUpdate .= $hierarchyLevelZero->levelTwo;
                    }//if_levelTwo

                    /* Add Level Three  */
                    if ($hierarchyLevelZero->levelThree) {
                        $toUpdate .= $hierarchyLevelZero->levelThree;
                    }//if_levelThree

                    /* Params           */
                    $params = array();
                    $params['parent_public'] = $instance->public;

                    /* SQL Instruction  */
                    $sqlUpdate = " UPDATE {report_gen_companydata}
                                   SET    public = :parent_public
                                   WHERE  id IN ($toUpdate) ";

                    /* Execute  */
                    $DB->execute($sqlUpdate,$params);
                }//if_hierarchy
            }//if_levelZero

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

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

    /**
     * @param           $levelZero
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    03/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the companies connected with a specific level zero
     */
    private static function GetHierarchy_LevelZero($levelZero) {
        /* Variables    */
        global $DB;
        $hierarchyLevelZero   = null;

        try {
            /* SEARCH Criteria  */
            $params = array();
            $params['levelzero'] = $levelZero;

            /* SQL Instruction  */
            $sql = " SELECT			GROUP_CONCAT(DISTINCT level_one.companyid ORDER BY level_one.companyid SEPARATOR ',') 		as 'level_one',
                                    GROUP_CONCAT(DISTINCT level_two.companyid ORDER BY level_two.companyid SEPARATOR ',') 		as 'level_two',
                                    GROUP_CONCAT(DISTINCT level_three.companyid ORDER BY level_three.companyid SEPARATOR ',') 	as 'level_three'
                     FROM			{report_gen_companydata}			co
                        LEFT JOIN 	{report_gen_company_relation} 	    level_one 	ON level_one.parentid 	= co.id
                        LEFT JOIN	{report_gen_company_relation}		level_two	ON level_two.parentid	= level_one.companyid
                        LEFT JOIN	{report_gen_company_relation}		level_three	ON level_three.parentid = level_two.companyid
                     WHERE			co.hierarchylevel = 0
                        AND			co.id             = :levelzero ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Hierarchy Level Zero */
                $hierarchyLevelZero = new stdClass();
                $hierarchyLevelZero->levelZero  = $levelZero;
                $hierarchyLevelZero->levelOne   = $rdo->level_one;
                $hierarchyLevelZero->levelTwo   = $rdo->level_two;
                $hierarchyLevelZero->levelThree = $rdo->level_three;
            }//if_rdo

            return $hierarchyLevelZero;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetHierarchy_LevelZero
}//class_company_structure
