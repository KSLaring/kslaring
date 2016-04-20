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
                    $action = substr($key, 4,-1);
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
     *
     * @updateDate  23/10/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Clean code.
     */
    public static function Get_EmployeeLevel($parent) {
        /* Variables    */
        global $DB;
        $employee_list  = array();
        $sql            = null;
        $info           = null;

        try {

            /* SQL Instruction      */
            $sql = " SELECT	    DISTINCT  	u.id,
                                            CONCAT(u.firstname,' ',u.lastname) as 'name'
                     FROM		{user}					    u
                        JOIN	{user_info_competence_data}	uicd		ON 		uicd.userid 	= u.id
                                                                        AND		uicd.companyid	IN (" .$parent . ")
                     WHERE		u.deleted = 0
                     ORDER BY 	u.lastname, u.firstname ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql)) {
                foreach ($rdo as $field) {
                    /* Add Employee */
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
                    /* New Company  */
                    $instance->name     = $data->name;
                    self::Insert_CompanyLevel($instance,$parents[$level-1]);

                    break;
            }//switch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_CompanyLevel

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
        $levelOne   = null;
        $levelTwo   = null;
        $levelTre   = null;



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
                /* Params           */
                $params = array();
                $params['parent_public'] = $instance->public;

                /* Level One */
                $levelOne = CompetenceManager::GetCompanies_LevelList(1,$data->company);
                unset($levelOne[0]);
                $levelOne = implode(',',array_keys($levelOne));
                if ($levelOne) {
                    $sqlUpdate = " UPDATE {report_gen_companydata}
                                   SET    public = :parent_public
                                   WHERE  id IN ($levelOne) ";

                    /* Execute  */
                    $DB->execute($sqlUpdate,$params);

                    /* Level Two */
                    $levelTwo = CompetenceManager::GetCompanies_LevelList(2,$levelOne);
                    unset($levelTwo[0]);
                    $levelTwo = implode(',',array_keys($levelTwo));
                    if ($levelTwo) {
                        $sqlUpdate = " UPDATE {report_gen_companydata}
                                          SET public = :parent_public
                                       WHERE  id IN ($levelTwo) ";

                        /* Execute  */
                        $DB->execute($sqlUpdate,$params);

                        /* Level Tre */
                        $levelTre = CompetenceManager::GetCompanies_LevelList(3,$levelTwo);
                        unset($levelTre[0]);
                        $levelTre = implode(',',array_keys($levelTre));
                        if ($levelTre) {
                            $sqlUpdate = " UPDATE {report_gen_companydata}
                                              SET public = :parent_public
                                           WHERE  id IN ($levelTre) ";

                            /* Execute  */
                            $DB->execute($sqlUpdate,$params);
                        }//if_levelTre
                    }//if_levelTwo
                }//if_levelOne
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
        $trans = null;

        /* Start Transaction   */
        $trans = $DB->start_delegated_transaction();

        try {
            $DB->delete_records('report_gen_companydata',array('id'=>$company_id));

            if ($rdo = $DB->get_record('report_gen_company_relation',array('companyid'=>$company_id))) {
                $DB->delete_records('report_gen_company_relation',array('id'=>$rdo->id));
            }//if

            /* Delete Employees */
            $DB->delete_records('user_info_competence_data',array('companyid' => $company_id));

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Delete_Company


    /**
     * @param           $companyId
     * @param           $employees
     * @param           $all
     *
     * @throws          Exception
     *
     * @creationDate    10/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete employees connected with
     */
    public static function DeleteEmployees($companyId,$employees,$all=false) {
        /* Variables */
        global $DB;
        $sql    = null;
        $params = null;
        $trans  = null;

        /* Start Transaction   */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Search Criteria  */
            $params = array();
            $params['companyid'] = $companyId;

            /* Deleted Employees  */
            if ($all) {
                $DB->delete_records('user_info_competence_data',$params);
            }else {
                /* SQL Instruction */
                $sql = " DELETE
                         FROM   {user_info_competence_data}
                         WHERE  companyid = :companyid
                            AND userid IN ($employees) ";

                /* Execute */
                $DB->execute($sql,$params);
            }//if_all

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//DeleteEmployees

    /**
     * @param           $companyId
     * @param           $moveFrom
     * @param           $moveTo
     *
     * @throws          Exception
     *
     * @creationDate    20/04/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Move one company from one parent to other different parent
     */
    public static function MoveFrom_To($companyId,$moveFrom,$moveTo) {
        /* Variables */
        global  $DB;
        $rdo    = null;
        $params = null;

        try {
            /* First Original Record    */
            /* Criteria */
            $params = array();
            $params['companyid'] = $companyId;
            $params['parentid']  = $moveFrom;

            /* Execute */
            $rdo = $DB->get_record('report_gen_company_relation',$params);
            if ($rdo) {
                /* Update to the new parent */
                $rdo->parentid = $moveTo;

                /* Execute */
                $DB->update_record('report_gen_company_relation',$rdo);
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MoveFrom_To

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
}//class_company_structure

