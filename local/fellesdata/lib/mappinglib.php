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
                    $toDelete[$instance->id] = $instance->companyid;
                }//for_rdo
            }//if_rdo

            return $toDelete;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetNewCompaniesToDelete

    /**
     * @param           $fsCompanies
     * @throws          Exception
     *
     * @creationDate    09/06/2016
     * @author          author      (fbv)
     *
     * Description
     * Clean the new companies
     */
    public static function CleanNewCompanies($fsCompanies) {
        /* Variables */
        global $DB;
        $in     = null;

        try {
            /* SQL Instruction */
            $in = implode(',',array_keys($fsCompanies));
            $sql = "DELETE FROM {fs_company}
                    WHERE id IN ($in) ";

            /* Execute */
            $DB->execute($sql);

            /* Update Imported 0 */
            $in = implode(',',$fsCompanies);
            $sql = " UPDATE {fs_imp_company}
                      SET imported = 0
                     WHERE org_enhet_id IN ($in) ";

            /* Execute */
            $DB->execute($sql);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanNewCompanies

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


        try {
            /* Search Criteria  */
            $params = array();
            $params['level']        = $level;
            $params['new']          = 1;
            $params['synchronized'] = 0;

            /* SQL Instruction */
            $sql = " SELECT	fs.id,
                            fs.name
                     FROM	{fs_company} fs
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
                    $fsCompanies[$instance->id] = $instance->name;
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

        try {
            /* Get Companies to Map */
            $fsCompanies = self::GetFSCompaniesToMap($level,$sector,$notIn,$start,$length);
            /* Get Total    */
            $total = self::GetTotalFSCompaniesToMap($level,$sector,$notIn);

            return array($fsCompanies,$total);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FSCompaniesToMap

    /**
     * @param           $toMap
     * @param           $data
     *
     * @return          bool
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
                    if ($possibleMatch) {
                        if ($possibleMatch == 'new') {
                            self::NewMapFSCompany($fsCompany,$data->le);
                        }else if ($possibleMatch == 'no_sure') {
                            $notIn[$fsCompany->fscompany] = $fsCompany->fscompany;
                        }else {
                            /* Mapping between FSand KS */
                            $infoMatch = explode('#KS#',$data->$refFS);
                            $match = $fsCompany->matches[$infoMatch[1]];
                            self::MapFSCompany($fsCompany,$match,$data->le);
                        }//if_possible:matches
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
     * @param           $sector
     * @param           $generic
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
     * Job roles to map
     */
    public static function FSJobRolesToMap($sector,$generic,$notIn,$start,$length) {
        /* Variables    */
        $fsJobRoles = null;
        $total      = 0;
        try {
            /* Get Job Roles to map */
            $fsJobRoles = self::GetFSJobRolesToMap($sector,$generic,$notIn,$start,$length);
            /* Get how many remains to map  */
            $total = self::GetTotalFSJobRolesToMap($sector,$notIn);

            return array($fsJobRoles,$total);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FSJobRolesToMap

    /**
     * @param           $toMap
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Mapping Job Roles
     */
    public static function MappingFSJobRoles($toMap,$data) {
        /* Variables    */
        global $SESSION;
        $notIn          = array();
        $possibleMatch  = null;
        $refFS          = null;
        $infoMatch      = null;

        try {
            /* Check Not In */
            if (isset($SESSION->notIn)) {
                $notIn = $SESSION->notIn;
            }//notIn

            /* Job roles to map */
            foreach ($toMap as $fsJR) {
                /* Reference    */
                $refFS = "FS_" . $fsJR->fsjobrole;

                /* Get Possible Match   */
                $possibleMatch = $data->$refFS;
                if ($possibleMatch) {
                    /* Mapping between FS and KS */
                    $infoMatch = explode('#KS#',$data->$refFS);
                    self::MapFSJobRole($fsJR,$infoMatch[1]);
                }else {
                    $notIn[$fsJR->fsjobrole] = $fsJR->fsjobrole;
                }//if_possibleMatch
            }//fs_company

            return array(true,$notIn);
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

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Check if already exist */
            $params = array();
            $params['companyid'] = $fsCompany->fscompany;
            $rdo = $DB->get_record('fs_company',$params);

            if (!$rdo) {
                /* Create Company   */
                $infoCompany = new stdClass();
                $infoCompany->companyid     = $fsCompany->fscompany;
                if (strpos($fsCompany->name,'>')) {

                }
                $infoCompany->name          = $fsCompany->real_name;
                $infoCompany->fs_parent     = $fsCompany->fs_parent;
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
                $SESSION->FS_COMP[$infoCompany->companyid] = $infoCompany;
            }//if_rdo

            /* Update Record as imported    */
            $infoImp = new stdClass();
            $infoImp->id            = $fsCompany->id;
            $infoImp->org_enhet_id  = $fsCompany->fscompany;
            $infoImp->imported      = 1;
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
                $infoCompany->fs_parent     = $fsCompany->fs_parent;
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
    private static function GetFSCompaniesToMap($level,$sector,$notIn,$start,$length) {
        /* Variables    */
        global $DB;
        $granpa         = false;
        $granpaName     = null;
        $fsCompanies    = array();
        $infoCompany    = null;
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
                    $params['level'] = 2;

                    break;
                case FS_LE_5;
                    $params['level'] = 5;
                    $granpa = true;

                    break;
                default:
                    $params['level'] = '-1';

                    break;
            }//level

            /* SQL Instruction  */
            $sql = " SELECT DISTINCT fs_imp.id,
                                     fs_imp.org_enhet_id    as 'fscompany',
                                     fs_imp.org_navn	    as 'name',
                                     fs_imp.org_enhet_over,
                                     fs_imp.privat,
                                     fs_imp.ansvar,
                                     fs_imp.tjeneste,
                                     fs_imp.adresse1,
                                     fs_imp.adresse2,
                                     fs_imp.adresse3,
                                     fs_imp.postnr,
                                     fs_imp.poststed,
                                     fs_imp.epost
                     FROM			{fs_imp_company}  fs_imp
                        LEFT JOIN	{fs_company}	  fs	  ON fs.companyid = fs_imp.org_enhet_id
                     WHERE	fs_imp.imported  = :imported
                        AND fs_imp.action   != :action
                        AND	fs.id IS NULL
                        AND	fs_imp.org_nivaa = :level
                        AND fs_imp.org_enhet_id NOT IN ($notIn) ";

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
            }else {
                $sql .= " AND fs_imp.org_navn like '%" . $sector . "%' ";
            }

            /* Order Criteria   */
            $sql .= " ORDER BY fs_imp.org_navn
                      LIMIT $start, $length ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Company */
                    $infoCompany = new stdClass();
                    $infoCompany->id            = $instance->id;
                    $infoCompany->fscompany     = $instance->fscompany;
                    $infoCompany->name          = $instance->name;
                    $infoCompany->real_name     = $instance->name;
                    /* Get Name Granpa */
                    if ($granpa) {
                        $granpaName = self::GetGranparentName($instance->org_enhet_over);
                        if ($granpaName) {
                            $infoCompany->name = $granpaName . ' > ' . $infoCompany->name ;
                        }
                    }//if_ganpa

                    $infoCompany->fs_parent     = $instance->org_enhet_over;
                    /* Invoice Data */
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

                    /* Add FS Company   */
                    $fsCompanies[$instance->id] = $infoCompany;
                }//for_Rdo
            }//if_rdo

            return $fsCompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFSCompaniesToMap

    /**
     * @param           $parentId
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
    private static function GetGranparentName($parentId) {
        /* Variables */
        global $DB;
        $name   = null;
        $sql    = null;
        $rdo    = null;
        $params = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['parent'] = $parentId;

            /* SQL Instruction  */
            $sql = " SELECT IF(fs_granpa.org_navn,fs_granpa.org_navn,fs_imp.org_navn)		as 'granpa'
                     FROM			{fs_imp_company}  fs_imp
                        LEFT JOIN	{fs_imp_company}	fs_granpa	ON fs_granpa.org_enhet_id = fs_imp.org_enhet_over
                    WHERE	fs_imp.org_enhet_id = :parent ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $name = $rdo->granpa;
            }//IF_RDO

            return $name;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetGranparentName

    /**
     * @param           $level
     * @param           $sector
     * @param           $notIn
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    09/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total companies to map
     */
    private static function GetTotalFSCompaniesToMap($level,$sector,$notIn) {
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
                    $params['level'] = 2;
                    break;
                case FS_LE_5;
                    $params['level'] = 5;
                    break;
                default:
                    $params['level'] = '-1';
                    break;
            }//level

            /* SQL Instruction  */
            $sql = " SELECT DISTINCT count(fs_imp.id) as 'total'
                     FROM			{fs_imp_company}  fs_imp
                        LEFT JOIN	{fs_company}	  fs	  ON fs.companyid = fs_imp.org_enhet_id
                     WHERE	fs_imp.imported  = :imported
                        AND fs_imp.action   != :action
                        AND	fs.id IS NULL
                        AND	fs_imp.org_nivaa = :level
                        AND fs_imp.org_enhet_id NOT IN ($notIn) ";

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
            }else {
                $sql .= " AND fs_imp.org_navn like '%" . $sector . "%' ";
            }//if_patterns

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
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
                $sql .= " AND ks.name like '%" . $fscompany . "%'";
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
     * @param           $sector
     * @param           $generic
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
     * GEt job roles to map
     */
    private static function GetFSJobRolesToMap($sector,$generic,$notIn,$start,$length) {
        /* Variables */
        global $DB;
        $fsJobRoles = array();
        $infoJR     = null;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;
            $params['action']   = ACT_DELETE;

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT  fs_imp.id,
                                      fs_imp.stillingskode as 'fsjobrole',
                                      fs_imp.stillingstekst,
                                      fs_imp.stillingstekst_alternativ
                     FROM			{fs_imp_jobroles}	fs_imp
                        LEFT JOIN	{fs_jobroles} 	    fs		ON fs.jrcode = fs_imp.stillingskode
                     WHERE	fs_imp.imported = :imported
                        AND fs_imp.action  != :action
                        AND fs_imp.stillingskode NOT IN ($notIn)
                        AND	fs.id IS NULL ";


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

                    $sqlMatch .= " (fs_imp.stillingstekst like '%" . $match . "%'
                                    OR
                                    fs_imp.stillingstekst_alternativ like '%" . $match . "%')";
                }//for_search

                $sql .= " AND " . $sqlMatch . "";
            }else {
                $sql .= " AND (fs_imp.stillingstekst like '%" . $sector . "%' OR fs_imp.stillingstekst_alternativ like '%" . $sector . "%') ";
            }

            /* Order Criteria  */
            $sql .= " ORDER BY fs_imp.stillingstekst
                      LIMIT $start, $length  ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Job Role */
                    $infoJR = new stdClass();
                    $infoJR->id             = $instance->id;
                    $infoJR->fsjobrole      = $instance->fsjobrole;
                    $infoJR->name           = $instance->fsjobrole . ' - ' . $instance->stillingstekst;
                    $infoJR->alternative    = $instance->stillingstekst_alternativ;
                    $infoJR->matches        = self::GetPossiblesJRMatches($infoJR->name,$sector,$generic);

                    /* Add Job Role */
                    $fsJobRoles[$instance->fsjobrole] = $infoJR;
                }//for_rdo
            }//if_Rdo

            return $fsJobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFSJobRolesToMap

    /**
     * @param           $sector
     * @param           $notIn
     *
     * @return          int
     * @throws          Exception
     *
     * Description
     * gets how main remains to map
     */
    private static function GetTotalFSJobRolesToMap($sector,$notIn) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;
            $params['action']   = ACT_DELETE;

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT  count(fs_imp.id) as 'total'
                     FROM			{fs_imp_jobroles}	fs_imp
                        LEFT JOIN	{fs_jobroles} 	    fs		ON fs.jrcode = fs_imp.stillingskode
                     WHERE	fs_imp.imported = :imported
                        AND fs_imp.action  != :action
                        AND fs_imp.stillingskode NOT IN ($notIn)
                        AND	fs.id IS NULL ";

            /* Pattern */
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

                    $sqlMatch .= " (fs_imp.stillingstekst like '%" . $match . "%'
                                    OR
                                    fs_imp.stillingstekst_alternativ like '%" . $match . "%')";
                }//for_search

                $sql .= " AND " . $sqlMatch . "";
            }else {
                $sql .= " AND (fs_imp.stillingstekst like '%" . $sector . "%' OR fs_imp.stillingstekst_alternativ like '%" . $sector . "%') ";
            }//if_sector

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTotalFSJobRolesToMap

    /**
     * @param           $fsJobRole
     * @param           $sector
     * @param           $generic
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get possible matches - Job Roles
     */
    private static function GetPossiblesJRMatches($fsJobRole,$sector,$generic) {
        /* Variables    */
        global $DB;
        $sql        = null;
        $sqlMatch   = null;
        $rdo        = null;
        $searchBy   = null;
        $matches    = array();
        $infoMatch  = null;
        $pluginInfo = null;
        $hierarchy  = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT DISTINCT jr.id,
                                     jr.jobroleid,
                                     jr.name,
                                     jr.industrycode
                      FROM		{ks_jobroles} 			jr
                        JOIN	{ks_jobroles_relation}	jr_rel ON jr_rel.jobroleid = jr.jobroleid ";

            /* Add Level    */
            if ($generic) {
                $sql .= " WHERE (jr_rel.levelzero IS NULL
                                 OR
                                 jr_rel.levelzero = 0)";
            }else {
                $sql .= " WHERE jr_rel.levelzero IS NOT NULL
                            AND jr_rel.levelzero != 0 ";
            }//if_generic


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
                    $sqlMatch .= " jr.name like '%" . $match . "%'";
                }//for_search

                $sql .= " AND (jr.name like '%" . $fsJobRole . "%' OR " . $sqlMatch . ")";
            }else {
                $sql .= " AND jr.name like '%" . $fsJobRole . "%'";
            }//if_sector

            /* Order Criteria  */
            $sql .= " ORDER BY jr.industrycode,jr.name ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Match   */
                    $infoMatch = new stdClass();
                    $infoMatch->id          = $instance->id;
                    $infoMatch->jobrole     = $instance->jobroleid;
                    $infoMatch->name        = $instance->name;
                    $infoMatch->industry    = $instance->industrycode;

                    /* Add Match    */
                    $matches[$instance->jobroleid] = $infoMatch;
                }//for_Rdo
            }//if_rdo

            return $matches;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetPossiblesJRMatches

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
            $params['jrcode'] = $fsJobRole->fsjobrole;
            $rdo = $DB->get_record('fs_jobroles',$params);

            if (!$rdo) {
                /* New Entry    */
                $infoJobRole = new stdClass();
                $infoJobRole->jrcode            = $fsJobRole->fsjobrole;
                $infoJobRole->jrname            = $fsJobRole->name;
                $infoJobRole->jrjralternative   = $fsJobRole->alternative;
                $infoJobRole->synchronized      = 1;
                $infoJobRole->new               = 0;
                $infoJobRole->timemodified      = $time;

                /* Execute  */
                $DB->insert_record('fs_jobroles',$infoJobRole);
            }else {
                $rdo->jrname            = $fsJobRole->name;
                $rdo->jrjralternative   = $fsJobRole->alternative;
                $rdo->synchronized      = 1;
                $rdo->timemodified      = $time;

                /* Execute  */
                $DB->update_record('fs_jobroles',$rdo);
            }//if_else

            /* Relation */
            /* Check if already exists  */
            $params = array();
            $params['fsjobrole'] = $fsJobRole->fsjobrole;
            $params['ksjobrole'] = $ksJobRole;
            $rdo = $DB->get_record('ksfs_jobroles',$params);
            if (!$rdo) {
                /* Create Relation  */
                $infoRelation = new stdClass();
                $infoRelation->fsjobrole = $fsJobRole->fsjobrole;
                $infoRelation->ksjobrole = $ksJobRole;

                /* Execute  */
                $DB->insert_record('ksfs_jobroles',$infoRelation);
            }//if_no_exists

            /* Updated record as imported   */
            $infoImp = new stdClass();
            $infoImp->id            = $fsJobRole->id;
            $infoImp->stillingskode = $fsJobRole->fsjobrole;
            $infoImp->imported      = 1;
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

    /**
     * @param           $fsJR
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Map a FS Job role with the 'new' option
     */
    private static function NewMapFSJobRole($fsJR) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $params         = null;
        $infoJobRole    = null;
        $infoImp        = null;
        $trans          = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Check if already exists  */
            $params = array();
            $params['jrcode'] = $fsJR->fsjobrole;
            $rdo = $DB->get_record('fs_jobroles',$params);

            if (!$rdo) {
                /* New Entry    */
                $infoJobRole = new stdClass();
                $infoJobRole->jrcode            = $fsJR->fsjobrole;
                $infoJobRole->jrname            = $fsJR->name;
                $infoJobRole->jrjralternative   = $fsJR->alternative;
                $infoJobRole->synchronized      = 0;
                $infoJobRole->new               = 1;
                $infoJobRole->timemodified      = time();

                /* Execute  */
                $DB->insert_record('fs_jobroles',$infoJobRole);
            }//if_not_exists

            /* Update record as imported    */
            $infoImp = new stdClass();
            $infoImp->id            = $fsJR->id;
            $infoImp->stillingskode = $fsJR->fsjobrole;
            $infoImp->imported      = 1;
            /* Execute  */
            $DB->update_record('fs_imp_jobroles',$infoImp);

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//NewMapFSJobRole

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
}//FS_MAPPING