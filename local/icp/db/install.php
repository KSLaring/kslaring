<?php
/**
 * Inconsistencies Course Completions - Install Script
 *
 * Description
 *
 * @package         local
 * @subpackage      icp
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      25/05/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_icp_install() {
    global $DB;

    $db_man = $DB->get_manager();

    /* ****************************** */
    /* mdl_course_inconsistencies     */
    /* ****************************** */
    $tblInconsistencies = new xmldb_table('course_inconsistencies');

    //Adding fields
    /* id               (Primary)           */
    $tblInconsistencies->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
    /* Course Id        */
    $tblInconsistencies->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* User ID          */
    $tblInconsistencies->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
    /* Course Module ID */
    $tblInconsistencies->add_field('coursemoduleid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Criteria Compl ID - criteria id  */
    $tblInconsistencies->add_field('criteriaid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Grade Item Id    */
    $tblInconsistencies->add_field('gradeitemid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Completion ID    */
    $tblInconsistencies->add_field('completionid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* timecompleted    */
    $tblInconsistencies->add_field('timecompleted',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Inconsistency    */
    $tblInconsistencies->add_field('inconsistency',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
    /* Fixed    */
    $tblInconsistencies->add_field('fixed',XMLDB_TYPE_INTEGER,'1',null, null, null,null);

    //Adding Keys
    $tblInconsistencies->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $tblInconsistencies->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'), 'course', array('id'));
    $tblInconsistencies->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

    /* *************************** */
    /* Create tables into database */
    /* *************************** */
    if (!$db_man->table_exists('course_inconsistencies')) {
        $db_man->create_table($tblInconsistencies);
    }
}//xmldb_local_force_profile_install