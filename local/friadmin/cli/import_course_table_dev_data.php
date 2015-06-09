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
 * Import course table dev data
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/clilib.php');      // cli only functions

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help' => false),
    array('h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Import course list data from a JSON file.

Options:
-h, --help            Print out this help

Example:
\$/usr/bin/php local/friadmin/cli/import_course_table_dev_data.php
";

    echo $help;
    die;
}

function import_data() {
    global $CFG, $DB;
    $DB->delete_records('friadmin_courselist_dev');

    $f = file_get_contents($CFG->dirroot . '/local/friadmin/fixtures/friadmin_courselist.json');

    $feed = json_decode($f, true);
    for ($i = 0; $i < count($feed['data']); $i++) {
        $row = array();
        foreach ($feed['data'][$i] as $key => $value) {
            $row[$key] = (is_numeric($value)) ? $value : mysql_real_escape_string($value);
        }
        $DB->insert_record('friadmin_courselist_dev', $row);
    }
}

// import the data
echo 'running...' . "\n";
import_data();
echo 'done...' . "\n";
