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
 * Classroom Frikomport Format - Update Script
 *
 * Description
 *
 * @package             course/format
 * @subpackage          classroom_frikomport/db
 * @copyright           2010 eFaktor
 * @license             http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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