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

// Basic page init - set context and pagelayout
$friadmin->init_page();

// In Moodle 2.7 renderers and renderables can't be loaded via namespaces
// Get the renderer for this plugin
$output = $PAGE->get_renderer('local_friadmin');

// Prepare the renderables for the page and the page areas
$page = new local_friadmin_courselist_page();
$filter = new local_friadmin_courselist_filter();
$table = new local_friadmin_courselist_table($page->data->url,
    $filter->userleveloneids, $filter->fromform);

$friadmin->set_courselist_references($page, $filter, $table, $output);

$friadmin->setup_courselist_page();
$friadmin->display_courselist_page();
