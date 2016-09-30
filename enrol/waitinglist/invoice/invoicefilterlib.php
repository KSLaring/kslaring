<?php
/**
 * Invoice Approval Users - Filter Library
 *
 * @package         enrol/waitinglist
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    29/09/2016
 * @author          efaktor     (fbv)
 *
 */
define('MAX_INVOICE_USERS', 100);

class InvoiceFilter {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $filtering
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get users invoice filter
     */
    public static function GetSelection_InvoiceFilter($filtering) {
        /* Variables */
        global $DB, $CFG;
        $lstUsers       = null;
        $in             = null;
        
        try {
            /**
             * Get SQL Filter
             */
            list($sqlwhere, $params) = $filtering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));
            
            $lstUsers = $DB->get_records_select_menu('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
            
            /* Filter by industry code  */
            $in = ($lstUsers ? implode(',',array_keys($lstUsers)) : 0);
            $lstUsers = self::GetSelection_ByIndustryCode($in,$filtering->industry);
            
            return $lstUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSelection_InvoiceFilter

    /**
     * @param           $userId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    29/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get resource number connected with user
     */
    public static function GetResourceNumber_ByUser($userId) {
        /* Variables */
        global $DB;
        $rdo = null;

        try {
            $rdo = $DB->get_record('user_resource_number',array('userid' => $userId),'ressursnr');
            if ($rdo) {
                return $rdo->ressursnr;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetResourceNumber_ByUser

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $usersIn
     * @param           $industry
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get users filtered by industry code
     */
    private static function GetSelection_ByIndustryCode($usersIn,$industry) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $total          = null;
        $lstUsers       = array();
        $availableUsers = array();
        $groupName      = null;
        $key            = null;

        try {
            /* Search Criteria  */
            $params = array();
            if ($industry) {
                $params['ic'] = $industry;
            }else {
                $params['ic'] = 0;
            }
            $params['se'] = 2;
            $params['wk'] = 3;
            
            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT  urn.userid,
                                      u.firstname,
                                      u.lastname,
                                      u.email
                         FROM		{user_resource_number}		  urn
                            JOIN	{user}						  u   ON 	u.id 				= urn.userid
                                                                      AND	u.deleted 			= 0
                                                                      AND   u.suspended 		= 0
                            -- Competence
                            JOIN	{user_info_competence_data}	  uic ON    uic.userid			= u.id
                            -- Workaplace
                            JOIN	{report_gen_companydata}	  wk  ON	wk.id				= uic.companyid
                                                                      AND	wk.hierarchylevel	= :wk
                                                                      AND   wk.industrycode		= urn.industrycode
                            -- Sector 		(Level Two)
                            JOIN	{report_gen_company_relation} cr  ON	cr.companyid		= wk.id
                            JOIN	{report_gen_companydata}	  se  ON	se.id				= cr.parentid
                                                                      AND	se.hierarchylevel	= :se
                         WHERE		urn.industrycode = :ic 
                            AND     urn.userid IN ($usersIn)
                         ORDER BY u.firstname, u.lastname ";
            
            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $total = count($rdo);
                if ($total > MAX_INVOICE_USERS) {
                    $availableUsers = self::TooMany_UsersSelector($total);
                }else {
                    $groupName = get_string('users_matching', 'enrol_waitinglist');

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $lstUsers[$instance->userid] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add users    */
                    $availableUsers[$groupName] = $lstUsers;
                }//if_tooMany
            }else {
                /* Info to return */
                $groupName = get_string('no_users_invoice','enrol_waitinglist');
                $availableUsers[$groupName]  = array('');
            }//if_rdo

            return $availableUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSelection_ByIndustryCode

    /**
     * @param           $total
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Message when there are too many usrs
     */
    private static function TooMany_UsersSelector($total) {
        /* Variables    */
        $availableUsers = array();
        $info           = null;
        $tooMany        = null;
        $searchMore     = null;

        try {
            /* Get Info to show */
            $tooMany    = get_string('toomanyuserstoshow', '', $total);
            $searchMore = get_string('please_use_filter','enrol_waitinglist');

            /* Info to return   */
            $availableUsers[$tooMany]       = array('');
            $availableUsers[$searchMore]    = array('');

            return $availableUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//TooMany_UsersSelector
}//InvoiceFilter