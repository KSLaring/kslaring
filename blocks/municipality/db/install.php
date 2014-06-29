<?php

/**
 * Local Municipality -
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @author          efaktor     (fbv)
 */

    defined('MOODLE_INTERNAL') || die();

function xmldb_block_municipality_install() {
    global $DB;

    $db_man = $DB->get_manager();

    /*********************/
    /* mdl_muni_logos    */
    /*********************/
    $table_muni_logos = new xmldb_table('muni_logos');
    //Adding fields
    /* Id           - Primary Key   */
    $table_muni_logos->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* municipality    - Foreign Key   */
    $table_muni_logos->add_field('municipality',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
    /* logo */
    $table_muni_logos->add_field('logo',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);

    //Adding Keys
    $table_muni_logos->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    /*********************/
    /* Create the table  */
    /*********************/
    if (!$db_man->table_exists('muni_logos')) {
        $db_man->create_table($table_muni_logos);
    }//if_table_exists
}//_install