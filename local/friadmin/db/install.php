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
 * Local friadmin - Install Script
 *
 * Add the frikomport block to all friadmin pages
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
defined('MOODLE_INTERNAL') || die();

function xmldb_local_friadmin_install() {
    /* Variables    */
    global $DB;
    $tblCourseLocations        = null;


    try {
        /* The block has to be visible in all site */
        FriAdmin_Handler::AddInstance_FrikomportBlock();

        /* Course Locations Table           */
        $db_man = $DB->get_manager();
        if (!$db_man->table_exists('course_locations')) {
            /* Structure table  */
            $tblCourseLocations = FriAdmin_Handler::GetTable_CourseLocations();

            /* Create DB        */
            $db_man->create_table($tblCourseLocations);
        }//if_tbl_courseLocations
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
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
class FriAdmin_Handler {
    /* ******************/
    /* PUBLIC FUNCTIONS */
    /* ******************/

    /**
     * @throws          Exception
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * The block has to be visible in all site
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
                     WHERE		blockname 			= 'frikomport' ";

            /* Execute  */
            $blocks = $DB->get_records_sql($sql);
            if ($blocks) {
                // Loop through and remove them from the My Moodle page.
                foreach ($blocks as $block) {
                    blocks_delete_instance($block);
                }
            }//deleted

            /* Block has to be visible in all site    */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 1;
            $instanceBlock->pagetypepattern     = '*';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = 0;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddInstance_FrikomportBlock

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
    public static function AddInstance_FrikomportBlock_old() {
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

            /* local-friadmin-newcourse */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-newcourse';
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
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-locations ';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-view              */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-view ';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-add_location      */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-add_location ';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-edit_location     */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-edit_location ';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* local-course_locations-delete_location   */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'local-friadmin-course_locations-delete_location ';
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
            $instanceBlock->pagetypepattern     = 'course-edit ';
            $instanceBlock->defaultregion       = 'side-pre';
            $instanceBlock->defaultweight       = -10;
            /* Execute  */
            $DB->insert_record('block_instances',$instanceBlock);

            /* Course Index  */
            $instanceBlock = new stdClass();
            $instanceBlock->blockname           = 'frikomport';
            $instanceBlock->parentcontextid     = 1;
            $instanceBlock->showinsubcontexts   = 0;
            $instanceBlock->pagetypepattern     = 'course-index ';
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

    /**
     * @return          xmldb_table
     * @throws          Exception
     *
     * @creationDate    28/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the structure table for course_locations
     */
    public static function GetTable_CourseLocations() {
        /* Variables    */
        $tbl_courseLocations = null;

        try {
            /* Create Table */
            $tbl_courseLocations = new xmldb_table('course_locations');

            /* Add Fields   */
            /* Id               --> Primary Key. Auto numeric                       */
            $tbl_courseLocations->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* levelZero        --> Foreign Key --> report_gen_companydata  --> id  */
            $tbl_courseLocations->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelOne         --> Foreign Key --> report_gen_companydata  --> id  */
            $tbl_courseLocations->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* name             --> Location Name. Not null.                        */
            $tbl_courseLocations->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* description      --> Location Description                            */
            $tbl_courseLocations->add_field('description',XMLDB_TYPE_TEXT,null,null, null, null,null);
            /* url              --> URL Description                                 */
            $tbl_courseLocations->add_field('url',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* floor            --> Floor. Not Null                                 */
            $tbl_courseLocations->add_field('floor',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* room             --> Room number. Not Null                           */
            $tbl_courseLocations->add_field('room',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* seats            --> Maximum number of seats                         */
            $tbl_courseLocations->add_field('seats',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* street          --> Location Address. Not Null                      */
            $tbl_courseLocations->add_field('street',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* Post code        */
            $tbl_courseLocations->add_field('postcode',XMLDB_TYPE_CHAR,'10',null, null, null,null);
            /* City             */
            $tbl_courseLocations->add_field('city',XMLDB_TYPE_CHAR,'100',null, null, null,null);
            /* urlmap           --> URL Map to the address location                 */
            $tbl_courseLocations->add_field('urlmap',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* post             --> Post address location.                          */
            $tbl_courseLocations->add_field('post',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* contact          --> Contact person                                  */
            $tbl_courseLocations->add_field('contact',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* phone            --> Contact phone                                   */
            $tbl_courseLocations->add_field('phone',XMLDB_TYPE_CHAR,'25',null, null, null,null);
            /* email            --> Contact eMail                                   */
            $tbl_courseLocations->add_field('email',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* comments         --> Comments                                        */
            $tbl_courseLocations->add_field('comments',XMLDB_TYPE_TEXT,null,null, null, null,null);
            /* active           --> Location available or not. Not Null. Default 1  */
            $tbl_courseLocations->add_field('activate',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,1);
            /* createdby        --> Foreign Key --> user --> userid                 */
            $tbl_courseLocations->add_field('createdby',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* timecreated      --> Time created. Not Null.                         */
            $tbl_courseLocations->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* timemodified     --> Time Modified.                                  */
            $tbl_courseLocations->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);


            /* Add Keys */
            $tbl_courseLocations->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tbl_courseLocations->add_key('levelzero',XMLDB_KEY_FOREIGN,array('levelzero'), 'report_gen_companydata', array('id'));
            $tbl_courseLocations->add_key('levelone',XMLDB_KEY_FOREIGN,array('levelone'), 'report_gen_companydata', array('id'));
            $tbl_courseLocations->add_key('createdby',XMLDB_KEY_FOREIGN,array('createdby'), 'user', array('id'));

            return $tbl_courseLocations;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTable_CourseLocations
}//FriAdmin_Handler
