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
     * Description
     * Get the company name
     *
     * @param           int $company_id     Company id
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    11/12/2014
     * @author          eFaktor     (fbv)
     */
    public static function get_company_name($company_id) {
        /* Variables    */
        global $DB;

        try {
            // Execute
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company_id),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_company_name

    /**
     * Description
     * Return the action that the user want to carry out and the level.
     *
     * @static
     * @param       array   $data.      Form data.
     * @return      array               Action and level.
     *
     * @updateDate  08/10/2014.
     * @author      eFaktor     (fbv)
     */
    public static function get_action_level($data = array()) {
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
    }//get_action_level

    /**
     * Description
     * Get a list of all employees who work to a specific company.
     *
     * Update to the level zero
     *
     * @static
     * @param       string $parent      List of companies
     *
     * @return      array
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      eFaktor         (fbv)
     *
     * @updateDate  30/01/2015
     * @author      eFaktor     (fbv)
     */
    public static function get_employee_level($parent) {
        /* Variables    */
        global $DB;
        $employee_list  = array();
        $sql            = null;
        $info           = null;

        try {
            // SQL Instruction
            $sql = " SELECT   DISTINCT  	
                                u.id,
                                CONCAT(u.firstname,' ',u.lastname) as 'name'
                     FROM		{user}					    u
                        JOIN	{user_info_competence_data}	uicd		ON 		uicd.userid 	= u.id
                                                                        AND		uicd.companyid	IN ($parent)
                     WHERE		u.deleted = 0
                     ORDER BY 	u.lastname, u.firstname ";

            // Execute
            if ($rdo = $DB->get_records_sql($sql)) {
                foreach ($rdo as $field) {
                    // Add Employee
                    $employee_list[$field->id] = $field->name;
                }//for
            }//if_rdo

            return $employee_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_employee_level

    /**
     * Description
     * Get the parent's company name
     *
     * @static
     * @param       int $level      Hierarchy level
     * @param       int $parent     Company
     *
     * @return      null/string
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      eFaktor     (fbv)
     */
    public static function get_company_parent_name($level, $parent) {
        /* Variables    */
        global $DB;

        try {
            // SQL Instruction
            $sql = " SELECT     name
                     FROM       {report_gen_companydata}
                     WHERE      id             = :parent
                        AND     hierarchylevel = :level ";

            // Research Criteria
            $params = array();
            $params['level']    = $level;
            $params['parent']   = $parent;

            // Execute
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_company_parent_name

    /**
     * Description
     * Return if one company already exists to a specific level and parent.
     *
     * @static
     * @param           $level              Hierarchy level of company.
     * @param           $company_info       Company Identity.
     * @param           int $parent         Company's parent identity.
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     */
    public static function exists_company($level, $company_info,$parent=0) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            //Search Criteria
            $params = array();
            $params['industry_code']    = $company_info['industry_code'];
            $params['level']            = $level;
            $params['parent']           = $parent;
            //Company Name
            if ($company_info['name']) {
                $params['company_name']  = $company_info['name'];
            }else {
                $params['company_name']  = $company_info['other_company'];
            }

            // SQL Instruction
            $sql = " SELECT   rgc.id
                     FROM     {report_gen_companydata}  rgc ";

            // Level criteria
            if ($level) {
                $sql .= " JOIN    {report_gen_company_relation} rgcr  ON  rgc.id        = rgcr.companyid
                                                                      AND rgcr.parentid = :parent ";
            }//if_level_1


            $sql .= " WHERE      rgc.hierarchylevel = :level
                        AND      rgc.name           = :company_name
                        AND      rgc.industrycode   = :industry_code";

            // Company criteria
            if (isset($company_info['company']) && $company_info['company']) {
                $params['company'] = $company_info['company'];
                $sql .= " AND rgc.id <> :company ";
            }

            // Execute
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                return true;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//exists_company

    /**
     * Description
     * Get all details of the company
     *
     * @static
     * @param           $company_id
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    02/09/2014
     * @author          eFaktor     (fbv)
     */
    public static function get_company_info($company_id) {
        /* Variables    */
        global $DB;
        $company_info   = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            // Search Criteria
            $params = array();
            $params['company'] = $company_id;

            // SQL Instruction
            $sql = " SELECT    		rc.id,
                                    rc.name,
                                    rc.industrycode,
                                    rc.public,
                                    rc.mapped
                     FROM			{report_gen_companydata}	rc
                     WHERE          rc.id = :company ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            /**
            if ($rdo) {
                // Info company
                $company_info = new stdClass();
                $company_info->id           = $rdo->id;
                $company_info->name         = $rdo->name;
                $company_info->industrycode = $rdo->industrycode;
                $company_info->public       = $rdo->public;
            }//if_rdo **/

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_company_info

    /**
     * Description
     * Check if one company has employees.
     *
     * @static
     * @param           int $company_id     Company id
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    13/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     */
    public static function company_has_employees($company_id) {
        /* Variables    */
        global $DB;
        $count  = 0;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            // Research Criteria
            $params = array();
            $params['parent']   = $company_id;

            // SQL Instruction
            $sql = " SELECT	    count(distinct u.id) as 'count'
                     FROM		{user}					    u
                        JOIN	{user_info_competence_data}	uicd		ON 		uicd.userid = u.id
                                                                        AND		uicd.companyid = :parent

                     WHERE		u.deleted = 0";

            // Execute
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                $count = $rdo->count;
            }//if_rdo

            return $count;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_has_employees

    /**
     * Description
     * Check if there are another companies under it.
     *
     * @static
     * @param           int $company_id     Company id
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    11/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     */
    public static function company_has_children($company_id){
        /* Variables    */
        global $DB;
        $count  = 0;
        $params = null;
        $rdo    = null;
        $sql    = null;

        try {
            // Search Criteria
            $params = array();
            $params['company']  = $company_id;

            // SQL Instruction
            $sql = " SELECT     count(distinct rgcr.parentid) as 'count'
                     FROM       {report_gen_companydata}      rgc
                        JOIN    {report_gen_company_relation} rgcr  ON rgc.id = rgcr.parentid
                     WHERE      rgc.id = :company ";

            // Execute
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                $count = $rdo->count;
            }//if_rdo

            return $count;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_has_children

    /**
     * Description
     * Count the parents connected with
     *
     * @static
     * @param           int $company_id     Company id
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor     (fbv)
     */
    public static function company_count_parents($company_id) {
        /* Variables    */
        global $DB;

        try {
            return $DB->count_records('report_gen_company_relation',array('companyid' => $company_id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_count_parents

    /**
     * Description
     * Get the parent list connected with
     *
     * @static
     * @param           int $company_id     Company id
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor         (fbv)
     */
    public static function company_get_parent_list($company_id) {
        /* Variables    */
        global $DB;
        $parent_lst = array();
        $params     = null;
        $sql        = null;
        $rdo        = null;

        try {
            // First Element
            $parent_lst[0] = get_string('select_level_list','report_manager');

            // Search Criteria
            $params = array();
            $params['company_id'] = $company_id;

            // SQL Instruction
            $sql = " SELECT		gr.parentid,
                                c.name,
                                c.industrycode
                    FROM		{report_gen_company_relation}	gr
                        JOIN	{report_gen_companydata}		c	ON 	c.id = gr.parentid
                    WHERE		gr.companyid = :company_id
                    ORDER BY 	c.industrycode, c.name ";

            // Execute
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
    }//company_get_parent_list

    /**
     * Description
     * Add a new Company Level. Insert a new one or link.
     *
     * @static
     * @param           $data
     * @param           $parents
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    23/10/2014
     * @author          eFaktor     (fbv)
     */
    public static function add_company_level($data,$parents,$level) {
        /* Variables    */
        $instance = null;
        $index    = null;

        try {
            // Company Info
            $instance = new stdClass();
            $instance->hierarchylevel   = $level;
            $instance->modified         = time();
            $instance->industrycode     = $data->industry_code;
            if (isset($data->public)) {
                $instance->public = $data->public;
            }else {
                $instance->public = 0;
            }//if_public

            switch ($level) {
                case 0:
                    // Create a new Company
                    $instance->name     = $data->name;
                    self::insert_company_level($instance);

                    break;

                default:
                    // New Company
                    $instance->name     = $data->name;
                    self::insert_company_level($instance,$parents[$level-1]);

                    break;
            }//switch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_company_level

    /**
     * Description
     * Update company data.
     *
     * @static
     * @param           $data
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    10/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     */
    public static function update_company_level($data,$level) {
        /* Variables    */
        global $DB;
        $instance   = null;
        $index      = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelTre   = null;
        $params     = null;
        $sqlUpdate  = null;
        $trans      = null;

        // Begin Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Company Info
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

            // First Update Company Data
            $DB->update_record('report_gen_companydata',$instance);

            // Second Update the status company for the hierarchy of level Zero
            if ($level == 0) {
                // Criteria
                $params = array();
                $params['parent_public'] = $instance->public;

                // Level One
                $levelOne = CompetenceManager::GetCompanies_LevelList(1,$data->company);
                unset($levelOne[0]);
                $levelOne = implode(',',array_keys($levelOne));
                if ($levelOne) {
                    $sqlUpdate = " UPDATE {report_gen_companydata}
                                   SET    public = :parent_public
                                   WHERE  id IN ($levelOne) ";

                    // Execute
                    $DB->execute($sqlUpdate,$params);

                    // Level Two
                    $levelTwo = CompetenceManager::GetCompanies_LevelList(2,$levelOne);
                    unset($levelTwo[0]);
                    $levelTwo = implode(',',array_keys($levelTwo));
                    if ($levelTwo) {
                        $sqlUpdate = " UPDATE {report_gen_companydata}
                                          SET public = :parent_public
                                       WHERE  id IN ($levelTwo) ";

                        // Execute
                        $DB->execute($sqlUpdate,$params);

                        // Level Three
                        $levelTre = CompetenceManager::GetCompanies_LevelList(3,$levelTwo);
                        unset($levelTre[0]);
                        $levelTre = implode(',',array_keys($levelTre));
                        if ($levelTre) {
                            $sqlUpdate = " UPDATE {report_gen_companydata}
                                              SET public = :parent_public
                                           WHERE  id IN ($levelTre) ";

                            // Execute
                            $DB->execute($sqlUpdate,$params);
                        }//if_levelTre
                    }//if_levelTwo
                }//if_levelOne
            }//if_levelZero

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//update_company_level

    /**
     * Description
     * Remove the company
     *
     * @static
     * @param           int $company_id     Company id
     *
     * @return          bool
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     */
    public static function delete_company($company_id) {
        /* Variables    */
        global $DB;
        $trans = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            $DB->delete_records('report_gen_companydata',array('id'=>$company_id));

            if ($rdo = $DB->get_record('report_gen_company_relation',array('companyid'=>$company_id))) {
                $DB->delete_records('report_gen_company_relation',array('id'=>$rdo->id));
            }//if

            // Delete Employees
            $DB->delete_records('user_info_competence_data',array('companyid' => $company_id));

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//delete_company


    /**
     * Description
     * Delete employees connected with
     *
     * @param           int     $companyId      Company id
     * @param           string  $employees      List of employees
     * @param           bool    $all
     *
     * @throws          Exception
     *
     * @creationDate    10/03/2016
     * @author          eFaktor     (fbv)
     */
    public static function delete_employees($companyId,$employees,$all=false) {
        /* Variables */
        global $DB;
        $sql    = null;
        $params = null;
        $trans  = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Search Criteria
            $params = array();
            $params['companyid'] = $companyId;

            // Deleted Employees
            if ($all) {
                $DB->delete_records('user_info_competence_data',$params);
            }else {
                // SQL Instruction
                $sql = " DELETE
                         FROM   {user_info_competence_data}
                         WHERE  companyid = :companyid
                            AND userid IN ($employees) ";

                // Execute
                $DB->execute($sql,$params);
            }//if_all

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//delete_employees

    /**
     * Description
     * Move one company from one parent to other different parent
     *
     * @param           int $companyId      Company id
     * @param           int $moveFrom       Old Parent
     * @param           int $moveTo         New Parent
     *
     * @throws          Exception
     *
     * @creationDate    20/04/2016
     * @author          eFaktor     (fbv)
     */
    public static function move_from_to($companyId,$moveFrom,$moveTo) {
        /* Variables */
        global  $DB;
        $rdo    = null;
        $params = null;

        try {
            // First original record
            // Criteria
            $params = array();
            $params['companyid'] = $companyId;
            $params['parentid']  = $moveFrom;

            // Execute
            $rdo = $DB->get_record('report_gen_company_relation',$params);
            if ($rdo) {
                // Update to the new parent
                $rdo->parentid = $moveTo;

                // Execute
                $DB->update_record('report_gen_company_relation',$rdo);
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//move_from_to

    /************/
    /* PRIVATE  */
    /************/

    /**
     * Description
     * Insert a new company
     *
     * @static
     * @param           $instance
     * @param           null $parent
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    10/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     */
    private static function insert_company_level($instance,$parent = null) {
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
    }//insert_company_level
}//class_company_structure

