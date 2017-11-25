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
 * The course list page
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $USER,$PAGE,$OUTPUT,$CFG;

$courseid = required_param('id', PARAM_INT);

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
$friadmin = new local_friadmin\friadmin();

// Basic page init - set context and pagelayout
$friadmin->init_page();

/**
 * @updateDate  22/06/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Check if the user is super user
 */
if (!$friadmin->__get('superuser')) {
    print_error('nopermissions', 'error', '', 'block/frikomport:view');
}//if_superuser

// In Moodle 2.7 renderers and renderables can't be loaded via namespaces
// Get the renderer for this plugin
$output = $PAGE->get_renderer('local_friadmin');

// Prepare the renderables for the page and the page areas
$page = new local_friadmin_coursedetail_page();
$table = new local_friadmin_coursedetail_table($courseid);
$linklist = new local_friadmin_coursedetail_linklist($courseid);

$friadmin->set_coursedetail_references($page, $table, $linklist, $output);

$friadmin->setup_coursedetail_page();
$friadmin->display_coursedetail_page();
