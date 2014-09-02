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

    public static function municipality_ExitsMuni_User($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id']      = $user_id;
            $params['shortname']    = 'Municipality';

            /* SQL Instruction  */
            $sql = " SELECT		uid.data
                     FROM		{user_info_data} 	uid
                        JOIN	{user_info_field}	uif ON 	uid.fieldid 	= uif.id
                                                        AND uif.shortname   = :shortname
                     WHERE		uid.userid = :user_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->data;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//municipality_ExitsMuni_User

    public static function municipality_GetLogo($municipality) {
        /* Variables    */
        global $DB, $CFG;

        try {
            /* Search Criteria */
            $params = array();
            $params['municipality'] = $municipality;

            /* SQL Instruction */
            $sql = " SELECT     logo
                     FROM       {muni_logos}
                     WHERE      municipality = :municipality ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);

            if ($rdo) {
                /* Get the link with the Municipality logo */
                $url_logo   = new moodle_url($CFG->wwwroot . '/KommuneLogos/' . $rdo->logo);
                $logo       = '<img src=' . $url_logo . '>';

                return $logo;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//municipality_GetLogo

    public static function municipality_GetMunicipality_List() {
        /* Variables    */
        global $DB;

        try {
            /* Municipality List */
            $lst_muni = array();
            $lst_muni[0] = get_string('choose_muni','block_municipality');

            /* Execute   */
            $rdo = $DB->get_records('muni_logos',null,'municipality ASC','municipality');
            if ($rdo) {
                foreach($rdo as $muni) {
                    $lst_muni[$muni->municipality] = $muni->municipality;
                }//for_rdo
            }//if_else

            return $lst_muni;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//municipality_GetMunicipality_List

    public static function municipality_InsertMunicipality_UserProfile($user_id,$municipality) {
        /* Variables    */
        global $DB;

        try {
            /* Get the identifier of Municipality Profile Field */
            $field_id = self::municipality_GetIdentifierProfileField('Municipality');

            /* New Instace */
            $instance = new stdClass();
            $instance->userid   = $user_id;
            $instance->fieldid  = $field_id;
            $instance->data     = $municipality;

            $DB->insert_record('user_info_data',$instance);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//municipality_InsertMunicipality_UserProfile

    protected static function municipality_GetIdentifierProfileField($short_name) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user_info_field',array('shortname'=>$short_name),'id');
            if ($rdo) {
                return $rdo->id;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//municipality_GetIdentifierProfileField
}//municipality
