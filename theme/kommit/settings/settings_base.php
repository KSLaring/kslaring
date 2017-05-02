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
 * Moodle's kommit theme central settings, included by theme settings.php
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package    theme_kommit
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/* Generic Settings */
$temp = new admin_settingpage($themename . '_generic',
    get_string('genericsettings', 'theme_kommit'));

//// Invert Navbar to dark background.
//$name = $themename . '/invert';
//$title = get_string('invert', 'theme_kommit');
//$description = get_string('invertdesc', 'theme_kommit');
//$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
//$setting->set_updatedcallback('theme_reset_all_caches');
//$temp->add($setting);
//
//// Logo file setting.
//$name = $themename . '/logo';
//$title = get_string('logo', 'theme_kommit');
//$description = get_string('logodesc', 'theme_kommit');
//$setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
//$setting->set_updatedcallback('theme_reset_all_caches');
//$temp->add($setting);

// Custom CSS file.
$name = $themename . '/customcss';
$title = get_string('customcss', 'theme_kommit');
$description = get_string('customcssdesc', 'theme_kommit');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Footnote setting.
$name = $themename . '/footnote';
$title = get_string('footnote', 'theme_kommit');
$description = get_string('footnotedesc', 'theme_kommit');
$default = '';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Footnote setting.
$name = $themename . '/useadminlogin';
$title = get_string('useadminlogin', 'theme_kommit');
$description = get_string('useadminlogindesc', 'theme_kommit');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$ADMIN->add($themename, $temp);


/* Frontpage Settings */
$temp = new admin_settingpage($themename . '_frontpage',
    get_string('frontpagesettings', 'theme_kommit'));

// Hero Image.
$name = $themename . '/heroimg';
$title = get_string('heroimage', 'theme_kommit');
$description = get_string('heroimagedesc', 'theme_kommit');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'heroimg');
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Hero headline.
$name = $themename . '/heroheadline';
$title = get_string('heroheadline', 'theme_kommit');
$description = get_string('heroheadlinedesc', 'theme_kommit');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Hero headlead.
$name = $themename . '/herolead';
$title = get_string('herolead', 'theme_kommit');
$description = get_string('heroleaddesc', 'theme_kommit');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Hero herolinktext.
$name = $themename . '/herolinktext';
$title = get_string('herolinktext', 'theme_kommit');
$description = get_string('herolinktextdesc', 'theme_kommit');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Hero herolink.
$name = $themename . '/herolink';
$title = get_string('herolink', 'theme_kommit');
$description = get_string('herolinkdesc', 'theme_kommit');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$ADMIN->add($themename, $temp);
