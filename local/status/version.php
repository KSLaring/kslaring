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
 * Fellesdata Status Integration - Version
 *
 * @package         local
 * @subpackage      status
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

$plugin->version    =  '2017091900';         /* The plugin current version (DATE: YYYYMMDDXX) */
$plugin->component  =  'local_status';

/* Dependencies */
$plugin->dependencies = array('local_fellesdata'  => 2017022002);