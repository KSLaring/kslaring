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
     * @param           $level
     * @param           $sector
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
    public static function FSCompaniesToMap($level,$sector,$start,$length) {
        /* Variables    */
        $fsCompanies = null;

        try {
            /* Get Companies to Map */
            $fsCompanies = self::GetFSCompaniesToMap($level,$sector,$start,$length);

            return $fsCompanies;
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
        $possibleMatch  = null;
        $refFS          = null;
        $infoMatch      = null;
        $match          = null;

        try {
            /* Companies to map */
            foreach ($toMap as $fsCompany) {
                /* Reference    */
                $refFS = 'FS_' . $fsCompany->fscompany;

                /* Get Possible Match   */
                $possibleMatch = $data->$refFS;
                if ($possibleMatch) {
                    if ($possibleMatch == 'new') {
                        self::NewMapFSCompany($fsCompany,$data->le);
                    }else {
                        /* Mapping between FSand KS */
                        $infoMatch = explode('#KS#',$data->$refFS);
                        $match = $fsCompany->matches[$infoMatch[1]];
                        self::MapFSCompany($fsCompany,$match,$data->le);
                    }//if_possible:matches
                }//if_possibleMatch
            }//fs_company

            return true;
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
     * @param           $level
     * @param           $sector
     * @param           $generic
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
    public static function FSJobRolesToMap($level,$sector,$generic,$start,$length) {
        /* Variables    */
        $fsJobRoles = null;

        try {
            /* Get Job Roles to map */
            $fsJobRoles = self::GetFSJobRolesToMap($level,$sector,$generic,$start,$length);

            return $fsJobRoles;
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
        $possibleMatch  = null;
        $refFS          = null;
        $infoMatch      = null;

        try {
            /* Job roles to map */
            foreach ($toMap as $fsJR) {
                /* Reference    */
                $refFS = "FS_" . $fsJR->fsjobrole;

                /* Get Possible Match   */
                $possibleMatch = $data->$refFS;
                if ($possibleMatch) {
                    if ($possibleMatch == 'new') {
                        self::NewMapFSJobRole($fsJR);
                    }else {
                        /* Mapping between FS and KS */
                        $infoMatch = explode('#KS#',$data->$refFS);
                        self::MapFSJobRole($fsJR,$infoMatch[1]);
                    }//if_possible:matches
                }//if_possibleMatch
            }//fs_company

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
        global $DB;
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
                $infoCompany->name          = $fsCompany->name;
                $infoCompany->fs_parent     = $fsCompany->fs_parent;
                $infoCompany->parent        = 0;
                $infoCompany->level         = $level;
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
                $DB->insert_record('fs_company',$infoCompany);
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
                $infoCompany->name          = $fsCompany->name;
                $infoCompany->fs_parent     = $fsCompany->fs_parent;
                $infoCompany->parent        = $ksCompany->parent;
                /* Invoice Data */
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
    private static function GetFSCompaniesToMap($level,$sector,$start,$length) {
        /* Variables    */
        global $DB;
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
                        AND	fs_imp.org_nivaa = :level ";

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
                    $infoCompany->fs_parent     = $instance->org_enhet_over;
                    /* Invoice Data */
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
                    //if ($infoCompany->matches) {
                        $fsCompanies[$instance->fscompany] = $infoCompany;
                    //}//if_matches
                }//for_Rdo
            }//if_rdo

            return $fsCompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFSCompaniesToMap

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

            /* Search By    */
            $fscompany  = str_replace('/',' ',$fscompany);
            $searchBy   = explode(' ',$fscompany);

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


                /* Search by */
                foreach($searchBy as $match) {
                    if ($sqlMatch) {
                        $sqlMatch .= " OR ";
                    }//if_sqlMatch
                    $sqlMatch .= " ks.name like '%" . $match . "%'";
                }//for_search

                $sql .= " AND (ks.name like '%" . $sector . "%' OR " . $sqlMatch . ")";
            }else {
                $sql .= " AND ks.name like '%" . $sector . "%'";
            }//if_sector

            /* Execute  */
            $sql .= " ORDER BY ks.industrycode, ks.name ";
            echo "SQL: " . $sql . "</br>";
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
     * @param           $level
     * @param           $sector
     * @param           $generic
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
    private static function GetFSJobRolesToMap($level,$sector,$generic,$start,$length) {
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
                                      fs_imp.alternative
                     FROM			{fs_imp_jobroles}	fs_imp
                        LEFT JOIN	{fs_jobroles} 	    fs		ON fs.jrcode = fs_imp.stillingskode
                     WHERE	fs_imp.imported = :imported
                        AND fs_imp.action  != :action
                        AND	fs.id IS NULL
                        AND (fs_imp.stillingstekst like '%" . $sector . "%'
                             OR
                             fs_imp.alternative like '%" . $sector . "%'
                             )
                     ORDER BY fs_imp.stillingstekst
                     LIMIT $start, $length ";


            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Job Role */
                    $infoJR = new stdClass();
                    $infoJR->id             = $instance->id;
                    $infoJR->fsjobrole      = $instance->fsjobrole;
                    $infoJR->name           = $instance->stillingstekst;
                    $infoJR->alternative    = $instance->alternative;
                    $infoJR->matches        = self::GetPossiblesJRMatches($infoJR->name,$level,$sector,$generic);

                    /* Add Job Role */
                    if ($infoJR->matches) {
                        $fsJobRoles[$instance->fsjobrole] = $infoJR;
                    }//if_matches
                }//for_rdo
            }//if_Rdo

            return $fsJobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFSJobRolesToMap

    /**
     * @param           $fsJobRole
     * @param           $level
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
    private static function GetPossiblesJRMatches($fsJobRole,$level,$sector,$generic) {
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
                $sql .= " WHERE jr_rel.levelzero IS NULL ";
            }else {
                /* Plugin Info      */
                $pluginInfo     = get_config('local_fellesdata');
                /* Get Top hierarchy for job Roles */
                $hierarchy = KS::GetHierarchy_JR($pluginInfo->ks_muni);

                switch ($level) {
                    case '0':
                        $sql .= " WHERE jr_rel.levelzero IN ($hierarchy) ";

                        break;
                    case '1':
                        $sql .= " WHERE ((jr_rel.levelzero IN ($hierarchy)  AND     jr_rel.levelone IS NOT NULL
                                         AND
                                         jr_rel.leveltwo IS NULL            AND     jr_rel.levelthree IS NULL)
                                        OR
                                        (jr_rel.levelzero IN ($hierarchy)   AND     jr_rel.levelone IS NULL
                                         AND
                                         jr_rel.leveltwo IS NULL            AND     jr_rel.levelthree IS NULL))";

                        break;
                    case '2':
                        $sql .= " WHERE ((jr_rel.levelzero IN ($hierarchy)  AND     jr_rel.levelone IS NOT NULL
                                         AND
                                         jr_rel.leveltwo IS NOT NULL        AND     jr_rel.levelthree IS NULL)
                                        OR
                                        (jr_rel.levelzero IN ($hierarchy)   AND     jr_rel.levelone IS NOT NULL
                                         AND
                                         jr_rel.leveltwo IS NULL            AND     jr_rel.levelthree IS NULL)
                                        OR
                                        (jr_rel.levelzero IN ($hierarchy)   AND     jr_rel.levelone IS NULL
                                         AND
                                         jr_rel.leveltwo IS NULL            AND     jr_rel.levelthree IS NULL))";
                        break;
                    case '3':
                        $sql .= " WHERE ((jr_rel.levelzero IN ($hierarchy)  AND     jr_rel.levelone IS NOT NULL
                                         AND
                                         jr_rel.leveltwo IS NOT NULL        AND     jr_rel.levelthree IS NOT NULL)
                                        OR
                                        (jr_rel.levelzero IN ($hierarchy)   AND     jr_rel.levelone IS NOT NULL
                                         AND
                                         jr_rel.leveltwo IS NOT NULL        AND     jr_rel.levelthree IS NULL)
                                        OR
                                        (jr_rel.levelzero IN ($hierarchy)   AND     jr_rel.levelone IS NOT NULL
                                         AND
                                         jr_rel.leveltwo IS NULL            AND     jr_rel.lelvethree IS NULL)
                                        OR
                                        (
                                         jr_rel.levelzero IN ($hierarchy)   AND     jr_rel.levelone IS NULL
                                         AND
                                         jr_rel.leveltwo IS NULL            AND     jr_rel.levelthree IS NULL))";
                        break;
                }//level
            }//if_generic


            /* Pattern  */
            if ($sector) {
                $sql .= " AND jr.name like '%" . $sector . "%'";
            }//if_sector

            /* Search by */
            $fsJobRole  = str_replace('/',' ',$fsJobRole);
            $searchBy   = explode(' ',$fsJobRole);
            foreach($searchBy as $match) {
                if ($sqlMatch) {
                    $sqlMatch .= " OR ";
                }//if_sqlMatch
                $sqlMatch .= " LOCATE('" . $match ."',jr.name) > 0
                               OR
                               LOCATE(jr.name,'" . $match ."') > 0 ";
            }//for_search

            /* Execute  */
            $sql .= " AND (" . $sqlMatch . ")";
            $sql .= " ORDER BY jr.industrycode,jr.name ";

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
}//FS_MAPPING