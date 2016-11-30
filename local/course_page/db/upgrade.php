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
    // Variables
    global $DB;
    $db_man = $DB->get_manager();

    // Table
    $tableCourse        = new xmldb_table('course');
    // New fields
    $fieldHomePage      = new xmldb_field('homepage');
    $fieldHomeVisible   = new xmldb_field('homevisible');
    $fieldHomeDesc      = new xmldb_field('homesummary');
    $fileHomeGraphics   = new xmldb_field('homegraphics');
    $fileHomeVideo      = new xmldb_field('homevideo');

    if ($oldversion < 2014052800) {
        // Homepage
        if ($db_man->field_exists($tableCourse,$fieldHomePage)) {
            $db_man->drop_field($tableCourse,$fieldHomePage);
        }//if_exists

        // Visible
        if ($db_man->field_exists($tableCourse,$fieldHomeVisible)) {
            $db_man->drop_field($tableCourse,$fieldHomeVisible);
        }//if_exists

        // Description
        if ($db_man->field_exists($tableCourse,$fieldHomeDesc)) {
            $db_man->drop_field($tableCourse,$fieldHomeDesc);
        }//if_exists

        // Home Graphics
        if ($db_man->field_exists($tableCourse,$fileHomeGraphics)) {
            $db_man->drop_field($tableCourse,$fileHomeGraphics);
        }//if_exists

        // Home Video
        if ($db_man->field_exists($tableCourse,$fileHomeVideo)) {
            $db_man->drop_field($tableCourse,$fileHomeVideo);
        }//if_exists
    }//if_oldversion

    if ($oldversion < 2016012100) {
        coursepage_upgrade::add_ratings_format_option();
    }//If_oldversion

    return true;
}//xmldb_local_course_page_upgrade

class coursepage_upgrade {
    public static function add_ratings_format_option() {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $instanceFormat = null;
        
        try {
            // SQL Instruction
            $sql = " SELECT   DISTINCT  id,
                                        format
                     FROM 	            {course}
                     WHERE 	            format IN ('classroom','classroom_frikomport','elearning_frikomport',
                                                   'netcourse','single_frikomport','format_whitepaper') ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $instanceFormat = new stdClass();
                    $instanceFormat->courseid   = $instance->id;
                    $instanceFormat->format     = $instance->format;
                    $instanceFormat->name       = 'ratings';
                    $instanceFormat->value      = 1;

                    // Execute
                    $DB->insert_record('course_format_options',$instanceFormat);
                }//for_Rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_ratings_format_option
}//CoursePage_Upgrade