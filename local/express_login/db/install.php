<?php
/**
 * Express Login - Install Script
 *
 * Description
 *
 * @package         local
 * @subpackage      express_login/db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      26/11/2014
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_express_login_install() {
    /* Variables    */
    global $DB;
    $db_man = $DB->get_manager();

    /* User Express Login   */
    if (!$db_man->table_exists('user_express')) {
        /* Create Table */
        $tb_express_login =  new xmldb_table('user_express');

        /* Add Fields       */
        /* Id                   -->     Primary Key                 */
        $tb_express_login->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* User Id              -->     Foreign Key                 */
        $tb_express_login->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Express              --> Not Null                        */
        $tb_express_login->add_field('express',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
        /* Token                --> Not Null                        */
        $tb_express_login->add_field('token',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
        /* Remember             --> Not Null                        */
        $tb_express_login->add_field('remind',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
        /* Attempt              */
        $tb_express_login->add_field('attempt',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,0);
        /* Time Created         --> Not Null                        */
        $tb_express_login->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Time Modified                                            */
        $tb_express_login->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

        /* Add Keys */
        $tb_express_login->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tb_express_login->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

        $db_man->create_table($tb_express_login);
    }//if_exists
}//xmldb_local_express_login_install
