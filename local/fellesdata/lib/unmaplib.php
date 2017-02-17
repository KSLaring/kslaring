<?php
/**
 * Fellesdata Integration - Unmap Library
 *
 * @package         local/fellesdata
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    18/11/2016
 * @author          eFaktor     (fbv)
 *
 */

class FS_UnMap {
    /**********/
    /* PUBLIC */
    /**********/
    
    public static function FSCompaniesMapped($level,$sector,$start,$length) {
        /* Variables */
        $fsMapped    = null;
        $total       = null;

        try {
            /* Get Companies Mapped */
            $fsMapped = self::GetFSCompaniesMapped($level,$sector,$start,$length);
            /* Get Total    */
            $total = self::GetTotalFSCompaniesMapped($level,$sector);

            return array($fsMapped,$total);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FSCompaniesMapped


    /**
     * @param           $toUnMap
     *
     * @throws          Exception
     *
     * @creationDate    19/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap organizations
     */
    public static function UnMap($toUnMap) {
        /* Variables */

        try {
            if ($toUnMap) {
                foreach ($toUnMap as $infoMapped) {
                    self::UnMapOrganization($infoMapped);
                }//for_toUnMap
            }//if_toUnaMap
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UnMap

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $infoOrg
     *
     * @throws          Exception
     * @throws          dml_transaction_exception
     *
     * @creationDate    19/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Un map organizations
     */
    private static function UnMapOrganization($infoOrg) {
        /* Variables */
        global $DB;
        $trans      = null;
        $instance   = null;
        $params     = null;


        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            // New instance into mdl_ksfs_org_unmap
            $instance = new stdClass();
            $instance->kscompany = $infoOrg->kscompany;
            $instance->fscompany = $infoOrg->fscompany;
            $instance->tosync    = ($infoOrg->new ? 1 : 0);
            $instance->sync      = ($infoOrg->new ? 0 : 1);

            // Insert
            $instance->id = $DB->insert_record('ksfs_org_unmap',$instance);

            // Delete from mdl_ks
            if ($infoOrg->new) {
                $params = array();
                $params['companyid']    = $infoOrg->kscompany;
                // Execute
                $DB->delete_records('ks_company',$params);
            }//if_new


            // Delete instance from mdl_ksfs_company
            $params = array();
            $params['kscompany']    = $instance->kscompany;
            $params['fscompany']    = $instance->fscompany;
            // Execute
            $DB->delete_records('ksfs_company',$params);

            // Delete from fs_company because is not mapped anymore
            $params = array();
            $params['id']           = $infoOrg->id;
            $params['companyid']    = $infoOrg->fscompany;
            // Execute
            $DB->delete_records('fs_company',$params);

            // Update fs_imp_company as imported = 0
            $rdo = $DB->get_record('fs_imp_company',array('ORG_ENHET_ID' => $infoOrg->fscompany),'id,imported');
            if ($rdo) {
                $rdo->imported = 0;
                $DB->update_record('fs_imp_company',$rdo);
            }

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UnMapOrganization

    /**
     * @param           $level
     * @param           $sector
     * @param           $start
     * @param           $length
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    18/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all companies already mapped
     */
    private static function GetFSCompaniesMapped($level,$sector,$start,$length) {
        /* Variables */
        global $DB;
        $fsMapped   = array();
        $infoMapped = null;
        $sql        = null;
        $sqlMatch   = null;
        $searchBy   = null;
        $rdo        = null;
        $params     = null;
        
        try {
            /* Search Criteria */
            $params = array();
            $params['level'] = $level;

            /* SQL Instruction  */
            $sql = " SELECT	  DISTINCT
                                  fs.id,
                                  fs.companyid 							as 'fscompany',
                                  fs.name,
                                  ks.companyid 							as 'kscompany',
                                  CONCAT(ks.industrycode,' - ',ks.name) 	as 'ksname',
                                  fs.new
                     FROM		  {fs_company}		fs
                        JOIN	  {ksfs_company}	ksfs 	ON	ksfs.fscompany 	= fs.companyid
                        JOIN	  {ks_company}		ks		ON  ks.companyid 	= ksfs.kscompany
                        -- NOT UNMAPPED
                        LEFT JOIN {ksfs_org_unmap}	un		ON	un.kscompany 	= ks.companyid
                                                            AND un.fscompany 	= ksfs.fscompany
                     WHERE 	fs.level = :level
                        AND un.id IS NULL";

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

                    $sqlMatch .= " LOCATE('" . $match . "', fs.name) >0 ";
                }//for_search

                $sql .= " AND (LOCATE('" . $sector . "', fs.name) >0 
                               OR
                               " . $sqlMatch . ") ";
            }

            /* Execute */
            $sql .= " ORDER BY fs.name ";
            $rdo = $DB->get_records_sql($sql,$params,$start,$length);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info mapped  */
                    $infoMapped = new stdClass();
                    $infoMapped->id             = $instance->id;
                    $infoMapped->fscompany      = $instance->fscompany;
                    $infoMapped->fsname         = $instance->name;
                    $infoMapped->kscompany      = $instance->kscompany;
                    $infoMapped->ksname         = $instance->ksname;
                    $infoMapped->new            = $instance->new;

                    /* Add to the list  */
                    $fsMapped[$instance->id] = $infoMapped;
                }//for_Rdo
            }//if_Rdo

            return $fsMapped;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFSCompaniesMapped

    /**
     * @param           $level
     * @param           $sector
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    18/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many companies are mapped
     */
    private static function GetTotalFSCompaniesMapped($level,$sector) {
        /* Variables */
        global $DB;
        $sql        = null;
        $sqlMatch   = null;
        $searchBy   = null;
        $rdo        = null;
        $params     = null;
        
        try {
            /* Search Criteria */
            $params = array();
            $params['level'] = $level;

            /* SQL Instruction  */
            $sql = " SELECT	count(*) as 'total'
                     FROM			{fs_company}		fs
                        JOIN		{ksfs_company}	    ksfs 	ON	ksfs.fscompany 	= fs.companyid
                        JOIN		{ks_company}		ks		ON  ks.companyid 	= ksfs.kscompany
                        -- NOT UNMAPPED
                        LEFT JOIN	{ksfs_org_unmap}	un		ON	un.kscompany 	= ks.companyid
                                                                AND un.fscompany 	= ksfs.fscompany
                     WHERE 	fs.level = :level
                        AND un.id IS NULL";

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

                    $sqlMatch .= " LOCATE('" . $match . "', fs.name) >0 ";
                }//for_search

                $sql .= " AND (LOCATE('" . $sector . "', fs.name) >0 
                               OR
                               " . $sqlMatch . ") ";
            }

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTotalFSCompaniesMapped
}//FS_UnMap

