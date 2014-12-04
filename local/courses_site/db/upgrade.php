<?php
/**
 * Local Block Courses Site database upgrade
 *
 * @package         local
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    2014-11-11
 * @author          efaktor     (uh)
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_courses_site_upgrade($oldversion) {
    global $DB;

    $db_man = $DB->get_manager();

    if ($oldversion < 2014120400) {
        /* New Field -- picturetitle */
        $table = new xmldb_table('block_courses_site');
        $fieldPicTitle = new xmldb_field('picturetitle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'picture');

        if (!$db_man->field_exists($table, $fieldPicTitle)) {
            $db_man->add_field($table, $fieldPicTitle);
        }//if_not_exists

        upgrade_plugin_savepoint(true, 2014120400, 'local', 'courses_site');
    }//if_oldversion

    return true;
}//xmldb_local_courses_site_upgrade
