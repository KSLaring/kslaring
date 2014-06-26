<?php
/**
 * Course Home Page - Update Script
 *
 * Description
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      28/05/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_course_page_upgrade($oldversion) {
    global $DB;

    $db_man = $DB->get_manager();

    /* New Fields -- Course */
    $tableCourse = new xmldb_table('course');
    $fieldHomePage          = new xmldb_field('homepage');
    $fieldHomeVisible       = new xmldb_field('homevisible');
    $fieldHomeDesc          = new xmldb_field('homesummary');
    $fileHomeGraphics       = new xmldb_field('homegraphics');
    $fileHomeVideo          = new xmldb_field('homevideo');

    if ($oldversion < 2014052800) {
        /* Homepage */
        if ($db_man->field_exists($tableCourse,$fieldHomePage)) {
            $db_man->drop_field($tableCourse,$fieldHomePage);
        }//if_exists

        /* Visible */
        if ($db_man->field_exists($tableCourse,$fieldHomeVisible)) {
            $db_man->drop_field($tableCourse,$fieldHomeVisible);
        }//if_exists

        /* Description  */
        if ($db_man->field_exists($tableCourse,$fieldHomeDesc)) {
            $db_man->drop_field($tableCourse,$fieldHomeDesc);
        }//if_exists

        /* Home Graphics  */
        if ($db_man->field_exists($tableCourse,$fileHomeGraphics)) {
            $db_man->drop_field($tableCourse,$fileHomeGraphics);
        }//if_exists

        /* Home Video  */
        if ($db_man->field_exists($tableCourse,$fileHomeVideo)) {
            $db_man->drop_field($tableCourse,$fileHomeVideo);
        }//if_exists
    }//if_oldversion

    return true;
}//xmldb_local_course_page_upgrade