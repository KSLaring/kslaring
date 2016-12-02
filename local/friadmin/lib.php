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
 * The friadmin lib with the plugin defines
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('TEMPLATE_TYPE_EVENT', 0);
define('TEMPLATE_TYPE_NETCOURSE', 1);

function local_friadmin_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('moodle/backup:backupcourse', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $strduplicatecourse = get_string('duplicatecourse', 'local_friadmin');
        $url = new moodle_url('/local/friadmin/duplicatecourse.php', array('id' => $PAGE->course->id));
        $duplicatecoursenode = navigation_node::create(
            $strduplicatecourse,
            $url,
            navigation_node::NODETYPE_LEAF,
            //navigation_node::TYPE_SETTING,
            'friadmin',
            'friadmin',
            new pix_icon('t/addcontact', $strduplicatecourse)
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $duplicatecoursenode->make_active();
        }
        $settingnode->add_node($duplicatecoursenode, 'backup');
    }
}
