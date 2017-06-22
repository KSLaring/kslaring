<?php
/**
 * Fellesdata Integration Mapping - Library
 *
 * @package         local/fellesdata
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
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
     * @param           $parent
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Companies connected with the parent
     */
    public static function FindFSCompanies_WithParent($level,$search,$parent) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sqlExtra       = null;
        $extra          = null;
        $locate         = null;
        $params         = null;
        $fsCompanies    = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['level']        = $level;
            $params['parent']       = $parent;
            $params['new']          = 1;
            $params['synchronized'] = 0;

            /* SQL Instruction */
            $sql = " SELECT	fs.id,
                            fs.name
                     FROM	{fs_company} fs
                     WHERE	fs.parent       = :parent
                        AND fs.parent       != 0
                        AND	fs.level 		= :level
                        AND fs.new 			= :new
                        AND fs.synchronized = :synchronized ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " LOCATE('" . $str . "',fs.name) > 0";
                }//if_search_opt

                $sql .= $sqlExtra . " AND ($locate) ";
            }//if_search

            /* Execute */
            $sql .= " ORDER By fs.name ";

            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $fsCompanies[$instance->id] = $instance->name;
                }
            }//if_Rdo

            return $fsCompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cathc
    }//FindFSCompanies_WithParent

    /**
     * @param           $level
     * @param           $search
     * @param           $parent
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * All companies without parents
     */
    public static function FindFSCompanies_WithoutParent($level,$search,$parent=0) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sqlExtra       = null;
        $extra          = null;
        $locate         = null;
        $params         = null;
        $fsCompanies    = array();
        $granpaName     = null;
        $name           = null;
        $plugin         = null;
        $granpalevel    = null;

        try {
            // Plugin info
            $plugin     = get_config('local_fellesdata');

            // Search criteria
            $params = array();
            $params['level']        = $level;
            $params['new']          = 1;
            $params['synchronized'] = 0;

            // Level of the parent
            switch ($level) {
                case FS_LE_2:
                    $granpalevel     = $plugin->map_one;

                    break;

                case FS_LE_5;
                    $granpalevel     = $plugin->map_two;

                    break;

                default:
                    $granpalevel = '0';

                    break;
            }//level

            // SQL Instruction
            $sql = " SELECT	      fs.id,
                                  fs.fs_parent,
                                  fs.name,
                                  fs_granpa.ORG_NIVAA 		as 'parentnivaa',
                                  fs_granpa.ORG_ENHET_OVER	as 'parentparent',
                                  fs_granpa.ORG_NAVN		as 'parentname'
                     FROM		  {fs_company} 		fs
                        LEFT JOIN {fs_imp_company}	fs_granpa	ON fs_granpa.org_enhet_id 	= fs.fs_parent
                     WHERE	(fs.parent IS NULL
                             OR
                             fs.parent = 0)
                        AND	fs.level 		= :level
                        AND fs.new 			= :new
                        AND fs.synchronized = :synchronized ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= " OR ";
                    }
                    $locate .= " LOCATE('" . $str . "',fs.name) > 0";
                }//if_search_opt

                $sql .= $sqlExtra . " AND ($locate) ";
            }//if_search

            /* Execute */
            $sql .= " ORDER By fs.name ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if ($instance->fs_parent) {
                        if ($granpalevel == $instance->parentnivaa) {
                            $name = $instance->parentname . ' > ' . $instance->name ;
                        }else {
                            if ($instance->parentparent) {
                                $granpaName = self::GetGranparentName($instance->parentparent,$granpalevel);
                                if ($granpaName) {
                                    $name = $granpaName . ' > ' . $instance->name;
                                }
                            }//if_parentparent
                        }
                    }else {
                        $name = $instance->name;
                    }//if_org_enhet_over

                    $fsCompanies[$instance->id] = $name;
                }
            }//if_Rdo

            return $fsCompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindFSCompanies_WithoutParent

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
            $lstParents[0] = get_string('sel_parent','local_fellesdata');
            /* Search Criteria  */
            $params          = array();
            $params['hierarchylevel'] =  ($level - 1);

            if ($level == FS_LE_2) {
                /* Plugin Info      */
                $pluginInfo     = get_config('local_fellesdata');
                $params['name'] = $pluginInfo->ks_muni;
            }//if_FS_LE_2

            /* Execute */
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
            /* Search criteria */
            $params = array();
            $params['job_role'] = $ks_jobrole;
            $params['action']   = ACT_DELETE;

            /* SQL Instruction  */
            $sql = " SELECT 	    fs.stillingskode,
                                    fs.stillingstekst
                     FROM			{fs_imp_jobroles}	fs
                        LEFT JOIN	{ksfs_jobroles}		ksfs	ON 	ksfs.fsjobrole = fs.stillingskode
                                                                AND	ksfs.ksjobrole = :job_role
                     WHERE          fs.imported = 0	
                          AND       fs.action != :action
                          AND       ksfs.id IS NULL ";

            /* Search   */
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

            /* Execute */
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
            /* Search criteria */
            $params = array();
            $params['job_role'] = $ks_jobrole;

            /* SQL Instruction  */
            $sql = " SELECT   fs.jrcode,
                              fs.jrname
                     FROM	      {fs_jobroles}		fs
                        JOIN      {ksfs_jobroles}	ksfs	ON 	ksfs.fsjobrole  = fs.jrcode
                                                            AND	ksfs.ksjobrole  = :job_role ";

            /* Search   */
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

            /* Execute */
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
            $lstParents[0] = get_string('sel_parent','local_fellesdata');

            /* Execute */
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
            /* SQL Instruction  */
            $sql = "SELECT MAX(hierarchylevel) as max
                    FROM    {ks_company} ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                //for ($i=0;$i<=$rdo->max;$i++) {
                //    $lstLevels[$i] = $i;
                //}
            }

            /* Temporary for L5 */
            $lstLevels[0] = get_string('sel_parent','local_fellesdata');
            $lstLevels[1] = 1;
            $lstLevels[2] = 2;
            $lstLevels[3] = 3;

            return $lstLevels;
        }catch (Exception $ex) {
            throw $ex;
        }//
    }//getLevelsMapping

    /**
     * @param           $level
     * @param           $sector
     * @param           $notIn
     * @param           $start
     * @param           $length
     *
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies to map
     */
    public static function FSCompaniesToMap($level,$sector,$notIn,$start,$length) {
        /* Variables    */
        $fsCompanies    = null;
        $total          = null;
        $plugin         = null;

        try {
            /* Plugin Info      */
            $plugin     = get_config('local_fellesdata');

            /* Get Companies to Map */
            $fsCompanies = self::GetFSCompaniesToMap($plugin,$level,$sector,$notIn,$start,$length);
            /* Get Total    */
            $total = self::GetTotalFSCompaniesToMap($plugin,$level,$sector,$notIn);

            return array($fsCompanies,$total);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FSCompaniesToMap

    /**
     * @param           $toMap
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
    public static function MappingFSCompanies($toMap,$data) {
        /* Variables */
        global $SESSION;
        $possibleMatch  = null;
        $refFS          = null;
        $infoMatch      = null;
        $match          = null;
        $notIn          = array();

        try {
            /* Check Not In */
            if (isset($SESSION->notIn)) {
                $notIn = $SESSION->notIn;
            }//notIn

            /* Companies to map */
            foreach ($toMap as $fsCompany) {
                /* Reference    */
                $refFS = 'FS_' . $fsCompany->fscompany;

                /* Get Possible Match   */
                if (isset($data->$refFS)) {
                    $possibleMatch = $data->$refFS;
                    if ($possibleMatch == 0) {
                        self::NewMapFSCompany($fsCompany,$data->le);
                    }else if ($possibleMatch == 'no_sure') {
                        $notIn["'" . $fsCompany->fscompany . "'"] = "'" . $fsCompany->fscompany . "'";
                    }else {
                        /* Mapping between FSand KS */
                        $infoMatch = explode('#KS#',$data->$refFS);
                        $match = $fsCompany->matches[$infoMatch[1]];
                        self::MapFSCompany($fsCompany,$match,$data->le);
                    }//if_possibleMatch
                }
            }//fs_company

            return array(true,$notIn);
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
            /* Criteria */
            $params = array();
            $params['imported'] = 1;
            $params['deleted']  = ACT_DELETE;

            /* SQL Instruction  */
            $sql = " DELETE FROM {fs_imp_company}
                     WHERE  imported = :imported
                        AND action != :deleted ";
            /* Execute  */
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
            /* Get action   */
            switch ($action) {
                case MAP:
                    $function = "MapFSJobRole";

                    break;
                case UNMAP:
                    $function = "UnMapFSJobRole";

                    break;
            }

            /* Map/UnMap FS Job Roles */
            foreach ($toMap as $fs) {
                /* Get Info FS Job role */
                $rdo = $DB->get_record('fs_imp_jobroles',array('STILLINGSKODE' => $fs));
                /* Mapping */
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
            /* Criteria */
            $params = array();
            $params['imported'] = 1;
            $params['deleted']  = ACT_DELETE;

            /* SQL Instruction  */
            $sql = " DELETE FROM {fs_imp_jobroles}
                     WHERE  imported = :imported
                        AND action != :deleted ";
            /* Execute  */
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
     * @param           $fsCompany
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Map a FS Company with the 'new' option
     */
    private static function NewMapFSCompany($fsCompany,$level) {
        /* Variables    */
        global $DB,$SESSION;
        $rdo            = null;
        $params         = null;
        $infoCompany    = null;
        $infoImp        = null;
        $trans          = null;
        $time           = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();
            
            /* Check if already exist */
            $params = array();
            $params['companyid'] = $fsCompany->fscompany;
            $rdo = $DB->get_record('fs_company',$params);

            if (!$rdo) {
                /* Create Company   */
                $infoCompany = new stdClass();
                $infoCompany->companyid     = $fsCompany->fscompany;
                $infoCompany->name          = $fsCompany->real_name;
                $infoCompany->fs_parent     = ($fsCompany->fs_parent ? $fsCompany->fs_parent : 0);
                $infoCompany->parent        = 0;
                $infoCompany->level         = $level;
                $infoCompany->privat        = 0;
                /* Invoice Data */
                $infoCompany->ansvar        = $fsCompany->ansvar;
                $infoCompany->tjeneste      = $fsCompany->tjeneste;
                $infoCompany->adresse1      = $fsCompany->adresse1;
                $infoCompany->adresse2      = $fsCompany->adresse2;
                $infoCompany->adresse3      = $fsCompany->adresse3;
                $infoCompany->postnr        = $fsCompany->postnr;
                $infoCompany->poststed      = $fsCompany->poststed;
                $infoCompany->epost         = $fsCompany->epost;
                $infoCompany->synchronized  = 0;
                $infoCompany->new           = 1;
                $infoCompany->timemodified  = time();

                /* Execute  */
                $infoCompany->id = $DB->insert_record('fs_company',$infoCompany);

                /* Save */
                $SESSION->FS_COMP["'" . $infoCompany->companyid . "'"] = $infoCompany;
            }//if_rdo

            /* Update Record as imported    */
            $infoImp = new stdClass();
            $infoImp->id            = $fsCompany->id;
            $infoImp->org_enhet_id  = $fsCompany->fscompany;
            $infoImp->imported      = 1;
            $infoImp->timemodified  = $time;
            $DB->update_record('fs_imp_company',$infoImp);

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//NewMapFSCompany

    /**
     * @param           $fsCompany
     * @param           $ksCompany
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mapping between FS and KS company
     */
    private static function MapFSCompany($fsCompany,$ksCompany,$level) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $infoCompany    = null;
        $infoRelation   = null;
        $infoImp        = null;
        $time           = null;
        $trans          = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Check if already exist */
            $params = array();
            $params['companyid'] = $fsCompany->fscompany;
            $rdo = $DB->get_record('fs_company',$params);

            if (!$rdo) {
                /* FS Company   */
                $infoCompany = new stdClass();
                $infoCompany->companyid     = $fsCompany->fscompany;
                $infoCompany->name          = $fsCompany->real_name;
                $infoCompany->fs_parent     = ($fsCompany->fs_parent ? $fsCompany->fs_parent : 0);
                $infoCompany->parent        = $ksCompany->parent;
                /* Invoice Data */
                $infoCompany->privat        = 0;
                $infoCompany->ansvar        = $fsCompany->ansvar;
                $infoCompany->tjeneste      = $fsCompany->tjeneste;
                $infoCompany->adresse1      = $fsCompany->adresse1;
                $infoCompany->adresse2      = $fsCompany->adresse2;
                $infoCompany->adresse3      = $fsCompany->adresse3;
                $infoCompany->postnr        = $fsCompany->postnr;
                $infoCompany->poststed      = $fsCompany->poststed;
                $infoCompany->epost         = $fsCompany->epost;
                $infoCompany->level         = $level;
                $infoCompany->synchronized  = 1;
                $infoCompany->new           = 0;
                $infoCompany->timemodified  = $time;

                /* Execute  */
                $DB->insert_record('fs_company',$infoCompany);
            }else {
                $rdo->name          = $fsCompany->name;
                $rdo->fs_parent     = $fsCompany->fs_parent;
                $rdo->parent        = $ksCompany->parent;
                $rdo->level         = $level;
                $rdo->synchronized  = 1;
                $rdo->timemodified  = $time;

                /* Execute  */
                $DB->update_record('fs_company',$rdo);
            }//if_rdo

            /* Relation */
            /* Check if already exist   */
            $params = array();
            $params['fscompany'] = $fsCompany->fscompany;
            $params['kscompany'] = $ksCompany->kscompany;
            $rdo = $DB->get_record('ksfs_company',$params);
            if (!$rdo) {
                /* Create Relation  */
                $infoRelation = new stdClass();
                $infoRelation->fscompany = $fsCompany->fscompany;
                $infoRelation->kscompany = $ksCompany->kscompany;

                /* Execute  */
                $DB->insert_record('ksfs_company',$infoRelation);
            }//if_no_exists

            /* Update Record as imported    */
            $infoImp = new stdClass();
            $infoImp->id            = $fsCompany->id;
            $infoImp->org_enhet_id  = $fsCompany->fscompany;
            $infoImp->imported      = 1;
            $infoImp->timemodified  = $time;
            /* Executes */
            $DB->update_record('fs_imp_company',$infoImp);

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//MapFSCompany


    /**
     * @param    Object $plugin     Plugin info. Settings
     * @param           $level
     * @param           $sector
     * @param           $notIn
     * @param           $start
     * @param           $length
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies to map
     */
    private static function GetFSCompaniesToMap($plugin,$level,$sector,$notIn,$start,$length) {
        /* Variables    */
        global $DB;
        $granpa         = false;
        $granpaName     = null;
        $fsCompanies    = array();
        $infoCompany    = null;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $granpalevel    = null;

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
                    $granpalevel     = $plugin->map_one;
                    $granpa = true;

                    break;

                case FS_LE_5;
                    $params['level'] = $plugin->map_three;
                    $granpalevel     = $plugin->map_two;
                    $granpa = true;

                    break;

                default:
                    $params['level'] = '-1';

                    break;
            }//level

            // SQL Instruction
            $sql = " SELECT DISTINCT 
                                  fs_imp.id,
                                  fs_imp.org_enhet_id   		as 'fscompany',
                                  fs_imp.org_nivaa,
                                  fs_imp.org_navn	    		as 'name',
                                  fs_imp.org_enhet_over,
                                  fs_imp.privat,
                                  fs_imp.ansvar,
                                  fs_imp.tjeneste,
                                  fs_imp.adresse1,
                                  fs_imp.adresse2,
                                  fs_imp.adresse3,
                                  fs_imp.postnr,
                                  fs_imp.poststed,
                                  fs_imp.epost,
                                  fs_granpa.ORG_NIVAA 		as 'parentnivaa',
                                  fs_granpa.ORG_ENHET_OVER	as 'parentparent',
                                  fs_granpa.ORG_NAVN			as 'parentname'
                     FROM		  {fs_imp_company}  fs_imp
                        LEFT JOIN {fs_company}	    fs	  		ON fs.companyid 			= fs_imp.org_enhet_id
                        -- Granparent information
                        LEFT JOIN {fs_imp_company}	fs_granpa	ON fs_granpa.org_enhet_id 	= fs_imp.org_enhet_over
                     WHERE	      fs_imp.imported  = :imported
                          AND     fs_imp.action   != :action
                          AND	  fs.id IS NULL
                          AND	  fs_imp.org_nivaa = :level ";

            // Add notIn criteria
            if ($notIn) {
                $sql .= " AND fs_imp.org_enhet_id NOT IN ($notIn) ";
            }//if_notIn

            if ($sector) {
                $sqlMatch = null;
                $searchBy = null;
                /* Search By    */
                $sector     = str_replace(',',' ',$sector);
                $sector     = str_replace(' og ',' ',$sector);
                $sector     = str_replace(' eller ',' ',$sector);
                $sector     = str_replace('/',' ',$sector);
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
                foreach ($rdo as $instance) {
                    // Info company
                    $infoCompany = new stdClass();
                    $infoCompany->id            = $instance->id;
                    $infoCompany->fscompany     = $instance->fscompany;
                    $infoCompany->nivaa         = $instance->org_nivaa;
                    $infoCompany->name          = $instance->name;
                    $infoCompany->real_name     = $instance->name;
                    // Granparent name
                    if ($granpa) {
                        if ($instance->org_enhet_over) {
                            if ($granpalevel == $instance->parentnivaa) {
                                $infoCompany->name = $instance->parentname . ' > ' . $infoCompany->name;
                            }else {
                                if ($instance->parentparent) {
                                    $granpaName = self::GetGranparentName($instance->parentparent,$granpalevel);
                                    if ($granpaName) {
                                        $infoCompany->name = $granpaName . ' > ' . $infoCompany->name;
                                    }
                                }//if_parentparent
                            }
                        }
                    }//if_ganpa

                    $infoCompany->fs_parent     = $instance->org_enhet_over;
                    // Invoice data
                    $infoCompany->privat        = $instance->privat;
                    $infoCompany->ansvar        = $instance->ansvar;
                    $infoCompany->tjeneste      = $instance->tjeneste;
                    $infoCompany->adresse1      = $instance->adresse1;
                    $infoCompany->adresse2      = $instance->adresse2;
                    $infoCompany->adresse3      = $instance->adresse3;
                    $infoCompany->postnr        = $instance->postnr;
                    $infoCompany->poststed      = $instance->poststed;
                    $infoCompany->epost         = $instance->epost;
                    $infoCompany->matches       = self::GetPossibleOrgMatches($instance->name,$level,$sector);

                    // Add FS Company
                    $fsCompanies[$instance->id] = $infoCompany;
                }//for_Rdo
            }//if_rdo

            return $fsCompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFSCompaniesToMap

    /**
     * @param           $parent
     * @param           $granpalevel
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    11/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the granparent name
     */
    private static function GetGranparentName($parent,$granpalevel) {
        /* Variables */
        $name    = null;
        $grandpa = null;

        try {
            // Get granpa object
            self::get_parent($parent,$granpalevel,$grandpa);

            if ($grandpa) {
                $name = $grandpa->name;
            }

            return $name;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetGranparentName

    /**
     * Description
     * Get parent object connected with
     * @param           $parent
     * @param           $parentlevel
     * @param           $grandpa
     *
     * @throws          Exception
     *
     * @creationDate    20/04/17
     * @author          eFaktor     (fbv)
     */
    private static function get_parent($parent,$parentlevel,&$grandpa) {
        /* Variables */
        global $DB;
        $granpalevel = null;
        $granpa      = null;
        $sql         = null;
        $rdo         = null;
        $params      = null;

        try {
            // Search criteria
            $params = array();
            $params['parent'] = $parent;

            // SQL Instruction
            $sql = " SELECT       fs_imp.ORG_NIVAA 			as 'level',
                                  fs_imp.ORG_NAVN			as 'name',
                                  fs_imp.ORG_ENHET_OVER		as 'fs_parent'
                     FROM	      {fs_imp_company}	fs_imp
                     WHERE	      fs_imp.org_enhet_id	= :parent ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($parentlevel != $rdo->level) {
                    self::get_parent($rdo->fs_parent,$parentlevel,$grandpa);
                }else {
                    $grandpa = $rdo;
                }
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_parent


    /**
     * @param       Object  $plugin
     * @param               $level
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
    private static function GetTotalFSCompaniesToMap($plugin,$level,$sector,$notIn) {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;
            $params['action']   = ACT_DELETE;

            /* Get Level    */
            switch ($level) {
                case FS_LE_2:
                    $params['level'] = $plugin->map_two;;
                    break;
                case FS_LE_5;
                    $params['level'] = $plugin->map_three;
                    break;
                default:
                    $params['level'] = '-1';
                    break;
            }//level

            /* SQL Instruction  */
            $sql = " SELECT count(DISTINCT  fs_imp.id) as 'total'
                     FROM			{fs_imp_company}  fs_imp
                        LEFT JOIN	{fs_company}	  fs	  ON fs.companyid = fs_imp.org_enhet_id
                     WHERE	fs_imp.imported  = :imported
                        AND fs_imp.action   != :action
                        AND	fs.id IS NULL
                        AND	fs_imp.org_nivaa = :level ";

            // Add notIn criteria
            if ($notIn) {
                echo "NOT IN : " . $notIn . "</br>";
                $sql .= " AND fs_imp.org_enhet_id NOT IN ($notIn) ";
            }//if_notIn

            /* Sector */
            if ($sector) {
                $sqlMatch = null;
                $searchBy = null;
                /* Search By    */
                $sector     = str_replace(',',' ',$sector);
                $sector     = str_replace(' og ',' ',$sector);
                $sector     = str_replace(' eller ',' ',$sector);
                $sector     = str_replace('/',' ',$sector);
                $searchBy   = explode(' ',$sector);

                foreach($searchBy as $match) {
                    if ($sqlMatch) {
                        $sqlMatch .= " OR ";
                    }//if_sqlMatch

                    $sqlMatch .= " fs_imp.org_navn like '%" . $match . "%' ";
                }//for_search

                $sql .= " AND (fs_imp.org_navn like '%" . $sector . "%' OR " . $sqlMatch . ")";
            }//if_patterns

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                echo $sql . "</br>";
                echo "TOTAL FROM SQL:  " . $rdo->total . "</br>";
                return $rdo->total;
            }else {
                return 0;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTotalFSCompaniesToMap

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
    private static function GetPossibleOrgMatches($fscompany,$level,$sector) {
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
            /* Search Criteria  */
            $params = array();
            $params['level'] = $level;

            /* SQL Instruction  */
            $sql = " SELECT	ks.id,
                            ks.companyid as 'kscompany',
                            ks.name,
                            ks.industrycode,
                            ks.parent
                    FROM	{ks_company} ks
                    WHERE 	ks.hierarchylevel = :level ";

            /* Pattern  */
            if ($sector) {
                $sector     = str_replace(',',' ',$sector);
                $sector     = str_replace(' og ',' ',$sector);
                $sector     = str_replace(' eller ',' ',$sector);
                $sector     = str_replace('/',' ',$sector);
                $searchBy   = explode(' ',$sector);

                /* Search by */
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

            /* Execute  */
            $sql .= " ORDER BY ks.industrycode, ks.name ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Match   */
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->kscompany   = $instance->kscompany;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->industry    = $instance->industrycode;
                    $infoMatch->parent      = $instance->parent;

                    /* Add Match    */
                    $matches[$instance->kscompany] = $infoMatch;
                }//for_Rdo
            }//if_rdo

            return $matches;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetPossibleOrgMatches


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
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindFSCompanies_WithoutParent';
            $options['name']        = 'acompanies';
            $options['multiselect'] = true;

            /* Connect Selector User    */
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
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindFSCompanies_WithParent';
            $options['name']        = 'scompanies';
            $options['multiselect'] = true;

            /* Connect Selector User    */
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
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindFSJobroles_NO_Mapped';
            $options['name']        = 'ajobroles';
            $options['multiselect'] = true;


            /* Connect Selector User    */
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
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindFSJobroles_Mapped';
            $options['name']        = 'sjobroles';
            $options['multiselect'] = true;

            /* Connect Selector User    */
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