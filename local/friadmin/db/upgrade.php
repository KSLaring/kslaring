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

/**
 * @updateDate      16/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Integrate the Course Location plugin
 */
defined('MOODLE_INTERNAL') || die;

function xmldb_local_friadmin_upgrade($oldversion) {
    /* Variables    */
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015062206) {
        /* Add Instance FriAdmin - Frikomport Block */
        /* Add Instance Course Locations - Frikomport Block */
        /* Add Instance Frikomport Block  - Course Edit and Index */
        FriAdmin_UpdateHandler::AddInstance_FrikomportBlock();

        // Plugin savepoint reached.
        upgrade_plugin_savepoint(true, 2015062206, 'local', 'friadmin');
    }//if_odlversion

    return true;
}

/**
 * Class FriAdmin_Handler
 *
 * @creationDate    16/06/2015
 * @author          eFktor      (fbv)
 *
 * Description
 * To manage all the actions during the installation
 */
class FriAdmin_UpdateHandler {
    /* ******************/
    /* PUBLIC FUNCTIONS */
    /* ******************/

    /**
     * @throws          Exception
     *
     * @updateDate      16/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create Instance block Firkomport --  Fridadmin  Plugin
     * Create Instance block Firkomport --  Fridadmin - Course Locations Plugin
     * Create Instance Frikomport Block  - Course Edit and Index
     */
    public static function AddInstance_FrikomportBlock() {
        /* Variables    */
        global $DB;
        $blocks             = null;
        $instanceBlock      = null;

        try {
            /* Get Instance Frikomport Block    */
            $sql = " SELECT		*
                     FROM		{block_instances}
                     WHERE		blockname 			= 'frikomport'
                        AND		pagetypepattern		LIKE '%local-friadmin%' ";
            /* Execute  */
            $blocks = $DB->get_records_sql($sql);
            if ($blocks) {
                // Loop through and remove them from the My Moodle page.
                foreach ($blocks as $block) {
                    blocks_delete_instance($block);
                }
            }//deleted

            /* Create Instance Block Frikomport --> local friadmin plugin */
            /* local-friadmin-courselist */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-courselist';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-friadmin-coursedetail */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-coursedetail';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-friadmin-coursedetail */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-coursetemplate';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* Create Instance Block Frikomport --> Course Locations Plugin */
            /* local-course_locations-index             */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-index';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-course_locations  */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-course_locations';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-locations         */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-locations';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-view              */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-view';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-add_location      */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-add_location';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-edit_location     */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-edit_location';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-delete_location   */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-delete_location';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* Get Instance Frikomport Block  - Course Edit and Index  */
            $sql = " SELECT		*
                     FROM		{block_instances}
                     WHERE		blockname 			= 'frikomport'
                        AND		(pagetypepattern		LIKE '%course-edit%'
                                 OR
                                 pagetypepattern		LIKE '%course-index%'
                                )";
            /* Execute  */
            $blocks = $DB->get_records_sql($sql);
            if ($blocks) {
                // Loop through and remove them from the My Moodle page.
                foreach ($blocks as $block) {
                    blocks_delete_instance($block);
                }
            }//deleted

            /* Course Edit  */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 3;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'course-edit';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* Course Index  */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'course-index';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* Get Instance Frikomport Block - My Page  */
            $sql = " SELECT		*
                     FROM		{block_instances}
                     WHERE		blockname 			= 'frikomport'
                        AND		pagetypepattern		LIKE '%my-index%' ";
            /* Execute  */
            $blocks = $DB->get_records_sql($sql);
            if ($blocks) {
                // Loop through and remove them from the My Moodle page.
                foreach ($blocks as $block) {
                    blocks_delete_instance($block);
                }
            }//deleted

            /* My Index (My Page)   */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 1;
            $instanceBlock->pagetypepattern     = 'my-index';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddInstance_FrikomportBlock
}//FriAdmin_UpdateHandler
