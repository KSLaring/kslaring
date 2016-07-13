<?php
/**
 *  Invoice Enrolment - Update Script
 *
 * Description
 *
 * @package         enrol
 * @subpackage      invoice/db
 *
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @creationDate    30/10/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_invoice_upgrade($old_version) {
    /* Variable to manage DB */
    global $DB;
    $db_man = $DB->get_manager();

    $table_invoice = new xmldb_table('enrol_invoice');

    if ($old_version < 2014092518) {
        /* New Fields  -- Account Invoice             */
        /* Responsibility Number    */
        $field_respo       = new xmldb_field('responumber', XMLDB_TYPE_CHAR, 100, null, null, null,null,'type');
        if (!$db_man->field_exists($table_invoice, $field_respo)) {
            $db_man->add_field($table_invoice, $field_respo);
        }//field_repo

        /* Service Number           */
        $field_service     = new xmldb_field('servicenumber', XMLDB_TYPE_CHAR, 100, null, null, null,null,'responumber');
        if (!$db_man->field_exists($table_invoice, $field_service)) {
            $db_man->add_field($table_invoice, $field_service);
        }//field_service

        /* Project Number           */
        $field_project     = new xmldb_field('projectnumber', XMLDB_TYPE_CHAR, 100, null, null, null,null,'servicenumber');
        if (!$db_man->field_exists($table_invoice, $field_project)) {
            $db_man->add_field($table_invoice, $field_project);
        }//field_project

        /* Activity Number          */
        $field_act     = new xmldb_field('actnumber', XMLDB_TYPE_CHAR, 100, null, null, null,null,'projectnumber');
        if (!$db_man->field_exists($table_invoice, $field_act)) {
            $db_man->add_field($table_invoice, $field_act);
        }//field_act

        /* New Fields -- Address Invoice            */
        /* Street                       */
        $field_street       = new xmldb_field('street', XMLDB_TYPE_CHAR, 255, null, null, null,null,'actnumber');
        if (!$db_man->field_exists($table_invoice, $field_street)) {
            $db_man->add_field($table_invoice, $field_street);
        }//field_street

        /* Post Code                    */
        $field_code       = new xmldb_field('postcode', XMLDB_TYPE_CHAR, 5, null, null, null,null,'street');
        if (!$db_man->field_exists($table_invoice, $field_code)) {
            $db_man->add_field($table_invoice, $field_code);
        }//field_code

        /* City                         */
        $field_city       = new xmldb_field('city', XMLDB_TYPE_CHAR, 255, null, null, null,null,'postcode');
        if (!$db_man->field_exists($table_invoice, $field_city)) {
            $db_man->add_field($table_invoice, $field_city);
        }//field_city

        /* Bil                          */
        $field_bil       = new xmldb_field('bilto', XMLDB_TYPE_CHAR, 255, null, null, null,null,'city');
        if (!$db_man->field_exists($table_invoice, $field_bil)) {
            $db_man->add_field($table_invoice, $field_bil);
        }//field_bil
    }//if_old_version

    /**
     * @updateDate  28/10/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add waitinglistid
     */
    if ($old_version < 2015281000) {
        $fieldWait       = new xmldb_field('waitinglistid', XMLDB_TYPE_INTEGER,'10',null, null, null,null,'userenrolid');
        if (!$db_man->field_exists($table_invoice, $fieldWait)) {
            $db_man->add_field($table_invoice, $fieldWait);
        }//field_bil
    }//if_old_version

    return true;
}//xmldb_enrol_invoice_upgrade