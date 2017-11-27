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
 * Settings for the course search.
 *
 * @package    local_course_search
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_course_search',
        get_string('pluginname', 'local_course_search'));
    $ADMIN->add('localplugins', $settings);

    $options = array('0' => get_string('none', 'local_course_search'));

    $tagcollid = \core_tag_area::get_collection('core', 'course');
    $metatags = \local_tag\collection::get_meta_tags($tagcollid, \local_tag\tag::get_meta_group_prefix());

    if (!is_null($metatags)) {
        $groupprefix = \local_tag\tag::get_meta_group_prefix();
        $result = array();

        foreach ($metatags as $tag) {
            $result[$tag->id] = \local_tag\tag::get_meta_tag_stripped_name($tag->rawname, $groupprefix);
        }

        \core_collator::asort($result);
        $options = $options + $result;
    }

    $settings->add(new admin_setting_configselect('local_course_search/selected_tag_group',
        get_string('courseformatgroup', 'local_course_search'),
        get_string('courseformatgroupdesc', 'local_course_search'),
        0, $options));
}
