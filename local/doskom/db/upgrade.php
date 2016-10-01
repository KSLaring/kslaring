<?php
/**
 *  Update script.
 *
 * Description
 *
 * @package         local
 * @subpackage      doskom
 *
 * @copyright       2015    eFaktor {@link http://www.efaktor.no}
 * @creationDate    01/10/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_doskom_upgrade($oldVersion) {
    /* Variables */
    global $DB;
    $table      = null;
    $fldUser    = null;
    $fldToken   = null;
    $dbMan  = $DB->get_manager();
    
    try {
        if ($oldVersion < 2016100100) {
            $table = new xmldb_table('company_data');

            /* User Field   */
            $fldUser    = new xmldb_field('user', XMLDB_TYPE_CHAR, 100, null, null, null, null, 'name');
            if (!$dbMan->field_exists($table, $fldUser)) {
                $dbMan->add_field($table, $fldUser);
            }//if_not_exists

            /* Token Field  */
            $fldToken   = new xmldb_field('token', XMLDB_TYPE_CHAR, 100, null, null, null, null, 'user');
            if (!$dbMan->field_exists($table, $fldToken)) {
                $dbMan->add_field($table, $fldToken);
            }//if_not_exists

            /* Update Stavanger */
            $instance = new stdClass();
            $instance->user     = 'stvgrapi';
            $instance->name     = 'Stavanger kommune';
            $instance->token    = 'Gr3vlaBra!';
            $instance->id       = 108103;
            if ($rdo = $DB->get_record('company_data',array('id' => 108103))) {
                /* Execute */
                $DB->update_record('company_data',$instance);
            }else {
                $instance->timecreated = time();
                /* Execute */
                $DB->insert_record('company_data',$instance);
            }
        }//if_oldVersion
        
        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_doskom_upgrade