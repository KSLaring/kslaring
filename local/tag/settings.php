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
 * Settings for the meat tags.
 *
 * @package    local_tags
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_tag', get_string('pluginname', 'local_tag'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading('local_tag_metaheading', get_string('metaheading', 'local_tag'),
            get_string('metaheading_desc', 'local_tag')));
    $settings->add(new admin_setting_configtext('tag_metaprefix', get_string('metaprefix', 'local_tag'),
            get_string('metaprefix_desc', 'local_tag'), 'Meta_'));
    $settings->add(new admin_setting_configtext('tag_metagroupprefix',
            get_string('metagroupprefix', 'local_tag'), get_string('metaprefix_desc', 'local_tag'), 'Meta_group_'));
    $settings->add(new admin_setting_configtext('tag_metaoptionprefix',
            get_string('metaoptionprefix', 'local_tag'), get_string('metaoptionprefix_desc', 'local_tag'), 'Meta_option_'));
    $settings->add(new admin_setting_configtext('tag_metaoptionsingleselect',
            get_string('metaoptionsingleselect', 'local_tag'),
            get_string('metaoptionsingleselect_desc', 'local_tag'), 'Meta_option_singleselect'));

    $ADMIN->add(
            'appearance',
            new admin_externalpage(
                    'local_tag/edittags',
                    get_string('editgrouptags_menuentry', 'local_tag'),
                    new moodle_url('/local/tag/edit_group_tags.php'),
                    'moodle/site:config'
            )
    );
}
