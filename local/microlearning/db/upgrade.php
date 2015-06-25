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

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_express_login_upgrade