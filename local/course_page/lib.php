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
 * Course Home Page - Library
 *
 * Description
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate      13/09/2014
 * @author          eFaktor     (fbv)
 *
 */
function local_course_page_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/course_page:manage', context_course::instance($PAGE->course->id))) {
        return;
    }

    $format_options = course_get_format($PAGE->course)->get_format_options();
    if (array_key_exists('homepage',$format_options) && ($format_options['homepage'])) {
        if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
            $home_url = new moodle_url('/local/course_page/home_page.php',array('id' => $PAGE->course->id,'start'=>0));
            $home_url->param('sesskey', sesskey());
            if ($PAGE->user_is_editing()) {
                $home_url->param('edit', 'on');
                $home_url->param('show', '1');
            } else {
                $home_url->param('edit', 'off');
                $home_url->param('show', '0');
            }
            $str_edit = get_string('edit_home_page','local_course_page');
            $home_node = navigation_node::create($str_edit,
                                                 $home_url,
                                                 navigation_node::TYPE_SETTING,'homepage',
                                                 'homepage',
                                                 new pix_icon('i/settings', '')
            );
            if ($PAGE->url->compare($home_url, URL_MATCH_BASE)) {
                $home_node->make_active();
            }
            $settingnode->add_node($home_node,'editsettings');
        }//if_settingnode
    }
}//local_course_page_extends_setting_navigation
