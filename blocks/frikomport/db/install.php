<?php
/**
 * Block Frikomport - Install Script
 *
 * Description
 *
 * @package             block
 * @subpackage          frikomport/db
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        17/05/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_block_frikomport_install() {
    /* Variables    */
    global $DB;
    $blocks         = null;
    $instanceBlock  = null;

    /* Get Instance Frikomport Block    */
    $sql = " SELECT		*
             FROM		{block_instances}
             WHERE		blockname 			= 'frikomport'
                AND		pagetypepattern		LIKE '%local-course_locations%' ";
    /* Execute  */
    $blocks = $DB->get_records_sql($sql);
    if ($blocks) {
        // Loop through and remove them from the My Moodle page.
        foreach ($blocks as $block) {
            blocks_delete_instance($block);
        }
    }//deleted

    /* Create Instance Block Frikomport --> Course Locations Plugin */
    /* local-course_locations-index             */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-index';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-course_locations-course_locations  */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-course_locations';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-course_locations-locations         */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-locations ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-course_locations-view              */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-view ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-course_locations-add_location      */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-add_location ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-course_locations-edit_location     */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-edit_location ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-course_locations-delete_location   */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-course_locations-delete_location ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* Course Edit  */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 3;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'course-edit ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* Course Index  */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'course-index ';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);
}