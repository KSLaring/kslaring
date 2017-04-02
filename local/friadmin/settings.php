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
 * The friadmin settings page
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_friadmin',
        get_string('pluginname', 'local_friadmin'));
    $ADMIN->add('localplugins', $settings);

    // Template directory.
    $options = array('0' => get_string('coursetemplate_cat_select', 'local_friadmin'));
    /**
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get the categories where the user has permissions as super user
     */
    $catlist = local_friadmin_helper::getMyCategories();

    if (!is_null($catlist)) {
        asort($catlist);
        $options = $options + $catlist;
    }

    $settings->add(new admin_setting_configselect('local_friadmin/template_category',
        get_string('coursetemplate_cat', 'local_friadmin'),
        get_string('coursetemplate_cat_desc', 'local_friadmin'), 0, $options));


    // Add a course format list to select the template formats from.
    $courseformats = get_sorted_course_formats(true);
    $options = array();
    foreach ($courseformats as $courseformat) {
        $options[$courseformat] = get_string('pluginname', "format_$courseformat");
    }
    core_collator::asort($options);

    $settings->add(new admin_setting_configmultiselect('local_friadmin/template_list',
        get_string('courseformats', 'local_friadmin'),
        get_string('courseformatsdesc', 'local_friadmin'),
        array(), $options));
}
