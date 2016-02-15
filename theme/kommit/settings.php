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
 * Moodle's kommit theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_kommit
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = null;

if (is_siteadmin()) {
    $ADMIN->add('themes', new admin_category('theme_kommit', 'KommIT'));

    /* Generic Settings */
    $temp = new admin_settingpage('theme_kommit_generic',
        get_string('genericsettings', 'theme_kommit'));

    // Invert Navbar to dark background.
    $name = 'theme_kommit/invert';
    $title = get_string('invert', 'theme_kommit');
    $description = get_string('invertdesc', 'theme_kommit');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Logo file setting.
    $name = 'theme_kommit/logo';
    $title = get_string('logo', 'theme_kommit');
    $description = get_string('logodesc', 'theme_kommit');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Custom CSS file.
    $name = 'theme_kommit/customcss';
    $title = get_string('customcss', 'theme_kommit');
    $description = get_string('customcssdesc', 'theme_kommit');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Footnote setting.
    $name = 'theme_kommit/footnote';
    $title = get_string('footnote', 'theme_kommit');
    $description = get_string('footnotedesc', 'theme_kommit');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $ADMIN->add('theme_kommit', $temp);


    /* Frontpage Settings */
    $temp = new admin_settingpage('theme_kommit_frontpage',
        get_string('frontpagesettings', 'theme_kommit'));

    // Hero Image.
    $name = 'theme_kommit/heroimg';
    $title = get_string('heroimage', 'theme_kommit');
    $description = get_string('heroimagedesc', 'theme_kommit');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'heroimg');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $ADMIN->add('theme_kommit', $temp);
}
