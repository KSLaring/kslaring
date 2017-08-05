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
 * @subpackage tag
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_tag_add_course_tags' => array(
        'classname'       => 'local_tag\external\tag',
        'methodname'      => 'add_course_tags',
        'description'     => 'Add the given course tags',
        'type'            => 'write',
        'capabilities'    => 'moodle/site:config',
        'ajax'            => true,
    ),
    'local_tag_group_tags' => array(
        'classname'       => 'local_tag\external\tag',
        'methodname'      => 'group_tags',
        'description'     => 'Sort the tag list into the given group',
        'type'            => 'write',
        'capabilities'    => 'moodle/site:config',
        'ajax'            => true,
    ),
);
