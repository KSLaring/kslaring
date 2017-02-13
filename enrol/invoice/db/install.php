<?php
/**
 *  Invoice Enrolment - Install Script
 *
 * Description
 *
 * @package         enrol
 * @subpackage      invoice
 *
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @creationDate    26/09/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_invoice_install() {
    global $DB;

    $db_man = $DB->get_manager();

    /***********************/
    /* mdl_enrol_invoice   */
    /***********************/
    $table_invoice = new xmldb_table('enrol_invoice');

    /* Add fields   */
    /* Id               --> Primary Key                     */
    $table_invoice->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* Userid           --> Foreign Key --> User Table      */
    $table_invoice->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    //Companyid
    $table_invoice->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Courseid         --> Foreign Key --> Courses Table   */
    $table_invoice->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* userenrolid         --> Foreign Key --> User Enrolments Table   */
    $table_invoice->add_field('userenrolid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* Waiting list id  */
    $table_invoice->add_field('waitinglistid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Type             --> Not Null                        */
    $table_invoice->add_field('type',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
    /* Responsibility Number    */
    $table_invoice->add_field('responumber', XMLDB_TYPE_CHAR, 100, null, null, null,null);
    /* Service Number           */
    $table_invoice->add_field('servicenumber', XMLDB_TYPE_CHAR, 100, null, null, null,null);
    /* Project Number           */
    $table_invoice->add_field('projectnumber', XMLDB_TYPE_CHAR, 100, null, null, null,null);
    /* Activity Number          */
    $table_invoice->add_field('actnumber', XMLDB_TYPE_CHAR, 100, null, null, null,null);
    /* Street                   */
    $table_invoice->add_field('street', XMLDB_TYPE_CHAR, 255, null, null, null,null);
    /* Post Code                */
    $table_invoice->add_field('postcode', XMLDB_TYPE_CHAR, 255, null, null, null,null);
    /* City                     */
    $table_invoice->add_field('city', XMLDB_TYPE_CHAR, 255, null, null, null,null);
    /* Marked With              */
    $table_invoice->add_field('bilto', XMLDB_TYPE_CHAR, 255, null, null, null,null);
    /* details          --> Invoice Information             */
    $table_invoice->add_field('details',XMLDB_TYPE_CHAR,'250',null, null, null,null);
    /* Invoiced         --> Has been invoiced               */
    $table_invoice->add_field('invoiced',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
    /* Unenrol         --> Has been unenrol               */
    $table_invoice->add_field('unenrol',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
    /* Time Created     --> Not Null                        */
    $table_invoice->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* Time Modified    --> Null                            */
    $table_invoice->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

    //Adding Keys
    $table_invoice->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    //Adding Index
    $table_invoice->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'),'user', array('id'));
    $table_invoice->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'),'course', array('id'));
    $table_invoice->add_key('userenrolid',XMLDB_KEY_FOREIGN,array('userenrolid'),'user_enrolments', array('id'));

    /*********************/
    /* Create the table  */
    /*********************/
    if (!$db_man->table_exists('enrol_invoice')) {
        $db_man->create_table($table_invoice);
    }//if_table_exists
}//xmldb_enrol_invoice_install