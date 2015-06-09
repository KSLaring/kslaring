<?php
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
defined('MOODLE_INTERNAL') || die();

function xmldb_local_friadmin_install() {
    /* Variables    */
    global $DB;
    $blocks         = null;
    $instanceBlock  = null;

    /* Get the instance for the Frikomport Block */
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
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-friadmin-courselist';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);

    /* local-friadmin-coursedetail */
    $instanceBlock = new stdClass();
    $instanceBlock->blockname           = 'frikomport';
    $instanceBlock->parentcontextid     = 1;
    $instanceBlock->showinsubcontexts   = 0;
    $instanceBlock->pagetypepattern     = 'local-friadmin-coursedetail';
    $instanceBlock->defaultregion       = 'side-pre';
    $instanceBlock->defaultweight       = 0;
    /* Execute  */
    $DB->insert_record('block_instances',$instanceBlock);
}
