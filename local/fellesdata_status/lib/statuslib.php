<?php
/**
 * Fellesdata Status Integration - Library
 *
 * @package         local/fellesdata_status
 * @subpackage      cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    24/02/2017
 * @author          eFaktor     (fbv)
 *
 */

define('WS_COMPETENCE','ws_get_competence');

class STATUS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Get industry code
     * 
     * @param       String $muni
     * 
     * @return      int|string
     * @throws      Exception
     * 
     * @creationDate    24/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_industry_code($muni) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            if ($muni) {
                // Search criteria
                $params = array();
                $params['name']             = $muni;
                $params['hierarchylevel']   = 1;

                // Execute
                $rdo = $DB->get_record('ks_company',$params,'industrycode');
                if ($rdo) {
                    $industrycode = trim($rdo->industrycode);
                } else {
                    $industrycode = 0;
                }//if_rdo
            }else {
                $industrycode = 0;
            }//if muni

            return $industrycode;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_industry_code

    /***********/
    /* PRIVATE */
    /***********/
}//status