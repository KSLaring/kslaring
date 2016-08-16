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
 * Version details
 *
 * @package    theme_adaptable
 * @copyright 2015 Jeremy Hopkins (Coventry University)
 * @copyright 2015 Fernando Acedo (3-bits.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */


// Analytics Section.
$temp = new admin_settingpage('theme_adaptable_analytics', get_string('analyticssettings', 'theme_adaptable'));
$temp->add(new admin_setting_heading('theme_adaptable_analytics', get_string('analyticssettingsheading', 'theme_adaptable'),
format_text(get_string('analyticssettingsdesc', 'theme_adaptable'), FORMAT_MARKDOWN)));

// Enable analytics.
$name = 'theme_adaptable/enableanalytics';
$title = get_string('enableanalytics', 'theme_adaptable');
$description = get_string('enableanalyticsdesc', 'theme_adaptable');
$default = false;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Number of Analytics entries.
$name = 'theme_adaptable/analyticscount';
$title = get_string('analyticscount', 'theme_adaptable');
$description = get_string('analyticscountdesc', 'theme_adaptable');
$default = THEME_ADAPTABLE_DEFAULT_ANALYTICSCOUNT;
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices1to12);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// If we don't have an analyticscount yet, default to the preset.
$analyticscount = get_config('theme_adaptable', 'analyticscount');
if (!$analyticscount) {
    $alertcount = THEME_ADAPTABLE_DEFAULT_ANALYTICSCOUNT;
}

for ($analyticsindex = 1; $analyticsindex <= $analyticscount; $analyticsindex ++) {
    // Alert Box Heading 1.
    $name = 'theme_adaptable/settingsanalytics' . $analyticsindex;
    $heading = get_string('analyticssettings', 'theme_adaptable', $analyticsindex);
    $setting = new admin_setting_heading($name, $heading, '');
    $temp->add($setting);

    // Alert Text 1.
    $name = 'theme_adaptable/analyticstext' . $analyticsindex;
    $title = get_string('analyticstext', 'theme_adaptable');
    $description = get_string('analyticstextdesc', 'theme_adaptable');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $temp->add($setting);

    $name = 'theme_adaptable/analyticsprofilefield' . $analyticsindex;
    $title = get_string('analyticsprofilefield', 'theme_adaptable');
    $description = get_string('analyticsprofilefielddesc', 'theme_adaptable');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_RAW);
    $temp->add($setting);
}

$ADMIN->add('theme_adaptable', $temp);
