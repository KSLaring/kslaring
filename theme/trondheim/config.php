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

$THEME->name = 'trondheim';
$parentthemname = 'kommit';

// Load the parent theme config to be able to use the identical config settings.
$parentconfig = theme_config::load($parentthemname);

/////////////////////////////////
// The only thing you need to change in this file when copying it to
// create a new theme is the name above. You also need to change the name
// in version.php and lang/en/theme_stavanger.php as well.
//////////////////////////////////
//
$THEME->doctype = 'html5';
$THEME->parents = array($parentthemname, 'bootstrapbase');
$THEME->sheets = array('theme', 'custom');
$THEME->supportscssoptimisation = false;
$THEME->yuicssmodules = array();
$THEME->enable_dock = false;

// This will allow for moodle to be used on sites like responsinator.
$CFG->allowframembedding = true;

$THEME->parents_exclude_sheets = $parentconfig->parents_exclude_sheets;
$THEME->plugins_exclude_sheets = $parentconfig->plugins_exclude_sheets;
$THEME->blockrtlmanipulations = $parentconfig->blockrtlmanipulations;

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_trondheim_process_css';

// Use the parent layouts.
$THEME->layouts = $parentconfig->layouts;

// For the lesson pages force the default region to 'content-bottom'
// to place the 'Linked media' fake block away from the left column below the content.
// This change can only be done in the theme config.
global $cm;
if ($cm && property_exists($cm, 'modname') && $cm->modname === 'lesson') {
    $THEME->layouts['incourse']['defaultregion'] = 'content-bottom';
}
