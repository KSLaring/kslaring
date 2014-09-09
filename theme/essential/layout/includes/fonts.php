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
 * This is built using the bootstrapbase template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 * @package     theme_essential
 * @copyright   2013 Julian Ridden
 * @copyright   2014 Gareth J Barnard, David Bezemer, Mary L Evans
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

switch ($fontselect) {
case 1:
    $fonts[] = 'Open+Sans';
    break;
case 2:
    $fonts[] = 'Oswald';
    $fonts[] = 'PT+Sans';
    break;
case 3:
    $fonts[] = 'Roboto';
    break;
case 4:
    $fonts[] = 'PT+Sans';
    break;
case 5:
    $fonts[] = 'Ubuntu';
    break;
case 6:
    $fonts[] = 'Arimo';
    break;
case 7:
    $fonts[] = 'Lobster';
    $fonts[] = 'Raleway';
	break;
}

if(!empty($fonts)) {
	foreach($fonts as $font) {
		echo html_writer::empty_tag('link',
			array('href' => '//fonts.googleapis.com/css?family='.$font, 
				  'rel' => 'stylesheet', 
				  'type' => 'type/css'
				  ));
	}
}
?>