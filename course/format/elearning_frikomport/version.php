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
 * eLearning Frikomport Format - Version
 *
 * Description
 *
 * @package             course
 * @subpackage          format/elearning_frikomport
 * @copyright           2010 eFaktor
 *
 * @creationDate        20/04/2015
 * @author              eFaktor     (fbv)
 *
 */


defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2016030700;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2014050800;        // Requires this Moodle version.
$plugin->component = 'format_elearning_frikomport';    // Full name of the plugin (used for diagnostics).


/* Dependencies */
$plugin->dependencies = array('local_course_page' => 2016033100);
