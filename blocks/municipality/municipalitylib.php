<?php

/**
 * Municipality Block - Library
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @updateDate      20/08/2014
 * @author          efaktor     (fbv)
 */

class Municipality  {

    /**
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    22/08/2013
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Municipality connected to the users
     */
    public static function municipality_ExitsMuni_User($user_id) {
        /* Variables    */
        global $DB,$CFG;
        $municipality = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id']      = $user_id;
            $params['datatype']    = 'municipality';

            /* SQL Instruction  */
            $sql = " SELECT		mu.idmuni,
                                mu.municipality,
                                mu.logo
                     FROM		{municipality}      mu
                        JOIN    {user_info_data} 	uid ON  uid.data      = mu.idmuni
                        JOIN	{user_info_field}	uif ON 	uid.fieldid 	= uif.id
                                                        AND uif.datatype  = :datatype
                     WHERE		uid.userid = :user_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $municipality = new stdClass();
                $municipality->idmuni   = $rdo->idmuni;
                $municipality->name     = $rdo->municipality;
                $municipality->logo     = new moodle_url($CFG->wwwroot . '/KommuneLogos/' . $rdo->logo);
            }//if_rdo
            return $municipality;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//municipality_ExitsMuni_User

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the Municipality Profile Field has been installed
     */
    public static function ExistsMunicipality_Profile() {
        /* Variables    */
        global $DB;

        try {
            /* Execute   */
            $rdo = $DB->get_record('user_info_field',array('datatype' => 'municipality'));
            if ($rdo) {
                return true;
            }else {
                return false;
            }///if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistsMunicipality_Profile
}//municipality
