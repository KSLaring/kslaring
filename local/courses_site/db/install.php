<?php
/**
 * Local Block Courses Site - Version Settings
 *
 * @package         local
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    23/05/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_courses_site_install() {
    global $DB, $CFG;

    $db_man = $DB->get_manager();

    /*********************/
    /* mdl_courses_nav   */
    /*********************/
    $table_courses_site = new xmldb_table('block_courses_site');
    //Adding fields
    /* Id           - Primary Key   */
    $table_courses_site->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* Course_id    - Foreign Key   */
    $table_courses_site->add_field('course_id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* Title */
    $table_courses_site->add_field('title',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
    /* Description  */
    $table_courses_site->add_field('description',XMLDB_TYPE_TEXT,'big',null,null,null,null);
    /* Picture      */
    $table_courses_site->add_field('picture',XMLDB_TYPE_INTEGER,'10',null, null,null,0);
    /* picturetitle */
    $table_courses_site->add_field('picturetitle', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    /* Order        */
    $table_courses_site->add_field('sortorder',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,0);
    /* Timecreated  */
    $table_courses_site->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
    /* Timemodified */
    $table_courses_site->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null,null,null);

    //Adding Keys
    $table_courses_site->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    //Adding Index
    $table_courses_site->add_key('course_id',XMLDB_KEY_FOREIGN,array('course_id'), 'course', array('id'));

    /*********************/
    /* Create the table  */
    /*********************/
    if (!$db_man->table_exists('block_courses_site')) {
        $db_man->create_table($table_courses_site);
    }//if_table_exists
}//xmldb_local_courses_site_install