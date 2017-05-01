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
 * The my settings page with user specific setting options.
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$friadmin = new local_friadmin\friadmin();

// Basic page init - set context and pagelayout.
$friadmin->init_page();

//Check if the user has the capability to view the page.
if (!$friadmin->__get('superuser')) {
    print_error('nopermissions', 'error', '', 'block/frikomport:view');
}//if_superuser

// In Moodle 2.7 renderers and renderables can't be loaded via namespaces
// Get the renderer for this plugin.
/* @var $PAGE moodle_page The Moodle page object. */
$output = $PAGE->get_renderer('local_friadmin');

// Require needed JavaScript.
$PAGE->requires->js_call_amd('local_friadmin/categoryselect', 'init');

// Prepare the renderables for the page and the page areas.
$page = new local_friadmin_mysettings_page();
$select = new local_friadmin_mysettings_select();

$friadmin->set_mysettings_references($page, $select, $output);

$friadmin->setup_mysettings_page();
$friadmin->display_mysettings_page();
