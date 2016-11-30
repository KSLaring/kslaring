<?php
/**
 * Micro Learning Plugin - Update Script
 *
 * Description
 *
 * @package         local
 * @subpackage      microlearning/db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      16/06/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_microlearning_upgrade($oldVersion) {
    /* Variables    */
    global $DB;
    $tableMicroDeliveries   = null;
    $fieldMessage           = null;
    $tblMicrolearning       = null;
    $fieldDuplicatefrom     = null;
    $fieldAddUsers          = null;

    try {
        /* Get Manager  */
        $db_man = $DB->get_manager();

        if ($oldVersion < 2015061600) {
            /* New Fields   */
            $tableMicroDeliveries = new xmldb_table('microlearning_deliveries');
            $fieldMessage         = new xmldb_field('message', XMLDB_TYPE_TEXT, null, null, null, null,null,'sent');
            if (!$db_man->field_exists($tableMicroDeliveries, $fieldMessage)) {
                $db_man->add_field($tableMicroDeliveries, $fieldMessage);
            }//if_exists
        }//if_oldVersion

        if ($oldVersion < 2015090810) {
            /* Last time executed   */
            set_config('lastexecution', 0, 'local_microlearning');
        }//if_oldversion

        /* Add a new filed to know from where it was duplicated */
        if ($oldVersion <= 2015100100) {
            $tblMicrolearning   = new xmldb_table('microlearning');
            $fieldDuplicatefrom = new xmldb_field('duplicated_from', XMLDB_TYPE_INTEGER, 10, null, null, null,null,'activate');
            if (!$db_man->field_exists($tblMicrolearning, $fieldDuplicatefrom)) {
                $db_man->add_field($tblMicrolearning, $fieldDuplicatefrom);
            }//if_exists
        }//if_oldVersion

        if ($oldVersion < 2016031600) {
            $tblMicrolearning   = new xmldb_table('microlearning');
            $fieldAddUsers = new xmldb_field('addusers', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null,0,'duplicated_from');
            if (!$db_man->field_exists($tblMicrolearning, $fieldAddUsers)) {
                $db_man->add_field($tblMicrolearning, $fieldAddUsers);
            }//if_exists
        }//if_oldVersion_2016031600

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_express_login_upgrade