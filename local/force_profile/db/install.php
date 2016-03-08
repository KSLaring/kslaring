<?php
/**
 * Force Update Profile - Install Script
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/08/2014
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_force_profile_install() {
    global $DB;

    $db_man = $DB->get_manager();

    /* ************************** */
    /* mdl_user_force_profile     */
    /* ************************** */
    $tbl_force_profile = new xmldb_table('user_force_profile');

    //Adding fields
    /* id               (Primary)           */
    $tbl_force_profile->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* userid           (primary)           */
    $tbl_force_profile->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* type                                */
    $tbl_force_profile->add_field('type',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
    /* field                                */
    $tbl_force_profile->add_field('field',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
    /* old_value        */
    $tbl_force_profile->add_field('old_value',XMLDB_TYPE_CHAR,'255',null,null,null,null);
    /* Confirmed   */
    $tbl_force_profile->add_field('confirmed',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL,null,null);
    /* Time Created    */
    $tbl_force_profile->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
    /* Time Updated    */
    $tbl_force_profile->add_field('timeupdated',XMLDB_TYPE_INTEGER,'10',null, null,null,null);

    //Adding Keys
    $tbl_force_profile->add_key('primary', XMLDB_KEY_PRIMARY, array('id','userid'));
    $tbl_force_profile->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

    /* *************************** */
    /* Create tables into database */
    /* *************************** */
    if (!$db_man->table_exists('user_force_profile')) {
        $db_man->create_table($tbl_force_profile);
    }
}//xmldb_local_force_profile_install