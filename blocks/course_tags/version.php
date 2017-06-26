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
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2017062600;              // The current plugin version (Date: YYYYMMDDXX).
$plugin->release = "3.1.0 (Build: 2016122000)"; // User-friendly version number.
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'block_course_tags';   // Full name of the plugin (used for diagnostics).
$plugin->requires = 2016051900;             // Requires this Moodle version.
$plugin->dependencies = array(
        'local_tag' => 2017011100,
);
