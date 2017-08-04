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
 * @package    local
 * @subpackage course_search
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_course_search_get_course_data' => array(
        'classname'       => 'local_course_search\external\coursesearch',
        'methodname'      => 'get_course_data',
        'description'     => 'Get the course data',
        'type'            => 'read',
        'capabilities'    => 'moodle/course:view',
        'ajax'            => true,
    ),
    'local_course_search_get_all_course_tags' => array(
        'classname'       => 'local_course_search\external\coursesearch',
        'methodname'      => 'get_all_course_tags',
        'description'     => 'Get all course tags data',
        'type'            => 'read',
        'capabilities'    => 'moodle/course:view',
        'ajax'            => true,
    ),
    'local_course_search_save_search_criteria' => array(
        'classname'       => 'local_course_search\external\coursesearch',
        'methodname'      => 'save_search_criteria',
        'description'     => 'Save the user\'s preselected search criteria',
        'type'            => 'write',
        'capabilities'    => 'moodle/course:view',
        'ajax'            => true,
    ),
    'local_course_search_get_user_search_criteria' => array(
        'classname'       => 'local_course_search\external\coursesearch',
        'methodname'      => 'get_user_search_criteria',
        'description'     => 'Get the saved user selected tags',
        'type'            => 'read',
        'capabilities'    => 'moodle/course:view',
        'ajax'            => true,
    ),
);
