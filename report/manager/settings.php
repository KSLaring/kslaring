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
 * Report Competence Manager - Settings
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate      06/09/2012
 * @author          eFaktor     (fbv)
 *
 * Add link to site administration.
 *
 */

defined('MOODLE_INTERNAL') || die();

$url = new moodle_url('/report/manager/index.php');
//$CFG->wwwroot.'/report/manager/index.php'
$ADMIN->add('reports',
        new admin_externalpage('manager', get_string('pluginname','report_manager'),
        $url));

//Indicates That we only want to display link
$settings->add(new admin_setting_heading('report_manager_report', '', get_string('report_manager', 'report_manager')));

