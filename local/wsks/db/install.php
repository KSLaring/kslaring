<?php
/**
 * Web Services KS - Install Script
 *
 * @package         local
 * @subpackage      wsks/db
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
 * @author          eFaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_wsks_install() {
    /* Variables */
    global $DB;
    /* Get Manager  */
    $dbMan = $DB->get_manager();
    /* Tables   */
    $tblExtSlaves           = null;
    $tblExtSlavesServices   = null;
    $tblExtSlavesLog        = null;

    try {
        /* External Slaves      */
        if (!$dbMan->table_exists('external_slaves')) {
            /* Create table */
            $tblExtSlaves = new xmldb_table('external_slaves');
            
            /* Add Fields   */
            /* Id       */
            $tblExtSlaves->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* Slave    */
            $tblExtSlaves->add_field('slave',XMLDB_TYPE_CHAR,'250',null,XMLDB_NOTNULL,null,null);
            /* Token    */
            $tblExtSlaves->add_field('token',XMLDB_TYPE_CHAR,'128',null,XMLDB_NOTNULL,null,null);
            /* Time created     */
            $tblExtSlaves->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Time Modified    */
            $tblExtSlaves->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            
            /* Add Keys */
            $tblExtSlaves->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            /* Create table */
            $dbMan->create_table($tblExtSlaves);
        }//if_table_exits

        /* External Slaves Services */
        if (!$dbMan->table_exists('external_slaves_services')) {
            /* Create table */
            $tblExtSlavesServices = new xmldb_table('external_slaves_services');

            /* Add Fields   */
            /* Id           */
            $tblExtSlavesServices->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* Slave id     */
            $tblExtSlavesServices->add_field('slaveid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Service Id   */
            $tblExtSlavesServices->add_field('serviceid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Add Keys     */
            $tblExtSlavesServices->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblExtSlavesServices->add_key('slaveid',XMLDB_KEY_FOREIGN,array('slaveid'), 'external_slaves', array('id'));

            /* Create table */
            $dbMan->create_table($tblExtSlavesServices);
        }//if_table_exist

        /* External Slaves Log  */
        if (!$dbMan->table_exists('external_slaves_services_log')) {
            /* Create table */
            $tblExtSlavesLog = new xmldb_table('external_slaves_services_log');

            /* Add Fields   */
            /* Id           */
            $tblExtSlavesLog->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* Slave id     */
            $tblExtSlavesLog->add_field('slaveid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Service Id   */
            $tblExtSlavesLog->add_field('serviceid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Updated      */
            $tblExtSlavesLog->add_field('updated',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
            /* Message      */
            $tblExtSlavesLog->add_field('message',XMLDB_TYPE_TEXT,null,null,null,null,null);
            /* Updated By   */
            $tblExtSlavesLog->add_field('updatedby',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Time updated */
            $tblExtSlavesLog->add_field('timeupdated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Add Keys     */
            $tblExtSlavesLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblExtSlavesLog->add_key('slaveid',XMLDB_KEY_FOREIGN,array('slaveid'), 'external_slaves', array('id'));

            /* Create table */
            $dbMan->create_table($tblExtSlavesLog);
        }//if_table_exits

        if (!$dbMan->table_exists('fs_fellesdata_log')) {
            $tbl = new xmldb_table('fs_fellesdata_log');

            // Fields
            // Id --> primary key
            $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // action
            $tbl->add_field('action',XMLDB_TYPE_CHAR,'250',null, null, null,null);
            // description
            $tbl->add_field('description',XMLDB_TYPE_TEXT,null,null, null, null,null);
            // completion
            $tbl->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Adding keys, index, foreing keys
            $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tbl->add_index('timecreated',XMLDB_INDEX_NOTUNIQUE,array('timecreated'));
            $tbl->add_index('action',XMLDB_INDEX_NOTUNIQUE,array('action'));

            // Crete table
            $dbMan->create_table($tbl);
        }
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_wsks_install