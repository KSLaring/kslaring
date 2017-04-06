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

require_login();

$friadmin = new local_friadmin\friadmin();

// Basic page init - set context and pagelayout.
$friadmin->init_page();

$type = optional_param('type', -1, PARAM_INT);
$savedtype = optional_param('temptype', -1, PARAM_INT);

if ($type === -1) {
    if ($savedtype !== -1) {
        $type = $savedtype;
    } else {
        $type = TEMPLATE_TYPE_EVENT;
    }
}

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
// Get the renderer for this plugin.
$output = $PAGE->get_renderer('local_friadmin');

// Prepare the renderables for the page and the page areas.
$page = new local_friadmin_coursetemplate_page($type);
$select = new local_friadmin_coursetemplate_select($type);
$linklist = new local_friadmin_coursetemplate_linklist();

$friadmin->set_coursetemplate_references($page, $select, $linklist, $output);

$friadmin->setup_coursetemplate_page();
$friadmin->display_coursetemplate_page();
