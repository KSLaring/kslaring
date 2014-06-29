<?php

/**
 * Local Municipality Block  - Library
 *
 * @package         local
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @author          efaktor     (fbv)
 */


/**
 * @param           $user_id
 * @return          bool
 * @throws
 *
 * @creationDate    22/08/013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Returns the municipality connected with the user
 */
function local_muni_exists_municipality_user($user_id) {
    global $DB;

    /* Search Criteria  */
    $params = array();
    $params['user_id']      = $user_id;
    $params['shortname']    = 'Municipality';

    /* SQL Instruction  */
    $sql = " SELECT		uid.data
             FROM		mdl_user_info_data 	uid
                JOIN	mdl_user_info_field	uif ON 	uid.fieldid 	= uif.id
                                                AND uif.shortname = :shortname
             WHERE		uid.userid = :user_id ";

    /* Execute */
    try {
        $rdo = $DB->get_record_sql($sql,$params);
        if ($rdo) {
            return $rdo->data;
        }else {
            return false;
        }
    }catch (Execute $ex) {
        throw $ex;
    }//try_catch
}//local_muni_exists_municipality_user


/**
 * @param           $municipality
 * @return          string
 * @throws          Exception
 *
 * @creationDate    22/08/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return the municipality logo to display in front page
 */
function local_muni_get_municipality_logo($municipality) {
    global $DB, $CFG;

    /* Search Criteria */
    $params = array();
    $params['municipality'] = $municipality;

    /* SQL Instruction */
    $sql = " SELECT     logo
             FROM       {muni_logos}
             WHERE      municipality = :municipality ";

    /* Execute */
    try {
        $rdo = $DB->get_record_sql($sql,$params);

        /* Get the link with the Municipality logo */
        $url_logo   = new moodle_url($CFG->wwwroot . '/KommuneLogos/' . $rdo->logo);
        $logo       = '<img src=' . $url_logo . '>';

        return $logo;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_muni_make_municipality_logo

/**
 * @return          array
 * @throws          Exception
 *
 * @creationDate    22/08/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return the list of all municipalities
 */
function local_muni_get_list_municipalities() {
    global $DB;

    /* Municipality List */
    $lst_muni = array();
    $lst_muni[0] = get_string('choose_muni','local_muni_block');

    /* SQL Instruction */
    try {
        $rdo = $DB->get_records('muni_logos',null,'municipality ASC','municipality');
        if ($rdo) {
            foreach($rdo as $muni) {
                $lst_muni[$muni->municipality] = $muni->municipality;
            }//for_rdo


            return $lst_muni;
        }else {
            return $lst_muni;
        }//if_else
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_get_list_municipalities


/**
 * @param           $shortname
 * @return          bool
 * @throws          Exception
 *
 * @creationDate    22/08/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Retunr the identifier for a specific user profile field
 */
function local_muni_get_identifier_profile($shortname) {
    global $DB;

    try {
        $rdo = $DB->get_record('user_info_field',array('shortname'=>$shortname),'id');
        if ($rdo) {
            return $rdo->id;
        }else {
            return false;
        }//if_else
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_muni_get_identifier_profile

/**
 * @param           $user_id
 * @param           $municipality
 * @return          bool
 * @throws          Exception
 *
 * @creationDate    22/08/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Insert the municipality into the user profile.
 */
function local_muni_insert_municipality_user_profile($user_id,$municipality) {
    global $DB;

    /* Get the identifier of Municipality Profile Field */
    $field_id = local_muni_get_identifier_profile('Municipality');

    /* New Instace */
    $instance = new stdClass();
    $instance->userid   = $user_id;
    $instance->fieldid  = $field_id;
    $instance->data     = $municipality;

    try {
        $DB->insert_record('user_info_data',$instance);

        return true;
    }catch (Exception $ex){
        throw $ex;
    }//try_catch
}//local_insert_municipality_user_profile