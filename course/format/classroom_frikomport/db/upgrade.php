<?php
/**
 * Classroom Frikomport Format - Update Script
 *
 * Description
 *
 * @package             course/format
 * @subpackage          classroom_frikomport/db
 * @copyright           2010 eFaktor
 *
 * @creationDate        15/09/2015
 * @author              eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_format_classroom_frikomport_upgrade($oldVersion) {
    /* Variables    */
    global $DB;
    $instanceBlock  = null;

    if ($oldVersion <= 2015091504) {
        /* Get Courses with Classroom Firkomport format */
        /* Add to the course the Classroom block        */
        $rdo = $DB->get_records('course',array('format' => 'classroom_frikomport'),'id');
        if ($rdo) {
            foreach ($rdo as $instance) {
                /* Instance Block   */
                $instanceBlock = new stdClass();
                $instanceBlock->blockname           = 'classroom';
                $instanceBlock->parentcontextid     = CONTEXT_COURSE::instance($instance->id)->id;
                $instanceBlock->showinsubcontexts   = 0;
                $instanceBlock->pagetypepattern     = 'course-view-*';
                $instanceBlock->defaultregion       = 'side-post';
                $instanceBlock->defaultweight       = 0;

                /* Add Instance Classroom Format Block  */
                $DB->insert_record('block_instances',$instanceBlock);
            }//for_Each_rdo
        }//if_rdo
    }//if_oldVersion

    return true;
}//xmldb_format_classroom_frikomport_upgrade