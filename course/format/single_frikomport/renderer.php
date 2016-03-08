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
 * Single Activity Frikomport Format - Renderer
 *
 * Description
 *
 * @package             course
 * @subpackage          format/single_frikomport
 * @copyright           2010 eFaktor
 *
 * @creationDate        20/04/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

class format_single_frikomport_renderer extends plugin_renderer_base {

    /**
     * Displays the activities list in cases when course view page is not
     * redirected to the activity page.
     *
     * @param stdClass $course record from table course
     * @param bool $orphaned if false displays the main activity (if present)
     *     if true displays all other activities
     */
    public function display($course, $orphaned) {
        $courserenderer = $this->page->get_renderer('core', 'course');
        $output = '';
        $modinfo = get_fast_modinfo($course);
        if ($orphaned) {
            if (!empty($modinfo->sections[1])) {
                $output .= $this->output->heading(get_string('orphaned', 'format_single_frikomport'), 3, 'sectionname');
                $output .= $this->output->box(get_string('orphanedwarning', 'format_single_frikomport'));
                $output .= $courserenderer->course_section_cm_list($course, 1, 1);
            }
        } else {
            $output .= $courserenderer->course_section_cm_list($course, 0, 0);
            if (empty($modinfo->sections[0]) && course_get_format($course)->activity_has_subtypes()) {
                // Course format was unable to automatically redirect to add module page.
                $output .= $courserenderer->course_section_add_cm_control($course, 0, 0);
            }
        }
        return $output;
    }
}
