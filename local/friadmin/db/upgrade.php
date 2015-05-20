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
 * Page module upgrade code
 *
 * This file keeps track of upgrades to
 * the resource module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_local_friadmin_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015052000) {
        $blocks = null;
        $instanceBlock = null;

        /* Get the instance for the Frikomport Block for all friadmin pages*/
        $sql = " SELECT		*
                     FROM		{block_instances}
                     WHERE		blockname 			= 'frikomport'
                        AND		pagetypepattern		LIKE '%local-friadmin%' ";
        /* Execute  */
        $blocks = $DB->get_records_sql($sql);

        if ($blocks) {
            // Loop through and remove them from all pages
            foreach ($blocks as $block) {
                blocks_delete_instance($block);
            }
        }//deleted

        /* Create Instance Block Frikomport --> local friadmin plugin */
        /* local-friadmin-courselist */
        $instanceBlock = new stdClass();
        $instanceBlock->blockname = 'frikomport';
        $instanceBlock->parentcontextid = 1;
        $instanceBlock->showinsubcontexts = 0;
        $instanceBlock->pagetypepattern = 'local-friadmin-courselist';
        $instanceBlock->defaultregion = 'side-pre';
        $instanceBlock->defaultweight = 0;
        /* Execute  */
        $DB->insert_record('block_instances', $instanceBlock);

        /* local-friadmin-coursedetail */
        $instanceBlock = new stdClass();
        $instanceBlock->blockname = 'frikomport';
        $instanceBlock->parentcontextid = 1;
        $instanceBlock->showinsubcontexts = 0;
        $instanceBlock->pagetypepattern = 'local-friadmin-coursedetail';
        $instanceBlock->defaultregion = 'side-pre';
        $instanceBlock->defaultweight = 0;
        /* Execute  */
        $DB->insert_record('block_instances', $instanceBlock);

        // Plugin savepoint reached.
        upgrade_plugin_savepoint(true, 2015052000, 'local', 'friadmin');
    }

    return true;
}
