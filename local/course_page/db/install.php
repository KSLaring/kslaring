<?php

/**
 * Course Home Page - Install Script
 *
 * Description
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      28/04/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_course_page_install() {
    global $DB;

    $db_man = $DB->get_manager();

    /* New Fields -- Course */
    $tableCourse = new xmldb_table('course');
    $fieldHomePage          = new xmldb_field('homepage');
    $fieldHomeVisible       = new xmldb_field('homevisible');
    $fieldHomeDesc          = new xmldb_field('homesummary');
    $fileHomeGraphics       = new xmldb_field('homegraphics');
    $fileHomeVideo          = new xmldb_field('homevideo');
    /* Homepage */
    if (!$db_man->field_exists($tableCourse,$fieldHomePage)) {
        $fieldHomePage->set_attributes(XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
        $db_man->add_field($tableCourse,$fieldHomePage);
    }//if_exists
    /* Visible */
    if (!$db_man->field_exists($tableCourse,$fieldHomeVisible)) {
        $fieldHomeVisible->set_attributes(XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
        $db_man->add_field($tableCourse,$fieldHomeVisible);
    }//if_exists
    /* Description  */
    if (!$db_man->field_exists($tableCourse,$fieldHomeDesc)) {
        $fieldHomeDesc->set_attributes(XMLDB_TYPE_TEXT,null,null,null,null,null);
        $db_man->add_field($tableCourse,$fieldHomeDesc);
    }//if_exists
    /* Home Graphics  */
    if (!$db_man->field_exists($tableCourse,$fileHomeGraphics)) {
        $fileHomeGraphics->set_attributes(XMLDB_TYPE_INTEGER,'10',null,null,null,0);
        $db_man->add_field($tableCourse,$fileHomeGraphics);
    }//if_exists
    /* Home Video  */
    if (!$db_man->field_exists($tableCourse,$fileHomeVideo)) {
        $fileHomeVideo->set_attributes(XMLDB_TYPE_CHAR,'255',null,null,null);
        $db_man->add_field($tableCourse,$fileHomeVideo);
    }//if_exists
}//xmldb_local_course_page_install
