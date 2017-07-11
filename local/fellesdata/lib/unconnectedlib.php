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
 * Fellesdata Integration - Library to unconnected KS Organizations
 *
 * @package         local/fellesdata
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/02/2017
 * @author          eFaktor     (fbv)
 *
 */

class KS_UNCONNECT {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Javascript call for selectors
     *
     * @param       string $selector
     * @param       string $sunconnect
     * @param       string $removeSearch
     * @param       string $aunconncet
     * @param       string $addSearch
     *
     * @throws      Exception
     *
     * @creationDate    17/02/2017
     * @author          eFaktor (fbv)
     *
     */
    public static function ini_KS_unconnect_selectors($selector,$sunconnect,$removeSearch,$aunconncet,$addSearch) {
        /* Variables    */
        global $PAGE;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $hashAdd    = null;
        $hashRemove = null;

        try {
            /* Initialise variables */
            $name       = 'ks_unconnect';
            $path       = '/local/fellesdata/js/unconnect.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires,
                'strings'     => $strings);


            $PAGE->requires->js_init_call('M.core_user.init_ks_unconnected',
                array($selector,$sunconnect,$removeSearch,$aunconncet,$addSearch),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Ini_FSJobroles_Selectors

    /**
     * Description
     * Get the levels connected with
     *
     * @return      array
     * @throws      Exception
     *
     * @creationDate    17/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_levels() {
        /* Variables */
        $lstlevels  = array();

        try {
            /* Temporary for L5 */
            $lstlevels[0] = get_string('sel_parent','local_fellesdata');
            $lstlevels[1] = 1;
            $lstlevels[2] = 2;
            $lstlevels[3] = 3;

            return $lstlevels;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch        
    }//get_levels

    /**
     * Description
     * Get all companies from KS that have not been mapped yet
     *
     * @param       integer $level
     * @param       string  $search
     *
     * @return              array
     * @throws              Exception
     *
     * @creationDate        17/02/2017
     * @author              eFaktor     (fbv)
     */
    public static function find_ks_unconnected($level,$search) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sqlExtra       = null;
        $params         = null;
        $extra          = null;
        $locate         = null;
        $str            = null;
        $unconnected    = array();

        try {
            // Search criteria
            $params = array();
            $params['level'] = $level;

            // SQL instruction
            $sql = " SELECT 	DISTINCT 
                                    ks.companyid,
                                    ks.industrycode,
                                    ks.name
                     FROM			{ks_company}		ks
                        LEFT JOIN	{ksfs_company}	    ksfs 	ON ksfs.kscompany = ks.companyid
                        LEFT JOIN	{ksfs_org_unmap}	un		ON un.kscompany = ks.companyid
                     WHERE 			ks.hierarchylevel = :level
                        AND 		ksfs.id IS NULL
                        AND			un.id IS NULL ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " LOCATE('" . $str . "',ks.name) > 0 ";
                }//if_search_opt

                $sql .= " AND ($locate) ";
            }//if_search

            // Execute
            $sql.= " ORDER BY ks.industrycode,ks.name ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Add company
                    $unconnected[$instance->companyid] = $instance->industrycode . " - " . $instance->name;
                }//for_rdo
            }//if_rdo

            return $unconnected;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//find_ks_unconnected

    /**
     * Description
     * Get all companies that have to be deleted from KS 
     * 
     * @param       integer $level
     * @param       string  $search
     *
     * @return              array
     * @throws              Exception
     * 
     * @creationDate        17/02/2017
     * @author              eFaktor     (fbv)
     */
    public static function find_ks_to_unconnect($level,$search) {
        /* Varibales */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sqlExtra       = null;
        $params         = null;
        $extra          = null;
        $locate         = null;
        $str            = null;
        $tounconnect    = array();

        try {
            // Search criteria
            $params = array();
            $params['fs']       = 0;
            $params['sync']     = 0;
            $params['tosync']   = 1;
            $params['level']    = $level;

            // SQL Instruction
            $sql = " SELECT   DISTINCT 
                                ks.companyid,
                                ks.industrycode,
                                ks.name
                     FROM		{ksfs_org_unmap}	un		
                        JOIN	{ks_company}		ks 	ON  ks.companyid 		= un.kscompany
                                                        AND ks.hierarchylevel 	= :level
                     WHERE 		un.fscompany = :fs
                        AND		un.tosync 	 = :tosync
                        AND		un.sync 	 = :sync ";

            // Search
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " LOCATE('" . $str . "',ks.name) > 0 ";
                }//if_search_opt

                $sql .= " AND ($locate) ";
            }//if_search

            // Execute
            $sql.= " ORDER BY ks.industrycode,ks.name ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $tounconnect[$instance->companyid] = $instance->industrycode . " - " . $instance->name;
                }//for_rdo
            }//if_Rdo
            
            return $tounconnect;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//find_ks_to_unconnect

    /**
     * Description
     * Add companies that have to be deleted from KS
     * 
     * @param       array $companies
     * 
     * @throws            Exception
     * 
     * @creationDate      17/02/2017
     * @author            eFaktor   (fbv)  
     */
    public static function add_ks_to_unconnect($companies) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $params     = null;
        $instance   = null;

        try {
            // Search criteria
            $params = array();
            $params['tosync']   = 1;
            $params['sync']     = 0;

            if ($companies) {
                foreach ($companies as $company) {
                    // Check if already exists
                    $params['kscompany'] = $company;

                    // Execute
                    $rdo = $DB->get_record('ksfs_org_unmap',$params);
                    if (!$rdo) {
                        // Insert
                        $instance = new stdClass();
                        $instance->kscompany = $company;
                        $instance->fscompany = 0;
                        $instance->tosync = 1;
                        $instance->sync = 0;
                        
                        // Execute
                        $DB->insert_record('ksfs_org_unmap',$instance);
                    }//if_rdo
                }//company
            }//if_companies
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_ks_to_unconnect

    /**
     * Description
     * Companies that have not to be deleted from KS
     * 
     * @param       array $companies
     * 
     * @throws            Exception
     * 
     * @creationDate      17/02/2017
     * @author            eFaktor       (fbv)
     */
    public static function remove_ks_to_unconnect($companies) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $params     = null;
        $instance   = null;

        try {
            if ($companies) {
                $params = array();
                foreach ($companies as $company) {
                    $params['kscompany'] = $company;
                    $params['fscompany'] = 0;
                    
                    // Execute
                    $DB->delete_records('ksfs_org_unmap',$params);
                }//for_companies
            }//if_companies
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//remove_ks_to_unconnect

    /***********/
    /* PRIVATE */
    /***********/
}//KS_UNCONNECT