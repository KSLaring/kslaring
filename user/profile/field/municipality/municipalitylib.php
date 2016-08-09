<?php
/**
 * Extra Profile Field Municipality - Municipality Library
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/municipality
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    19/11/2014
 * @author          eFaktor     (fbv)
 *
 */

class MunicipalityProfile {
    /* ****** */
    /* PUBLIC */
    /* ****** */

    /**
     * @param           $inUsers
     * 
     * @return          array|null
     * @throws          Exception
     * 
     * @creationDate    09/08/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Get municipalities connected with 
     */
    public static function MunicipalitiesConnected($inUsers) {
        /* Variables    */
        global $DB;
        $sql                = null;
        $rdo                = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT	uid.userid,
                            mu.municipality
                     FROM		{user_info_data}	uid
                        JOIN	{user_info_field}	uif		ON 	uif.id 			= uid.fieldid
                                                            AND	uif.datatype 	= 'municipality'
                        JOIN	{municipality}		mu 		ON 	mu.idmuni		= uid.data
                     WHERE	uid.userid IN ($inUsers) ";
            
            /* Executed */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }
    }//GetMunicipalitiesConnected
    
    /* ******* */
    /* PRIVATE */
    /* ******* */


}//MunicipalityProfile