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
 * KS Læring Trondheim theme.
 *
 * @package   theme_trondheim
 * @copyright 2016 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = null;
$themename = 'theme_trondheim';

if (is_siteadmin()) {
    $ADMIN->add('themes', new admin_category($themename, 'Trondheim'));

    // Load the parent theme settings.
    require (__DIR__ . '/../kommit/settings/settings_base.php');

    // Load the slideshow theme settings.
    require (__DIR__ . '/../kommit/settings/settings_slideshow.php');
}
