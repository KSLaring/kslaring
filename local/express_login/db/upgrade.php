<?php
/**
 * Express Login - Update Script
 *
 * Description
 *
 * @package         local
 * @subpackage      express_login/db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      08/07/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_express_login_upgrade($old_version) {
    /* Variables */
    global $DB;
    $table  = null;
    $db_man = $DB->get_manager();

    if ($old_version <= 2015070814) {
        /* Table    */
        $table = new xmldb_table('user_express');

        /* New Field - Auto */
        $fieldAuto = new xmldb_field('auto', XMLDB_TYPE_INTEGER, 1, null, null, null, null, 'attempt');
        if (!$db_man->field_exists($table, $fieldAuto)) {
            $db_man->add_field($table, $fieldAuto);
        }//if_not_exists

        /* Sent Mail        */
        $fieldSent = new xmldb_field('sent', XMLDB_TYPE_INTEGER, 1, null, null, null, null, 'auto');
        if (!$db_man->field_exists($table, $fieldSent)) {
            $db_man->add_field($table, $fieldSent);
        }//if_not_exists
    }//if_old_version

    return true;
}//xmldb_local_express_login_upgrade