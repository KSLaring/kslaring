<?php
/**
 * Slaves Integration - Web Services  Library
 *
 * @package         local/wsslave
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
 * @author          eFaktor     (fbv)
 * 
 */

class WS_SLAVE {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $service
     * @param           $result
     * 
     * @throws          Exception
     * 
     * @creationDate    07/11/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Update the service from Main system connected with the slave system
     * 
     */
    public static function Process_UpdateMainService($service,&$result) {
        /* Variables */
        
        try {
            /* Check if the service exits*/
            if (self::CheckService($service)) {
                /* Upate token  */
                self::UpdateToken($service,$result);
            }else {
                $result['error']        = 500;
                $result['updated']      = 0;
                $result['msg_error']    = get_string('no_service','local_wsslave');
            }//if_service
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['updated']      = 0;
            $result['msg_error']    = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//Process_MainService
    
    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $service
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if exists the service in the slave system
     */
    private static function CheckService($service) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            /* Search Criteria  */
            $params =array();
            $params['plugin'] = 'local_' . $service['name'];

            global $CFG;
            $dbLog = " MAIN: " . $service['main'] . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/SLAVE.log");

            /* SQL instruction  */
            $sql = " SELECT	cs.id
                     FROM	{config_plugins}	cs
                     WHERE	cs.plugin = :plugin
                        AND	cs.value like '%" . $service['main'] . "%' ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CheckService

    /**
     * @param           $service
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    07/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the token connected with service
     */
    private static function UpdateToken($service,&$result) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;
        $sql    = null;

        try {
            /* Search Criteria  */
            $params =array();
            $params['plugin'] = 'local_' . $service['name'];

            /* SQL Instruction */
            $sql = " SELECT	cs.id,
                            cs.name,
                            cs.value
                     FROM	{config_plugins}	cs
                     WHERE	cs.plugin = :plugin
                        AND	cs.name like '%token%' ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Update to new token  */
                $rdo->value = $service['token'];

                /* Execute */
                $DB->update_record('config_plugins',$rdo);
                $result['updated']      = 1;
            }else {
                /* No token to update */
                $result['error']        = 500;
                $result['updated']      = 0;
                $result['msg_error']    = get_string('no_token','local_wsslave');
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateToken

}//WS_SLAVE