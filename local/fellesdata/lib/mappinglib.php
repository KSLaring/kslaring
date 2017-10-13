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
 * Fellesdata Integration Mapping - Library
 *
 * @package         local/fellesdata
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/02/2016
 * @author          eFaktor     (fbv)
 *
 */
define('MAPPING_CO','co');
define('MAPPING_JR','jr');

define('GENERIC_JR','ge');
define('NO_GENERIC_JR','no');

define('ACT_ADD',0);
define('ACT_UPDATE',1);
define('ACT_DELETE',2);
define('FS_LE_2',2);
define('FS_LE_5',3);
define('FS_LE_1',1);

define('MAP',1);
define('UNMAP',2);

class FS_MAPPING {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Initialize parent selector
     *
     * @param           $selectorlevel
     * @param           $hidelevel
     * @param           $selectorparent
     * @param           $hideparent
     *
     * @throws          Exception
     *
     * @creationDate    02/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function init_fsks_parent_selector($selectorlevel,$hidelevel,$selectorparent,$hideparent) {
        /* Variables */
        global $PAGE;
        $name       = null;
        $path       = null;
        $requires   = null;
        $jsmodule   = null;


        try {
            // Initialise variables
            $name       = 'fs_company';
            $path       = '/local/fellesdata/js/mapping.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification','datatype-number','arraysort');

            // Initialise js module
            $jsmodule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires
            );

            // Javascript
            $PAGE->requires->js_init_call('M.core_user.init_fs_company_to_map',
                array($selectorlevel,$hidelevel,$selectorparent,$hideparent),
                false,
                $jsmodule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//init_fsks_parent_selector

    /**
     * @param           $ks
     * @param           $level
     * @param           $scompaniesSelector
     * @param           $acompaniesSelector
     *
     * @throws          Exception
     *
     * @creationDate    08/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Ini the selectors
     */
    public static function Init_FSCompanies_Selectors($ks,$level,$scompaniesSelector,$acompaniesSelector) {
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
            $name       = 'fs_company';
            $path       = '/local/fellesdata/js/organization.js';
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


            $PAGE->requires->js_init_call('M.core_user.init_fs_company',
                                          array($ks,$level,$scompaniesSelector,$acompaniesSelector),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_SuperUsers_Selectors

    /**
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    08/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Init the search selectors
     */
    public static function Init_Search_Selectors($addSearch,$removeSearch,$level) {
        /* Variables */
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
            $name       = 'search_selector';
            $path       = '/local/fellesdata/js/search.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            /* Super Users - Add Selector       */
            self::Init_Search_AddSelector($addSearch,$jsModule,$level);
            /* Super Users - Remove Selector    */
            self::Init_Search_RemoveSelector($removeSearch,$jsModule,$level);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_search_Selectors

    /**
     * @param           $ks_jobrole
     * @param           $sjobrole
     * @param           $ajobrole
     *
     * @throws          Exception
     *
     * @creationDate    18/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize jobroles selector
     */
    public static function Ini_FSJobroles_Selectors($ks_jobrole,$sjobrole,$ajobrole) {
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
            $name       = 'fs_jobrole';
            $path       = '/local/fellesdata/js/jobroles.js';
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


            $PAGE->requires->js_init_call('M.core_user.init_fs_company',
                array($ks_jobrole,$sjobrole,$ajobrole),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Ini_FSJobroles_Selectors

    /**
     * @param           $addSearch
     * @param           $removeSearch
     *
     * @throws          Exception
     *
     * @creationDate    18/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize the selector for searching
     */
    public static function Init_Search_Jobroles($addSearch,$removeSearch) {
        /* Variables */
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
            $name       = 'search_jobrole';
            $path       = '/local/fellesdata/js/searchjr.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            /* Add Jobrole Selector       */
            self::Init_Search_AddJobrole($addSearch,$jsModule);
            /* Remove Job role Selector    */
            self::Init_Search_RemoveJobrole($removeSearch,$jsModule);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Search_Jobroles

    /**
     * @param           $fsCompanies
     * @param           $ksParent
     *
     * @throws          Exception
     *
     * @creationDate    08/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update fs company with the ks parent
     */
    public static function UpdateKSParent($fsCompanies,$ksParent) {
        /* Variables */
        global $DB;
        $instance = null;

        try {
            foreach ($fsCompanies as $fs) {
                $instance = new stdClass();
                $instance->id       = $fs;
                $instance->parent   = $ksParent;

                /* Update */
                $DB->update_record('fs_company',$instance);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateKSParent

    /**
     * @param           $fsCompanies
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    09/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get new comapnies that have to be deleted
     */
    public static function GetNewCompaniesToDelete($fsCompanies) {
        /* Variables */
        global $DB;
        $toDelete   = null;
        $rdo        = null;
        $sql        = null;
        $params     = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['new']  = 1;
            $params['sync'] = 0;

            /* SQL Instruction  */
            $sql = " SELECT	fs.id,
                            fs.companyid
                     FROM	{fs_company}	fs
                     WHERE	fs.companyid IN ($fsCompanies)
                        AND	fs.new 			= :new
                        AND fs.synchronized = :sync
                        AND parent	 		= 0 ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance){
                    $toDelete[$instance->id] = "'" . $instance->companyid . "'";
                }//for_rdo
            }//if_rdo

            return $toDelete;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetNewCompaniesToDelete

    /**
     * Description
     * Clean the new companies
     *
     * @param           $fsCompanies
     * @throws          Exception
     *
     * @creationDate    09/06/2016
     * @author          author      (fbv)
     */
    public static function clean_new_companies($fsCompanies) {
        /* Variables */
        global $DB;
        $in     = null;
        $time   = null;

        try {
            // Local time
            $time = time();

            // SQL Instruction
            $in = implode(',',array_keys($fsCompanies));
            $sql = "DELETE FROM {fs_company}
                    WHERE id IN ($in) ";

            /* Execute */
            $DB->execute($sql);

            // Imported = 0
            $in = implode(',',$fsCompanies);
            $sql = " UPDATE {fs_imp_company}
                      SET   imported      = :imp,
                            timemodified  = :up
                     WHERE  org_enhet_id IN ($in) ";

            // Execute
            $DB->execute($sql,array('imp' => 0,'up' => $time));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//clean_new_companies


    /**
     * @param           $level
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    07/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get parents
     */
    public static function GetParents($level) {
        /* Variables */
        global $DB;
        $lstParents = null;
        $rdo        = null;
        $params     = null;
        $pluginInfo = null;

        try {
            // First element of the list
            $lstParents[0] = get_string('sel_parent','local_fellesdata');

            // Search criteria
            $params          = array();
            if ($level != FS_LE_1) {
                $params['hierarchylevel'] =  ($level - 1);
            }else if ($level == FS_LE_1) {
                $params['hierarchylevel'] =  1;
            }

            if ($level == FS_LE_2) {
                // plugin info
                $pluginInfo     = get_config('local_fellesdata');
                $params['name'] = $pluginInfo->ks_muni;
            }//if_FS_LE_2

            // Execute
            $rdo = $DB->get_records('ks_company',$params,'industrycode,name');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstParents[$instance->companyid] = $instance->industrycode . ' - ' . $instance->name;
                }//rdo
            }//ifR_do

            return $lstParents;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetParents

    /**
     * Description
     * Parents synchronized
     *
     * @param           $level
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    02/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_parents_synchronized($level) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $lstparents = null;
        $params     = null;
        $plugin     = null;
        $parents    = null;

        try {
            // Plugin info
            $plugin = get_config('local_fellesdata');

            // First element
            $lstparents = array();
            $lstparents[0] = get_string('sel_parent','local_fellesdata');

            // Search criteria
            $params = array();
            $ini    = null;
            $nivaa  = null;
            $diff   = null;
            if ($level != FS_LE_1) {
                $params['level'] = ($level - 1);
                switch ($level) {
                    case FS_LE_2:
                        $ini    = $plugin->map_one;
                        $nivaa  = $plugin->map_two;
                        $params['nivaa']  = $plugin->map_two;
                        $diff = $plugin->map_two - $plugin->map_one;

                        break;
                    case FS_LE_5:
                        $ini    = $plugin->map_two;
                        $nivaa  = $plugin->map_three;
                        $params['nivaa'] = $plugin->map_three;
                        $diff = $plugin->map_three - $plugin->map_two;

                        break;
                    default:
                        $params['nivaa'] = 0;
                        $nivaa           = 0;
                }

                if ($diff > 1) {
                    for ($i=1;$i<$diff;$i++) {
                        $params['nivaa'] .= ',' .($i+$ini);
                    }
                }
            }else if ($level == FS_LE_1) {
                $params['level'] =  0;
                $params['nivaa'] =  0;
            }

            // SQL Instruction
            $sql = " SELECT   DISTINCT 
                                  ks.companyid,
                                  ks.name,
                                  fs_imp.org_nivaa 	as 'nivaa', 
                                  ksfs.fscompany    as 'parent'
                     FROM		  {ks_company}	    ks
                        JOIN	  {ksfs_company}	ksfs 	ON  ksfs.kscompany        = ks.companyid
                        JOIN	  {fs_company}	    fs	    ON  fs.companyid	      = ksfs.fscompany
                        JOIN	  {fs_imp_company}  fs_imp  ON  fs_imp.org_enhet_over = fs.companyid
							 							    AND fs_imp.org_nivaa      IN  (". $params['nivaa'] .")
                                                            AND fs_imp.imported       = 0
						-- Already synchronized
						LEFT JOIN {fs_company}	    syc	  	ON  syc.companyid 		= fs_imp.org_enhet_id
                                                            AND syc.level 			= fs_imp.org_nivaa
															AND syc.synchronized 	= 1
                     WHERE		ks.hierarchylevel = :level
                        AND     syc.id IS NULL
                     ORDER BY 	ks.name  ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $parents = array();
                foreach ($rdo as $instance) {
                    if ($nivaa) {
                        if ($instance->nivaa == $nivaa) {
                            $lstparents[$instance->companyid]   = $instance->name;
                            $parents[$instance->companyid]      = $instance->parent;
                        }else {
                            $ini = $instance->nivaa;
                            // Check childrens
                            $diff = $nivaa - $instance->nivaa;
                            if ($diff >= 1) {
                                $params = array();
                                $params['imported'] = 0;
                                $params['sync'] = 1;
                                $params['parent'] = $instance->parent;

                                for ($i=1;$i<=$diff;$i++) {
                                    $ini = ($i+$ini);
                                    $params['nivaa'] = $ini;

                                    $sql = " SELECT	DISTINCT 
                                                    fs_imp_ch.org_enhet_over as 'parent',
                                                    fs_imp_ch.org_nivaa      as 'nivaa'
                                             FROM		{fs_imp_company}		fs_imp
                                                -- Daughter
                                                JOIN	{fs_imp_company}		fs_imp_ch   ON  fs_imp_ch.org_enhet_over = fs_imp.org_enhet_id
                                                                                            AND fs_imp_ch.org_nivaa = :nivaa
                                                                                            AND fs_imp_ch.imported = :imported
                                                -- Already synchronized
                                                LEFT JOIN {fs_company}	      syc	  		ON  syc.companyid 		= fs_imp_ch.org_enhet_id
                                                                                            AND syc.level 			= fs_imp_ch.org_nivaa
                                                                                            AND syc.synchronized 	= :sync
                                             WHERE	fs_imp.org_enhet_over = :parent
                                                AND syc.id IS NULL ";

                                    // Execute
                                    $rdochild = $DB->get_records_sql($sql,$params);
                                    if ($rdochild) {
                                        if ($ini == $nivaa) {
                                            $lstparents[$instance->companyid]   = $instance->name;
                                            $aux = array();
                                            foreach ($rdochild as $child) {
                                                $aux[] = $child->parent;
                                            }
                                            if ($aux) {
                                                $parents[$instance->companyid] = implode(',',$aux);
                                            }
                                        }
                                    }//if_child
                                }///if_levels
                            }
                        }
                    }else {
                        $lstparents[$instance->companyid] = $instance->name;
                        $parents[$instance->companyid]    = $instance->parent;
                    }


                }
            }else if ($level == FS_LE_2) {
                $sql = " SELECT	  DISTINCT
                                    ks.companyid,
                                    ks.name
                         FROM	  {ks_company}	  ks
                            JOIN  {ksfs_company}  ksfs 	ON  ksfs.kscompany        = ks.companyid
                         WHERE	  ks.hierarchylevel = :level
                         ORDER BY ks.name ";

                // Execute
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    $lstparents[$rdo->companyid]    = $rdo->name;
                }
            }

            $parents = json_encode($parents);
            return array($lstparents,$parents);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_parents_synchronized

    /**
     * Description
     * Get company info connected
     *
     * @param           $company
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    02/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_company_ks_info($company) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['companyid'] = $company;

            // SQL Instruction
            $sql = " SELECT		ks.companyid,
                                ks.name
                     FROM		{ks_company}    ks
                     WHERE		ks.companyid = :companyid ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_company_ks_info

    /**
     * @param           $ks_jobrole
     * @param           $search
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find job roles have not been mapped to job role
     */
    public static function FindFSJobroles_NO_Mapped($ks_jobrole,$search) {
        /* Variables */
        global $DB;
        $sql        = null;
        $sqlExtra   = null;
        $locate     = null;
        $extra      = null;
        $rdo        = null;
        $params     = null;
        $fsJobroles = null;

        try {
            // Search criteria
            $params = array();
            $params['job_role'] = $ks_jobrole;
            $params['action']   = ACT_DELETE;

            // SQL Instruction
            $sql = " SELECT     DISTINCT
                                  fs.stillingskode,
                                  fs.stillingstekst
                     FROM		  {fs_imp_jobroles}	fs
                        LEFT JOIN {ksfs_jobroles}		ksfs	ON 	ksfs.fsjobrole = fs.stillingskode
                                                                AND	ksfs.ksjobrole = :job_role
                     WHERE        fs.imported = 0	
                          AND     fs.action != :action
                          AND     ksfs.id IS NULL ";

            // Search
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " (
                                    LOCATE('" . $str . "',fs.stillingstekst) > 0
                                    OR
                                    LOCATE('" . $str . "',fs.stillingstekst_alternativ) > 0
                                    OR
                                    LOCATE('" . $str . "',fs.stillingskode) > 0
                                 )";
                }//if_search_opt

                $sql .= " AND ($locate) ";
            }//if_search

            // Execute
            $sql .= " ORDER BY   fs.stillingskode,fs.stillingstekst ";

            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $fsJobroles[$instance->stillingskode] = $instance->stillingskode . " - " . $instance->stillingstekst;
                }
            }//if_rdo

            return $fsJobroles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindFSJobroles_NO_Mapped

    /**
     * @param           $ks_jobrole
     * @param           $search
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find job roles have been mapped to job role
     */
    public static function FindFSJobroles_Mapped($ks_jobrole,$search) {
        /* Variables */
        global $DB;
        $sql        = null;
        $sqlExtra   = null;
        $locate     = null;
        $rdo        = null;
        $params     = null;
        $fsJobroles = null;

        try {
            // Search criteria
            $params = array();
            $params['job_role'] = $ks_jobrole;

            // SQL Instruction
            $sql = " SELECT   DISTINCT 
                                fs.jrcode,
                                fs.jrname
                     FROM	    {fs_jobroles}	fs
                        JOIN    {ksfs_jobroles}	ksfs	ON 	ksfs.fsjobrole  = fs.jrcode
                                                        AND	ksfs.ksjobrole  = :job_role ";

            // Search
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " LOCATE('" . $str . "',fs.jrname) > 0
                                 OR
                                 LOCATE('" . $str . "',fs.jrcode) > 0 ";
                }//if_search_opt

                $sql .= " WHERE ($locate) ";
            }//if_search

            // Execute
            $sql .= " ORDER BY fs.jrcode,fs.jrname ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $fsJobroles[$instance->jrcode] = $instance->jrcode . " - " . $instance->jrname;
                }
            }//if_rdo

            return $fsJobroles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindFSJobroles_Mapped

    /**
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job roles from fellesdata
     */
    public static function GetKSJobroles() {
        /* Variables */
        global $DB;
        $lstParents = null;
        $rdo        = null;
        $params     = null;

        try {
            // First Element of the list
            $lstParents[0] = get_string('sel_parent','local_fellesdata');

            // Execute
            $rdo = $DB->get_records('ks_jobroles',null,'jobroleid,industrycode,name');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstParents[$instance->jobroleid] = $instance->name;
                }//rdo
            }//if_rdo

            return $lstParents;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetKSJobroles

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get levels
     */
    public static function getLevelsMapping() {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $lstLevels  = array();

        try {
            // SQL Instruction
            $sql = "SELECT MAX(hierarchylevel) as max
                    FROM    {ks_company} ";

            // Execute
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                //for ($i=0;$i<=$rdo->max;$i++) {
                //    $lstLevels[$i] = $i;
                //}
            }

            // Temporary for L5
            $lstLevels[0] = get_string('sel_parent','local_fellesdata');
            $lstLevels[1] = 1;
            $lstLevels[2] = 2;
            $lstLevels[3] = 3;

            return $lstLevels;
        }catch (Exception $ex) {
            throw $ex;
        }//
    }//getLevelsMapping

    public static function fs_companies_to_map($level,$parent,$fsparents,$sector,$notin,$start,$length) {
        /* Variables */
        $fscompanies    = null;
        $total          = null;
        $plugin         = null;
        $fsparent       = null;

        try {
            // Plugin info
            $plugin     = get_config('local_fellesdata');

            // Get Companies to Map
            $fscompanies = self::get_fscompanies_to_map($plugin,$level,$parent,$fsparents,$sector,$notin,$start,$length);
            // Get Total
            $total = self::get_total_fscompanies_to_map($plugin,$level,$fsparents,$sector,$notin);

            return array($fscompanies,$total);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_companies_to_map

    /**
     * @param           $tomap
     * @param           $parent
     * @param           $data
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mapping Companies
     */
    public static function mapping_fs_companies($tomap,$parent,$data) {
        /* Variables */
        global $SESSION;
        $possiblematch  = null;
        $reffs          = null;
        $info           = null;
        $fscompany      = null;
        $match          = null;
        $notin          = array();

        try {
            // Check not in
            if (isset($SESSION->notIn)) {
                $notin = $SESSION->notIn;
            }//notIn

            // Companies to map
            foreach ($tomap as $fscompany) {
                // Reference
                $reffs = 'FS_' . $fscompany->fscompany;

                // Get Possible Match
                if (isset($data->$reffs)) {
                    $possiblematch = $data->$reffs;

                    if ($possiblematch == '0') {
                        self::new_map_fs_company($fscompany,$parent->companyid,$data->le);
                    }else if ($possiblematch == 'no_sure') {
                        $notin["'" . $fscompany->fscompany . "'"] = "'" . $fscompany->fscompany . "'";
                    }else {
                        // Mapping between FSand KS
                        $info = explode('#KS#',$data->$reffs);
                        $match = $fscompany->matches[$info[1]];
                        self::map_fs_company($fscompany,$match,$data->le);
                    }
                }//if_reffs
            }//for_tomap

            return array(true,$notin);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MappingFSCompanies

    /**
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary tables for FS companies
     */
    public static function CleanOrganizationMapped() {
        /* Variables    */
        global $DB;
        $sql = null;
        $rdo = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 1;
            $params['deleted']  = ACT_DELETE;

            // SQL Instruction
            $sql = " DELETE FROM {fs_imp_company}
                     WHERE  imported = :imported
                        AND action != :deleted ";

            // Execute
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanOrganizationMapped

    /**
     * @param           $toMap
     * @param           $ksJobRole
     * @param           $action
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @updateDate      18/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mapping/Unmapping jobroles
     */
    public static function MappingFSJobRoles($toMap,$ksJobRole,$action) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $infoFS     = null;
        $function   = null;

        try {
            // Get action
            switch ($action) {
                case MAP:
                    $function = "MapFSJobRole";

                    break;
                case UNMAP:
                    $function = "UnMapFSJobRole";

                    break;
            }

            // Map/UnMap FS Job Roles
            foreach ($toMap as $fs) {
                // Get Info FS Job role
                $rdo = $DB->get_record('fs_imp_jobroles',array('STILLINGSKODE' => $fs));
                // Mapping
                if ($rdo) {

                    self::$function($rdo,$ksJobRole);
                }//if_rdo
            }//for_fs_jobrole

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MappingFSJobRoles

    /**
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary tables for FS job roles
     */
    public static function CleanJobRolesMapped() {
        /* Variables    */
        global $DB;
        $sql = null;
        $rdo = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 1;
            $params['deleted']  = ACT_DELETE;

            // SQL Instruction
            $sql = " DELETE FROM {fs_imp_jobroles}
                     WHERE  imported = :imported
                        AND action != :deleted ";
            // Execute
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanJobRolesMapped

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $fsJobroles
     * @param           $ks_jobrole
     * @param           $search
     * @param           $notIn
     *
     * @throws          Exception
     *
     * @creationDate    18/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job roles unmapped but no synchronized yet
     */
    private static function GetUnMappedJR(&$fsJobroles,$ks_jobrole,$search,$notIn) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $sqlExtra   = null;
        $locate     = null;

        try {
            /* Search criteria */
            $params = array();
            $params['job_role'] = $ks_jobrole;

            /* SQL Instruction */
            $sql = " SELECT   fs.jrcode,
                              fs.jrname
                     FROM	  {ksfs_jr_unmap}	un
                        JOIN  {fs_jobroles}		fs ON fs.jrcode = un.fsjobrole
                     WHERE	un.fsjobrole NOT IN ($notIn)
                        AND	un.ksjobrole = :job_role ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " (
                                    LOCATE('" . $str . "',fs.jrname) > 0
                                    OR
                                    LOCATE('" . $str . "',fs.jralternative) > 0
                                    OR
                                    LOCATE('" . $str . "',fs.jrcode) > 0
                                 )";
                }//if_search_opt

                $sql .= " AND ($locate) ";
            }//if_search

            /* Execute */
            $sql .= " ORDER BY   fs.jrcode,fs.jrname ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $fsJobroles[$instance->jrcode] = $instance->jrcode . " - " . $instance->jrname;
                }
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUnMappedJR

    /**
     * Description
     * Map a FS Company with the 'new' option
     *
     * @param           $fscompany
     * @param           $parent
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function new_map_fs_company($fscompany,$parent,$level) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $infocompany    = null;
        $infoimp        = null;
        $trans          = null;
        $time           = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // Company to update or create
            $infocompany = new stdClass();
            $infocompany->companyid     = $fscompany->fscompany;
            $infocompany->name          = str_replace("'"," ",$fscompany->name);
            $infocompany->fs_parent     = $fscompany->fs_parent;
            $infocompany->parent        = $parent;
            $infocompany->level         = $level;
            $infocompany->privat        = $fscompany->privat;
            // Invoice data
            $infocompany->ansvar        = $fscompany->ansvar;
            $infocompany->tjeneste      = $fscompany->tjeneste;
            $infocompany->adresse1      = $fscompany->adresse1;
            $infocompany->adresse2      = $fscompany->adresse2;
            $infocompany->adresse3      = $fscompany->adresse3;
            $infocompany->postnr        = $fscompany->postnr;
            $infocompany->poststed      = $fscompany->poststed;
            $infocompany->epost         = $fscompany->epost;
            $infocompany->synchronized  = 0;
            $infocompany->new           = 1;
            $infocompany->timemodified  = time();

            // Check if already exist
            $params = array();
            $params['companyid'] = $fscompany->fscompany;
            $rdo = $DB->get_record('fs_company',$params);
            if (!$rdo) {
                // Execute
                $infocompany->id = $DB->insert_record('fs_company',$infocompany);
            }else {
                $infocompany->id = $rdo->id;

                // Execute
                $DB->update_record('fs_company',$infocompany);
            }//if_rdo

            // Update records as imported
            $infoimp = new stdClass();
            $infoimp->id            = $fscompany->id;
            $infoimp->org_enhet_id  = $fscompany->fscompany;
            $infoimp->imported      = 1;
            $infoimp->timemodified  = $time;
            $DB->update_record('fs_imp_company',$infoimp);

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//new_map_fs_company


    /**
     * @param           $fscompany
     * @param           $kscompany
     * @param           $level
     *
     * @throws          Exception
     *
     * @updateDate      02/10/2017
     * @author          eFaktor     (fbv)
     */
    private static function map_fs_company($fscompany,$kscompany,$level) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $infocompany    = null;
        $inforel        = null;
        $infoimp        = null;
        $time           = null;
        $trans          = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // to create or to update
            // FS company
            $infocompany = new stdClass();
            $infocompany->companyid     = $fscompany->fscompany;
            $infocompany->name          = str_replace("'"," ",$fscompany->name);
            $infocompany->fs_parent     = $fscompany->fs_parent; ;
            $infocompany->parent        = $kscompany->parent;
            // Invoice data
            $infocompany->privat        = $fscompany->privat;
            $infocompany->ansvar        = $fscompany->ansvar;
            $infocompany->tjeneste      = $fscompany->tjeneste;
            $infocompany->adresse1      = $fscompany->adresse1;
            $infocompany->adresse2      = $fscompany->adresse2;
            $infocompany->adresse3      = $fscompany->adresse3;
            $infocompany->postnr        = $fscompany->postnr;
            $infocompany->poststed      = $fscompany->poststed;
            $infocompany->epost         = $fscompany->epost;
            $infocompany->level         = $level;
            $infocompany->synchronized  = 1;
            $infocompany->new           = 0;
            $infocompany->timemodified  = $time;

            // Check if already exist
            $params = array();
            $params['companyid'] = $fscompany->fscompany;
            $rdo = $DB->get_record('fs_company',$params);
            if (!$rdo) {
                // Execute
                $DB->insert_record('fs_company',$infocompany);
            }else {
                $infocompany->id = $rdo->id;

                // Execute
                $DB->update_record('fs_company',$infocompany);
            }//if_rdo

            // Relation
            // Check if already exist
            $params = array();
            $params['fscompany'] = $fscompany->fscompany;
            $params['kscompany'] = $kscompany->kscompany;
            $rdo = $DB->get_record('ksfs_company',$params);
            if (!$rdo) {
                // Create relation
                $inforelation = new stdClass();
                $inforelation->fscompany = $fscompany->fscompany;
                $inforelation->kscompany = $kscompany->kscompany;

                // Execute
                $DB->insert_record('ksfs_company',$inforelation);
            }//if_no_exists

            // Update record as importer
            $infoImp = new stdClass();
            $infoImp->id            = $fscompany->id;
            $infoImp->org_enhet_id  = $fscompany->fscompany;
            $infoImp->imported      = 1;
            $infoImp->org_navn      = str_replace("'"," ",$fscompany->name);
            $infoImp->timemodified  = $time;

            // Execute
            $DB->update_record('fs_imp_company',$infoImp);

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//map_fs_company

    /**
     * Description
     * Get companies to map
     *
     * @param           $plugin
     * @param           $level
     * @param           $parent
     * @param           $fsparents
     * @param           $sector
     * @param           $notin
     * @param           $start
     * @param           $length
     *
     * @return          array
     * @throws          Exception
     *
     * @updateDate      02/10/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_fscompanies_to_map($plugin,$level,$parent,$fsparents,$sector,$notin,$start,$length) {
        /* Variables */
        global $DB;
        $rdo         = null;
        $rdo         = null;
        $params      = null;
        $fscompanies = array();
        $infocompany = null;
        $parentid    = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = ACT_DELETE;

            // Get level
            switch ($level) {
                case FS_LE_1:
                    $params['level'] = $plugin->map_one;

                    break;

                case FS_LE_2:
                    $params['level'] = $plugin->map_two;

                    break;

                case FS_LE_5;
                    $params['level'] = $plugin->map_three;

                    break;

                default:
                    $params['level'] = '-1';

                    break;
            }//level

            // SQL instruction
            $sql = " SELECT DISTINCT 
                                  fs_imp.id,
                                  fs_imp.org_enhet_id   		as 'fscompany',
                                  fs_imp.org_nivaa              as 'nivaa',
                                  fs_imp.org_navn	    		as 'name',
                                  fs_imp.org_enhet_over         as 'fs_parent',
                                  fs_imp.privat,
                                  fs_imp.ansvar,
                                  fs_imp.tjeneste,
                                  fs_imp.adresse1,
                                  fs_imp.adresse2,
                                  fs_imp.adresse3,
                                  fs_imp.postnr,
                                  fs_imp.poststed,
                                  fs_imp.epost
                     FROM		  {fs_imp_company}  fs_imp
                        LEFT JOIN {fs_company}	  	fs	  		ON fs.companyid 			= fs_imp.org_enhet_id
                     WHERE	      fs_imp.imported  		= :imported
                          AND     fs_imp.action   	   != :action
                          AND	  fs.id IS NULL
                          AND	  fs_imp.org_nivaa 		= :level ";

            // Parent criteria
            if ($fsparents) {
                $sql .= " AND	  fs_imp.org_enhet_over IN (" . $fsparents . ")";
            }

            // Add notIn criteria
            if ($notin) {
                $sql .= " AND fs_imp.org_enhet_id NOT IN ($notin) ";
            }//if_notIn

            if ($sector) {
                $sqlMatch = null;
                $searchBy = null;
                // Search by
                $sector     = str_replace(',',' ',$sector);
                $sector     = str_replace(' og ',' ',$sector);
                $sector     = str_replace(' eller ',' ',$sector);
                $sector     = str_replace('/',' ',$sector);
                $sector     = str_replace("'","\'",$sector);
                $searchBy   = explode(' ',$sector);

                foreach($searchBy as $match) {
                    if ($sqlMatch) {
                        $sqlMatch .= " OR ";
                    }//if_sqlMatch

                    $sqlMatch .= " fs_imp.org_navn like '%" . $match . "%' ";
                }//for_search

                $sql .= " AND (fs_imp.org_navn like '%" . $sector . "%' OR " . $sqlMatch . ")";
            }

            // Order criteria
            $sql .= " ORDER BY fs_imp.org_navn
                      LIMIT $start, $length ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $parentid = ($parent->companyid ? $parent->companyid : 0);
                foreach ($rdo as $instance) {
                    // Info company
                    $instance->matches       = self::get_possible_org_matches($instance->name,$level,$parentid,$sector);

                    // Add FS Company
                    $fscompanies[$instance->id] = $instance;
                }//for_Rdo
            }else if ($level == FS_LE_1) {
                $sql = " SELECT       ks.id,
                                      CONCAT(ks.companyid,'LE1')  as 'fscompany',
                                      ks.hierarchylevel             as 'nivaa',
                                      ks.name	    	            as 'name',
                                      '' 				            as 'fs_parent',
                                      '' 				            as privat,
                                      '' as ansvar,
                                      '' as tjeneste,
                                      '' as adresse1,
                                      '' as adresse2,
                                      '' as adresse3,
                                      '' as postnr,
                                      '' as poststed,
                                      '' as epost,
                                      ks.parent
                         FROM	      {ks_company} ks 
                            LEFT JOIN {fs_company} fs ON fs.companyid = CONCAT(ks.companyid,'LE1') 
                         WHERE        ks.hierarchylevel = :level
                            AND       fs.id IS NULL ";

                // Execute
                $rdo = $DB->get_record_sql($sql,array('level' => $level));
                if ($rdo) {
                    // Info company
                    $rdo->matches       = self::get_possible_org_matches($rdo->name,$level,$rdo->parent,$sector);

                    // Add FS Company
                    $fscompanies[$rdo->id] = $rdo;
                }
            }//if_rdo

            return $fscompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_fscompanies_to_map

    /**
     * @param       Object  $plugin
     * @param               $level
     * @param               $fsparents
     * @param               $sector
     * @param               $notIn
     *
     * @return              int
     * @throws              Exception
     *
     * @creationDate        09/06/2016
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get total companies to map
     */
    private static function get_total_fscompanies_to_map($plugin,$level,$fsparents,$sector,$notIn) {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = ACT_DELETE;

            // Get level
            switch ($level) {
                case FS_LE_1:
                    $params['level'] = $plugin->map_one;
                    break;
                case FS_LE_2:
                    $params['level'] = $plugin->map_two;
                    break;
                case FS_LE_5;
                    $params['level'] = $plugin->map_three;
                    break;
                default:
                    $params['level'] = '-1';
                    break;
            }//level

            // SQL Instruction
            $sql = " SELECT         count(DISTINCT  fs_imp.id) as 'total'
                     FROM			{fs_imp_company}  fs_imp
                        LEFT JOIN	{fs_company}	  fs	  ON fs.companyid = fs_imp.org_enhet_id
                     WHERE	        fs_imp.imported  = :imported
                          AND       fs_imp.action   != :action
                          AND	    fs.id IS NULL
                          AND	    fs_imp.org_nivaa      = :level ";

            // Parent criteria
            if ($fsparents) {
                $sql .= " AND	  fs_imp.org_enhet_over IN (" . $fsparents . ") ";
            }

            // Add notIn criteria
            if ($notIn) {
                $sql .= " AND fs_imp.org_enhet_id NOT IN ($notIn) ";
            }//if_notIn

            // Sector
            if ($sector) {
                $sqlMatch = null;
                $searchBy = null;
                // Search by
                $sector     = str_replace(',',' ',$sector);
                $sector     = str_replace(' og ',' ',$sector);
                $sector     = str_replace(' eller ',' ',$sector);
                $sector     = str_replace('/',' ',$sector);
                $sector     = str_replace("'","\'",$sector);
                $searchBy   = explode(' ',$sector);

                foreach($searchBy as $match) {
                    if ($sqlMatch) {
                        $sqlMatch .= " OR ";
                    }//if_sqlMatch

                    $sqlMatch .= " fs_imp.org_navn like '%" . $match . "%' ";
                }//for_search

                $sql .= " AND (fs_imp.org_navn like '%" . $sector . "%' OR " . $sqlMatch . ")";
            }//if_patterns

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
    }//get_total_fscompanies_to_map

    /**
     * @param           $fscompany
     * @param           $level
     * @param           $sector
     *
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get possible matches - Companies
     */
    private static function get_possible_org_matches($fscompany,$level,$parent,$sector) {
        /* Variables    */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $params     = null;
        $searchBy   = null;
        $sqlMatch   = null;
        $matches    = array();
        $infoMatch  = null;

        try {
            // Company name
            $fscompany = str_replace("'","\'",$fscompany);

            // Search criteria
            $params = array();
            $params['level']    = $level;
            $params['parent']   = $parent;

            // SQL Instruction
            $sql = " SELECT	ks.id,
                            ks.companyid as 'kscompany',
                            ks.name,
                            ks.industrycode,
                            ks.parent
                    FROM	{ks_company} ks
                    WHERE 	ks.hierarchylevel = :level 
                      AND   ks.parent = :parent ";

            // Pattern
            if ($sector) {
                $sector     = str_replace(',',' ',$sector);
                $sector     = str_replace(' og ',' ',$sector);
                $sector     = str_replace(' eller ',' ',$sector);
                $sector     = str_replace('/',' ',$sector);
                $sector     = str_replace("'","\'",$sector);
                $searchBy   = explode(' ',$sector);

                // Search by
                foreach($searchBy as $match) {
                    if ($sqlMatch) {
                        $sqlMatch .= " OR ";
                    }//if_sqlMatch
                    $sqlMatch .= " ks.name like '%" . $match . "%'";
                }//for_search

                $sql .= " AND (ks.name like '%" . $fscompany . "%' OR " . $sqlMatch . ")";
            }else {
                if ($level != FS_LE_1) {
                    $sql .= " AND ks.name like '%" . $fscompany . "%'";
                }
            }//if_sector

            // Execute
            $sql .= " ORDER BY ks.industrycode, ks.name ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info match
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->kscompany   = $instance->kscompany;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->industry    = $instance->industrycode;
                    $infoMatch->parent      = $instance->parent;

                    // Add match
                    $matches[$instance->kscompany] = $infoMatch;
                }//for_Rdo
            }//if_rdo

            return $matches;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_possible_org_matches


    /**
     * @param           $fsJobRole
     * @param           $ksJobRole
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mapping between FS ans KS job role
     */
    private static function MapFSJobRole($fsJobRole,$ksJobRole) {
        /* Variables    */
        global $DB;
        $infoImp        = null;
        $infoJobRole    = null;
        $infoRelation   = null;
        $rdo            = null;
        $params         = null;
        $time           = null;
        $trans          = null;

        /* Start transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Check if already exists  */
            $params = array();
            $params['jrcode'] = $fsJobRole->stillingskode;
            $rdo = $DB->get_record('fs_jobroles',$params);

            if (!$rdo) {
                /* New Entry    */
                $infoJobRole = new stdClass();
                $infoJobRole->jrcode            = $fsJobRole->stillingskode;
                $infoJobRole->jrname            = $fsJobRole->stillingstekst;
                $infoJobRole->jrjralternative   = $fsJobRole->stillingstekst_alternativ;
                $infoJobRole->synchronized      = 1;
                $infoJobRole->new               = 0;
                $infoJobRole->timemodified      = $time;

                /* Execute  */
                $DB->insert_record('fs_jobroles',$infoJobRole);
            }else {
                $rdo->jrname            = $fsJobRole->stillingstekst;
                $rdo->jrjralternative   = $fsJobRole->stillingstekst_alternativ;
                $rdo->synchronized      = 1;
                $rdo->timemodified      = $time;

                /* Execute  */
                $DB->update_record('fs_jobroles',$rdo);
            }//if_else

            /* Relation */
            /* Check if already exists  */
            $params = array();
            $params['fsjobrole'] = $fsJobRole->stillingskode;
            $params['ksjobrole'] = $ksJobRole;
            $rdo = $DB->get_record('ksfs_jobroles',$params);
            if (!$rdo) {
                /* Create Relation  */
                $infoRelation = new stdClass();
                $infoRelation->fsjobrole = $fsJobRole->stillingskode;
                $infoRelation->ksjobrole = $ksJobRole;

                /* Execute  */
                $DB->insert_record('ksfs_jobroles',$infoRelation);
            }//if_no_exists

            /* Updated record as imported   */
            $infoImp = new stdClass();
            $infoImp->id            = $fsJobRole->id;
            $infoImp->stillingskode = $fsJobRole->stillingskode;
            $infoImp->imported      = 1;
            $infoImp->timemodified  = $time;
            /* Execute  */
            $DB->update_record('fs_imp_jobroles',$infoImp);

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//MapFSJobRole

    private static function UnMapFSJobRole($fsJobRole,$ksJobRole) {
        /* Variables    */
        global $DB;
        $infoImp        = null;
        $infoJobRole    = null;
        $infoRelation   = null;
        $rdo            = null;
        $params         = null;
        $time           = null;
        $trans          = null;

        /* Start transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Relation */
            /* Check if already exists  */
            $params = array();
            $params['fsjobrole'] = $fsJobRole->stillingskode;
            $params['ksjobrole'] = $ksJobRole;
            $rdo = $DB->get_record('ksfs_jobroles',$params);
            if ($rdo) {
                $DB->delete_records('ksfs_jobroles',array('id' => $rdo->id));

                /* Check if is connected with other job roles   */
                $params = array();
                $params['fsjobrole'] = $fsJobRole->stillingskode;
                $rdo = $DB->get_record('ksfs_jobroles',$params);
                if (!$rdo) {
                    /* Deleted  */
                    $params = array();
                    $params['jrcode'] = $fsJobRole->stillingskode;
                    $rdo = $DB->get_record('fs_jobroles',$params);
                    if ($rdo) {
                        $DB->delete_records('fs_jobroles',array('id' => $rdo->id));
                    }//if_rdo
                }///if_Rdo
            }//if_rdo

            /* Updated record as imported   */
            $infoImp = new stdClass();
            $infoImp->id            = $fsJobRole->id;
            $infoImp->stillingskode = $fsJobRole->stillingskode;
            $infoImp->imported      = 0;
            $infoImp->timemodified  = $time;
            /* Execute  */
            $DB->update_record('fs_imp_jobroles',$infoImp);

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UnMapFSJobRole

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    07/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Init add search selector
     */
    private static function Init_Search_AddSelector($search,$jsModule,$level) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise Options Selector
            $options = array();
            $options['class']       = 'FindFSCompanies_WithoutParent';
            $options['name']        = 'acompanies';
            $options['multiselect'] = true;

            // Connect Selector User
            $hash                           = md5(serialize($options));
            $USER->search_selectors[$hash]  = $options;

            $PAGE->requires->js_init_call('M.core_user.init_search_selector',
                                          array('acompanies',$hash, $level, $search),
                                          false,
                                          $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Search_AddSelector

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    07/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Init remove selector
     */
    private static function Init_Search_RemoveSelector($search,$jsModule,$level) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise Options Selector
            $options = array();
            $options['class']       = 'FindFSCompanies_WithParent';
            $options['name']        = 'scompanies';
            $options['multiselect'] = true;

            // Connect Selector User
            $hash                           = md5(serialize($options));
            $USER->search_selectors[$hash]  = $options;

            $PAGE->requires->js_init_call('M.core_user.init_search_selector',
                                          array('scompanies',$hash, $level, $search),
                                          false,
                                          $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_RemoveSelector

    /**
     * @param           $search
     * @param           $jsModule
     *
     * @throws          Exception
     *
     * @creationDate    18/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize add jobrole selector
     */
    private static function Init_Search_AddJobrole($search,$jsModule) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise Options Selector
            $options = array();
            $options['class']       = 'FindFSJobroles_NO_Mapped';
            $options['name']        = 'ajobroles';
            $options['multiselect'] = true;

            // Connect Selector User
            $hash                           = md5(serialize($options));
            $USER->search_selectors[$hash]  = $options;

            $PAGE->requires->js_init_call('M.core_user.init_search_jobrole',
                                          array('ajobroles','ks_jobrole',$hash, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Search_AddJobrole

    /**
     * @param           $search
     * @param           $jsModule
     *
     * @throws          Exception
     *
     * @creationDate    18/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Iniatialize remove jobrole selector
     */
    private static function Init_Search_RemoveJobrole($search,$jsModule) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise Options Selector
            $options = array();
            $options['class']       = 'FindFSJobroles_Mapped';
            $options['name']        = 'sjobroles';
            $options['multiselect'] = true;

            // Connect Selector User
            $hash                           = md5(serialize($options));
            $USER->search_selectors[$hash]  = $options;

            $PAGE->requires->js_init_call('M.core_user.init_search_jobrole',
                                          array('sjobroles','ks_jobrole',$hash, $search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_RemoveSelector
}//FS_MAPPING