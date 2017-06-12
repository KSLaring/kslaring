<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade code for install
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade this plugin instance.
 *
 * @param int $oldversion The old version of the assign module
 *
 * @return bool
 */
function xmldb_local_course_search_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017060400) {
        $tablename = 'local_course_search_presel';
        if (!$dbman->table_exists(new xmldb_table($tablename))) {
            $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', $tablename);
        }

        // Course search savepoint reached.
        upgrade_plugin_savepoint(true, 2017060400, 'local', 'courses_search');
    }

    return true;
}
