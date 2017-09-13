<?php
/**
 *  Post-install script for WS Single Sign On.
 *
 * Description
 *
 * @package         local
 * @subpackage      doskom
 *
 * @copyright       2015    eFaktor {@link http://www.efaktor.no}
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_doskom_install() {
    /* Variable to manage DB */
    global $DB;
    $tbldoskom      = null;
    $tbldoskomcomp  = null;
    $tbllog         = null;

    $db_man = $DB->get_manager();

    /***********************/
    /* mdl_company_data    */
    /***********************/
    $table_company = new xmldb_table('company_data');
    //Adding fields
    // id              --> Primary Key
    $table_company->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    // name          --> Foreign Key --> mdl_user
    $table_company->add_field('name',XMLDB_TYPE_CHAR,'250',null,XMLDB_NOTNULL,null,null);
    // User
    $table_company->add_field('user', XMLDB_TYPE_CHAR, 100, null, null, null, null);
    // Token
    $table_company->add_field('token', XMLDB_TYPE_CHAR, 100, null, null, null, null);
    // timecreated      --> Integer. Not Null
    $table_company->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    //Adding Keys
    $table_company->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    /***********************/
    /* mdl_user_company    */
    /***********************/
    $table_user_company = new xmldb_table('user_company');
    //Adding fields
    // id              --> Primary Key
    $table_user_company->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    // userid          --> Foreign Key --> mdl_user
    $table_user_company->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    // companyid        --> Foreign Key --> mdl_company_data
    $table_user_company->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    // timecreated      --> Integer. Not Null
    $table_user_company->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    //Adding Keys
    $table_user_company->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    //Adding Index
    $table_user_company->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'),'user', array('id'));
    $table_user_company->add_key('companyid',XMLDB_KEY_FOREIGN,array('companyid'),'company_data', array('id'));


    // Create tables
    // company_data
    if (!$db_man->table_exists('company_data')) {
        $db_man->create_table($table_company);
    }//if_table_exists
    //mdl_user_company
    if (!$db_man->table_exists('user_company')) {
        $db_man->create_table($table_user_company);
    }//if_table_exists

    // Add new field into mdl_enrol
    $tableEnrol     = new xmldb_table('enrol');
    $fieldCompany   = new xmldb_field('company');
    if (!$db_man->field_exists($tableEnrol,$fieldCompany)) {
        $fieldCompany->set_attributes(XMLDB_TYPE_TEXT,null,null, null, null,null);
        $db_man->add_field($tableEnrol,$fieldCompany);
    }//if_exists

    // Update Attribute - User Table
    $tableUser      = new xmldb_table('user');
    $fieldSecret    = new xmldb_field('secret', XMLDB_TYPE_CHAR, '250', null, XMLDB_NOTNULL, null,null);
    $db_man->change_field_precision($tableUser,$fieldSecret);

    // Add attribute - User Table
    $tableUser      = new xmldb_table('user');
    $fieldSource    = new xmldb_field('source', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null,null,'auth');
    if (!$db_man->field_exists($tableUser,$fieldSource)) {
        $db_man->add_field($tableUser,$fieldSource);
    }//if_exists

    /***************************/
    /* Create temporary table  */
    /* to import users         */
    /***************************/
    // mdl_user_personalia
    if (!$db_man->table_exists('user_personalia')) {
        $table_personalia = new xmldb_table('user_personalia');

        // Id   -- Autonumeric
        $table_personalia->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        // Person ID
        $table_personalia->add_field('personid',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Person Ext ID
        $table_personalia->add_field('personextid',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Employment ID
        $table_personalia->add_field('employmentid',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Employment Ext ID
        $table_personalia->add_field('employmentextid',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // User Name
        $table_personalia->add_field('username',XMLDB_TYPE_CHAR,'100',null, null, null,null);
        // User Name Ext
        $table_personalia->add_field('userextname',XMLDB_TYPE_CHAR,'100',null, null, null,null);
        // First Name
        $table_personalia->add_field('firstname',XMLDB_TYPE_CHAR,'100',null, null, null,null);
        // Last  Name
        $table_personalia->add_field('lastname',XMLDB_TYPE_CHAR,'100',null, null, null,null);
        // Personal Number
        $table_personalia->add_field('personssn',XMLDB_TYPE_CHAR,'15',null, null, null,null);
        // Mobile Phone
        $table_personalia->add_field('mobilephone',XMLDB_TYPE_CHAR,'20',null, null, null,null);
        // eMail
        $table_personalia->add_field('email',XMLDB_TYPE_CHAR,'100',null, null, null,null);
        // City
        $table_personalia->add_field('city',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Country
        $table_personalia->add_field('country',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Division Name (Workplace)
        $table_personalia->add_field('divisionname',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Division EXT ID
        $table_personalia->add_field('divisionextid',XMLDB_TYPE_CHAR,'250',null, null, null,null);
        // Company ID
        $table_personalia->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Status                       */
        $table_personalia->add_field('status',XMLDB_TYPE_INTEGER,'1',null,null,null,null);
        // Description Error
        $table_personalia->add_field('msgerror',XMLDB_TYPE_CHAR,'250',null, null, null,null);

        //Adding Keys
        $table_personalia->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Create Table
        $db_man->create_table($table_personalia);
    }//if_table_exists_user_personalia

    // create table for log to dossier 
    $tblLog = new xmldb_table('log_doskom_completions');
    if (!$db_man->table_exists($tblLog)) {
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

        // Adding keys
        $tblLog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Create table
        $db_man->create_table($tblLog);
    }


    // mdl_doskom table
    $tbldoskom      = new xmldb_table('doskom');
    if (!$db_man->table_exists($tbldoskom)) {
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
        $db_man->create_table($tbldoskom);
    }//if_doskom


    // mdl_doskom_company
    $tbldoskomcomp  = new xmldb_table('doskom_company');
    if (!$db_man->table_exists($tbldoskomcomp)) {
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

        // Create table
        $db_man->create_table($tbldoskomcomp);
    }//if_doskom_company

    // mdl_doskom_log
    $tblLog = new xmldb_table('doskom_log');
    if (!$db_man->table_exists($tblLog)) {
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

        // Create table
        $db_man->create_table($tblLog);
    }//log_doskom

    // mdl_doskom_api_log
    $tblLog = new xmldb_table('doskom_api_log');
    if (!$db_man->table_exists($tblLog)) {
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
        $db_man->create_table($tblLog);
    }//if_table_exist

    // doskom catalog log
    $tblLog = new xmldb_table('doskom_catalog_log');
    if (!$db_man->table_exists($tblLog)) {
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
        $db_man->create_table($tblLog);
    }//if_doskom_catalog_log

    // Last time executed
    set_config('lastexecution', 0, 'local_doskom');
}//xmldb_local_doskom_install