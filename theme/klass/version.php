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
 * @package    theme_klass
 * @copyright  2013 Moodle, moodle.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2015070600;
$plugin->maturity = MATURITY_STABLE; // this version's maturity level.
$plugin->release = 'Klass v1.1 for moodle 2.9.1';
$plugin->requires  = 2013110500;
$plugin->component = 'theme_klass';
$plugin->dependencies = array(
    'theme_bootstrapbase'  => 2013110500,
);
