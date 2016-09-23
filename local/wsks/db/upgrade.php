<?php
/**
 * Kommit ADFS - Update Script
 *
 * @package         local
 * @subpackage      wsks
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    23/09/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_wsks_upgrade($oldVersion) {
    /* Variables */
    global $DB;
    $table  = null;
    $dbMan  = $DB->get_manager();

    try {
        /* New Table user_resource_number */
        if ($oldVersion < 2016092300) {
            if (!$dbMan->table_exists('user_resource_number')) {
                /* Create Table */
                $table =  new xmldb_table('user_resource_number');

                /* Add fields   */
                /* Id               --> Primary Key */
                $table->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* User Id          --> Foreign Key */
                $table->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Resource Number  */
                $table->add_field('ressursnr',XMLDB_TYPE_CHAR,'50',null,XMLDB_NOTNULL,null,null);

                /* Add Keys */
                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $table->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbMan->create_table($table);
            }
        }//if_oldVersion

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_wsks_upgrade