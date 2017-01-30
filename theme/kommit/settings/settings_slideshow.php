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
$temp = new admin_settingpage($themename . '_slideshow',
    get_string('slideshowsettings', 'theme_kommit'));

// Toggle slide show.
$name = $themename . '/toggleslideshow';
$title = get_string('toggleslideshow', 'theme_kommit');
$description = get_string('toggleslideshowdesc', 'theme_kommit');
$alwaysdisplay = get_string('alwaysdisplay', 'theme_kommit');
$displaybeforelogin = get_string('displaybeforelogin', 'theme_kommit');
$displayafterlogin = get_string('displayafterlogin', 'theme_kommit');
$dontdisplay = get_string('dontdisplay', 'theme_kommit');
$default = 1;
$choices = array(1 => $alwaysdisplay, 2 => $displaybeforelogin,
    3 => $displayafterlogin, 0 => $dontdisplay);
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Number of slides.
$name = $themename . '/numberofslides';
$title = get_string('numberofslides', 'theme_kommit');
$description = get_string('numberofslides_desc', 'theme_kommit');
$default = 2;
$choices = array(
    1 => '1',
    2 => '2',
    3 => '3',
    4 => '4',
    5 => '5',
    6 => '6',
    7 => '7',
    8 => '8'
);
$temp->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

// Hide slide show on phones.
$name = $themename . '/hideontablet';
$title = get_string('hideontablet', 'theme_kommit');
$description = get_string('hideontabletdesc', 'theme_kommit');
$default = false;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Hide slide show on tablet.
$name = $themename . '/hideonphone';
$title = get_string('hideonphone', 'theme_kommit');
$description = get_string('hideonphonedesc', 'theme_kommit');
$default = true;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Slide interval.
$name = $themename . '/slideinterval';
$title = get_string('slideinterval', 'theme_kommit');
$description = get_string('slideintervaldesc', 'theme_kommit');
$default = '5000';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

$numberofslides = get_config('theme_kommit', 'numberofslides');
for ($i = 1; $i <= $numberofslides; $i++) {
    // This is the descriptor for a slide.
    $name = $themename . '/slide' . $i . 'info';
    $heading = get_string('slideno', 'theme_kommit', array('slide' => $i));
    $information = get_string('slidenodesc', 'theme_kommit', array('slide' => $i));
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Image.
    $name = $themename . '/slide' . $i . 'image';
    $title = get_string('slideimage', 'theme_kommit');
    $description = get_string('slideimagedesc', 'theme_kommit');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide' . $i . 'image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption title.
    $name = $themename . '/slide' . $i . 'captiontitle';
    $title = get_string('slidecaptiontitle', 'theme_kommit');
    $description = get_string('slidecaptiontitledesc', 'theme_kommit');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default,
        PARAM_RAW, '60');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption text.
    $name = $themename . '/slide' . $i . 'caption';
    $title = get_string('slidecaption', 'theme_kommit');
    $description = get_string('slidecaptiondesc', 'theme_kommit');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default,
        PARAM_RAW, '70', '8');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Link button text.
    $name = $themename . '/slide' . $i . 'linktext';
    $title = get_string('slidelinktext', 'theme_kommit');
    $description = get_string('slidelinktextdesc', 'theme_kommit');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default,
        PARAM_RAW, '60');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = $themename . '/slide' . $i . 'url';
    $title = get_string('slideurl', 'theme_kommit');
    $description = get_string('slideurldesc', 'theme_kommit');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default,
        PARAM_RAW, '60');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL target.
    $name = $themename . '/slide' . $i . 'target';
    $title = get_string('slideurltarget', 'theme_kommit');
    $description = get_string('slideurltargetdesc', 'theme_kommit');
    $target1 = get_string('slideurltargetself', 'theme_kommit');
    $target2 = get_string('slideurltargetnew', 'theme_kommit');
    $target3 = get_string('slideurltargetparent', 'theme_kommit');
    $default = '_self';
    $choices = array('_self' => $target1, '_blank' => $target2, '_parent' => $target3);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
}

$ADMIN->add($themename, $temp);
