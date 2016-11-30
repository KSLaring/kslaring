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
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_mcaedescription'] = 'This method provides a way to automatically enrol users into cohort.';
$string['pluginname'] = 'Autoenrol cohort';
$string['auth_fieldlocks_help'] = ' ';
$string['auth_mainrule_fld'] = 'Main template. 1 template per line.';
$string['auth_secondrule_fld'] = 'Empty field text';
$string['auth_replace_arr'] = 'Replace array. 1 value per line, format: old_val|new_val';
$string['auth_delim'] = 'Delimiter';
$string['auth_delim_help'] = 'Different OS use different end of line delimiters.<br>In Windows it\'s usually CR+LF<br>In Linux - LF<br>etc.<br>If the module does not work, try to change this value.';
$string['auth_donttouchusers'] = 'Ignore users';
$string['auth_donttouchusers_help'] = 'Comma-separated usernames.';
$string['auth_enableunenrol'] = 'Enable / Disable automatic unenrol';
$string['auth_tools_help'] = 'Unenrol function only works with cohorts associated with the module. With <a href="{$a->url}" target="_blank">this tool</a> you can convert / view / delete all cohorts you have.';
$string['auth_cohorttoolmcae'] = 'Cohort operations';
$string['auth_cohortviewmcae'] = 'Cohort viewer';
$string['auth_selectcohort'] = 'Select cohort';
$string['auth_username'] = 'User name';
$string['auth_link'] = 'Link';
$string['auth_userlink'] = 'View users';
$string['auth_userprofile'] = 'User profile &gt;&gt;';
$string['auth_emptycohort'] = 'Empty cohort';
$string['auth_viewcohort'] = 'Cohort view';
$string['auth_total'] = 'Total';
$string['auth_cohortname'] = 'Cohort name';
$string['auth_component'] = 'Component';
$string['auth_count'] = 'Count';
$string['auth_cohortoper_help'] = '<p>Select cohorts you want to convert.</p><p><b>NOTE:</b> <i>You <b>cannot</b> edit converted cohorts manually!</i></p><p>Backup your database!!!</p>';
$string['auth_profile_help'] = 'Available templates';
