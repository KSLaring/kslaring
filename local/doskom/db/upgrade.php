<?php
/**
 *  Update script.
 *
 * Description
 *
 * @package         local
 * @subpackage      doskom
 *
 * @copyright       2015    eFaktor {@link http://www.efaktor.no}
 * @creationDate    01/10/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_doskom_upgrade($oldVersion) {
    /* Variables */
    global $DB;
    $tbldoskom      = null;
    $tbldoskomcomp  = null;
    $table          = null;
    $fldUser        = null;
    $fldToken       = null;
    $tblLog         = null;
    $xmldb_index    = null;

    $dbMan  = $DB->get_manager();
    
    try {
        if ($oldVersion < 2016100100) {
            $table = new xmldb_table('company_data');

            /* User Field   */
            $fldUser    = new xmldb_field('user', XMLDB_TYPE_CHAR, 100, null, null, null, null, 'name');
            if (!$dbMan->field_exists($table, $fldUser)) {
                $dbMan->add_field($table, $fldUser);
            }//if_not_exists

            /* Token Field  */
            $fldToken   = new xmldb_field('token', XMLDB_TYPE_CHAR, 100, null, null, null, null, 'user');
            if (!$dbMan->field_exists($table, $fldToken)) {
                $dbMan->add_field($table, $fldToken);
            }//if_not_exists

            /* Update Stavanger */
            $instance = new stdClass();
            $instance->user     = 'stvgrapi';
            $instance->name     = 'Stavanger kommune';
            $instance->token    = 'Gr3vlaBra!';
            $instance->id       = 108103;
            if ($rdo = $DB->get_record('company_data',array('id' => 108103))) {
                /* Execute */
                $DB->update_record('company_data',$instance);
            }else {
                $instance->timecreated = time();
                /* Execute */
                $DB->insert_record('company_data',$instance);
            }
        }//if_oldVersion
        
        if ($oldVersion < 2017012902) {
            // create table for log to dossier 
            $tblLog = new xmldb_table('log_doskom_completions');
            if (!$dbMan->table_exists($tblLog)) {
                // create able
                // Id --> primary key
                $tblLog->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // company
                $tblLog->add_field('company',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // course
                $tblLog->add_field('course',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // user
                $tblLog->add_field('user',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // completion
                $tblLog->add_field('completion',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // timesent
                $tblLog->add_field('timesent',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                /* Adding keys  */
                $tblLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                // Create table
                $dbMan->create_table($tblLog);
            }
        }//$oldVersion

        // New tables for Multiple sources
        if ($oldVersion < 2017091208) {
            // mdl_doskom table
            $tbldoskom      = new xmldb_table('doskom');
            if (!$dbMan->table_exists($tbldoskom)) {
                // id           --> primary key
                $tbldoskom->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // api          --> index
                $tbldoskom->add_field('api',XMLDB_TYPE_CHAR,'250',null, XMLDB_NOTNULL, null,null);
                // label
                $tbldoskom->add_field('label',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
                // timecreated
                $tbldoskom->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Adding keys, index, foreing keys
                $tbldoskom->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tbldoskom->add_key('endpoint',XMLDB_INDEX_NOTUNIQUE,array('api'));

                // Create table
                $dbMan->create_table($tbldoskom);
            }//if_doskom

            // mdl_doskom_company
            $tbldoskomcomp  = new xmldb_table('doskom_company');
            if (!$dbMan->table_exists($tbldoskomcomp)) {
                // id           --> primary key
                $tbldoskomcomp->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // doskomid     --> foreign key to doskom
                $tbldoskomcomp->add_field('doskomid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // companyid    --> foreign key to company_data
                $tbldoskomcomp->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // active
                $tbldoskomcomp->add_field('active',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,1);

                // Adding keys, index, foreing keys
                $tbldoskomcomp->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tbldoskomcomp->add_key('doskomid',XMLDB_KEY_FOREIGN,array('doskomid'), 'doskom', array('id'));
                $tbldoskomcomp->add_key('doskcompanyidomid',XMLDB_KEY_FOREIGN,array('companyid'), 'company_data', array('id'));
                $tbldoskomcomp->add_key('source',XMLDB_INDEX_NOTUNIQUE,array('doskomid'));
                $tbldoskomcomp->add_key('company',XMLDB_INDEX_NOTUNIQUE,array('companyid'));
                $tbldoskomcomp->add_key('sourcecompany',XMLDB_INDEX_NOTUNIQUE,array('doskomid','companyid'));

                // Create table
                $dbMan->create_table($tbldoskomcomp);
            }//if_doskom_company


            // doskom logs
            // mdl_doskom_log
            $tblLog = new xmldb_table('doskom_log');
            if (!$dbMan->table_exists($tblLog)) {
                // Id --> primary key
                $tblLog->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // action
                $tblLog->add_field('action',XMLDB_TYPE_CHAR,'250',null, null, null,null);
                // description
                $tblLog->add_field('description',XMLDB_TYPE_TEXT,null,null, null, null,null);
                // completion
                $tblLog->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Adding keys, index, foreing keys
                $tblLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tblLog->add_key('timecreated',XMLDB_INDEX_NOTUNIQUE,array('timecreated'));
                $tblLog->add_key('action',XMLDB_INDEX_NOTUNIQUE,array('action'));

                // Create table
                $dbMan->create_table($tblLog);
            }//log_doskom

            // mdl_doskom_api_log
            $tblLog = new xmldb_table('doskom_api_log');
            if (!$dbMan->table_exists($tblLog)) {
                // Id --> primary key
                $tblLog->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // User Name
                $tblLog->add_field('username',XMLDB_TYPE_CHAR,'100',null, null, null,null);
                // First Name
                $tblLog->add_field('firstname',XMLDB_TYPE_CHAR,'100',null, null, null,null);
                // Last  Name
                $tblLog->add_field('lastname',XMLDB_TYPE_CHAR,'100',null, null, null,null);
                // Personal Number
                $tblLog->add_field('personssn',XMLDB_TYPE_CHAR,'15',null, null, null,null);
                // eMail
                $tblLog->add_field('email',XMLDB_TYPE_CHAR,'100',null, null, null,null);
                // Company ID
                $tblLog->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // action
                $tblLog->add_field('saved',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // imported
                $tblLog->add_field('imported',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // timesaved
                $tblLog->add_field('timesaved',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // timeimported
                $tblLog->add_field('timeimported',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Adding keys, index, foreing keys
                $tblLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tblLog->add_key('personssn',XMLDB_INDEX_NOTUNIQUE,array('personssn'));
                $tblLog->add_key('companyid',XMLDB_INDEX_NOTUNIQUE,array('companyid'));
                $tblLog->add_key('email',XMLDB_INDEX_NOTUNIQUE,array('email'));
                $tblLog->add_key('timesaved',XMLDB_INDEX_NOTUNIQUE,array('timesaved'));
                $tblLog->add_key('timeimported',XMLDB_INDEX_NOTUNIQUE,array('timeimported'));

                // Create table
                $dbMan->create_table($tblLog);
            }//if_table_exist

            // doskom catalog log
            $tblLog = new xmldb_table('doskom_catalog_log');
            if (!$dbMan->table_exists($tblLog)) {
                // Id --> primary key
                $tblLog->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // Company ID
                $tblLog->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // category id
                $tblLog->add_field('categoryid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // category name
                $tblLog->add_field('catname',XMLDB_TYPE_CHAR,'250',null, null, null,null);
                // course id
                $tblLog->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // course name
                $tblLog->add_field('coname',XMLDB_TYPE_CHAR,'250',null, null, null,null);
                // time send
                $tblLog->add_field('timesend',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Adding keys, index, foreing keys
                $tblLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tblLog->add_key('companyid',XMLDB_INDEX_NOTUNIQUE,array('companyid'));
                $tblLog->add_key('categoryid',XMLDB_INDEX_NOTUNIQUE,array('categoryid'));
                $tblLog->add_key('courseid',XMLDB_INDEX_NOTUNIQUE,array('courseid'));
                $tblLog->add_key('timesend',XMLDB_INDEX_NOTUNIQUE,array('timesend'));

                // Create table
                $dbMan->create_table($tblLog);
            }//if_doskom_catalog_log
        }//oldversion

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_doskom_upgrade