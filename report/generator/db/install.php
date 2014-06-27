<?php

/**
 *  Post-install script for the report generator plugin.
 *
 * Description
 *
 * @package         report
 * @subpackage      generator
 * @copyright       2010 eFaktor
 * @updateDate      06/09/2012
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Report generator upgrade code.
 */
function xmldb_report_generator_install() {
    global $DB, $CFG;

    $db_man = $DB->get_manager();

    /* ************************** */
    /* mdl_report_gen_companydata */
    /* ************************** */
    $table_company_data = new xmldb_table('report_gen_companydata');
    //Adding fields
    /* id               (Primary)           */
    $table_company_data->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* name             (Not null)          */
    $table_company_data->add_field('name',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
    /* hierarchylevel   (Not null - Index)  */
    $table_company_data->add_field('hierarchylevel',XMLDB_TYPE_INTEGER,'2',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,1);
    /* modified         (Not null)          */
    $table_company_data->add_field('modified',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    //Adding Keys
    $table_company_data->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    //Adding Index
    $table_company_data->add_index('hierarchylevel',XMLDB_INDEX_NOTUNIQUE,array('hierarchylevel'));

    /* ****************** */
    /* report_gen_jobrole */
    /* ****************** */
    $table_job_role = new xmldb_table('report_gen_jobrole');
    //Adding fields
    /* id               (Primary)       */
    $table_job_role->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* name             (Not null)      */
    $table_job_role->add_field('name',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
    /* modified         (Not null)      */
    $table_job_role->add_field('modified',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    //Adding Keys
    $table_job_role->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    /* ************************** */
    /* report_gen_outcome_jobrole */
    /* ************************** */
    $table_outcome_job_role = new xmldb_table('report_gen_outcome_jobrole');
    //Adding fields
    /* id           (Primary)                   */
    $table_outcome_job_role->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* outcomeid    (Foreign key - Not null)    */
    $table_outcome_job_role->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    /* jobroleid    (Foreign key - Not null)    */
    $table_outcome_job_role->add_field('jobroleid',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    /* modified     (Not null)                  */
    $table_outcome_job_role->add_field('modified',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    //Adding Keys
    $table_outcome_job_role->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table_outcome_job_role->add_key('outcomeid',XMLDB_KEY_FOREIGN,array('outcomeid'), 'grade_outcomes', array('id'));
    $table_outcome_job_role->add_key('jobroleid',XMLDB_KEY_FOREIGN,array('jobroleid'), 'report_gen_jobrole', array('id'));

    /* *************************** */
    /* report_gen_company_relation */
    /* *************************** */
    $table_company_relation = new xmldb_table('report_gen_company_relation');
    //Adding fields
    /* id           (Primary)                   */
    $table_company_relation->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* companyid    (Foreign Key - Not null)    */
    $table_company_relation->add_field('companyid',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    /* parentid     (Foreign Key - Not null)    */
    $table_company_relation->add_field('parentid',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    /* modified     (Not null)                  */
    $table_company_relation->add_field('modified',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    //Adding Keys
    $table_company_relation->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table_company_relation->add_key('companyid',XMLDB_KEY_FOREIGN,array('companyid'), 'report_gen_companydata', array('id'));
    $table_company_relation->add_key('parentid',XMLDB_KEY_FOREIGN,array('parentid'), 'report_gen_companydata', array('id'));

    /* ***************************** */
    /* report_gen_outcome_expiration */
    /* ***************************** */
    $table_outcome_expiration = new xmldb_table('report_gen_outcome_exp');
    //Adding fields
    /* id               (Primary)                   */
    $table_outcome_expiration->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* outcomeid        (Foreign key - Not null)    */
    $table_outcome_expiration->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    /* expirationperiod (Int - Not null - Index)    */
    $table_outcome_expiration->add_field('expirationperiod',XMLDB_TYPE_INTEGER,'2',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,0);
    /* modified         (Not null)                  */
    $table_outcome_expiration->add_field('modified',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL,null,null);
    //Adding Keys
    $table_outcome_expiration->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table_outcome_expiration->add_key('outcomeid',XMLDB_KEY_FOREIGN,array('outcomeid'), 'grade_outcomes', array('id'));
    //Adding Index
    $table_outcome_expiration->add_index('expirationperiod',XMLDB_INDEX_NOTUNIQUE,array('expirationperiod'));

    /* *************************** */
    /* Create tables into database */
    /* *************************** */
    if (!$db_man->table_exists('report_gen_companydata')) {
        $db_man->create_table($table_company_data);
    }
    if (!$db_man->table_exists('report_gen_jobrole')) {
        $db_man->create_table($table_job_role);
    }
    if (!$db_man->table_exists('report_gen_outcome_jobrole')) {
        $db_man->create_table($table_outcome_job_role);
        if ($db_man->table_exists('report_gen_outcomejobrolerel')) {
            $outcome_jobrole = $DB->get_records('report_gen_outcomejobrolerel');
            foreach ($outcome_jobrole as $out) {
                $new = new stdClass();
                $new->outcomeid = $out->outcomeid;
                $new->jobroleid = $out->jobroleid;
                $new->modified  = time();
                $DB->insert_record('report_gen_outcome_jobrole',$new);
            }
        }
    }
    if (!$db_man->table_exists('report_gen_company_relation')) {
        $db_man->create_table($table_company_relation);
        if ($db_man->table_exists('report_gen_companyrelation')) {
            $company_relation = $DB->get_records('report_gen_companyrelation');
            foreach ($company_relation as $relation) {
                $new = new stdClass();
                $new->companyid = $relation->companyid;
                $new->parentid  = $relation->parentid;
                $new->modified  = time();
                $DB->insert_record('report_gen_company_relation',$new);
            }
        }
    }
    if (!$db_man->table_exists('report_gen_outcome_exp')) {
        $db_man->create_table($table_outcome_expiration);
        if ($db_man->table_exists('report_gen_outcomeexpiration')) {
            $expiration = $DB->get_records('report_gen_outcomeexpiration');
            foreach ($expiration as $exp) {
                $new = new stdClass();
                $new->outcomeid         = $exp->outcomeid;
                $new->expirationperiod  = $exp->expirationperiod;
                $new->modified          = time();
                $DB->insert_record('report_gen_outcome_exp',$new);
            }
        }
    }
}//xmldb_report_generator_install