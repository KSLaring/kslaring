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
 * Arbeid og inkludering theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_ai
 * @copyright 2016 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'ai';

/////////////////////////////////
// The only thing you need to change in this file when copying it to
// create a new theme is the name above. You also need to change the name
// in version.php and lang/en/theme_ai.php as well.
//////////////////////////////////
//
$THEME->doctype = 'html5';
$THEME->parents = array('kommit', 'bootstrapbase');
$THEME->sheets = array('theme', 'custom');
$THEME->supportscssoptimisation = false;
$THEME->yuicssmodules = array();
$THEME->enable_dock = false;

// This will allow for moodle to be used on sites like responsinator.
$CFG->allowframembedding = true;

$THEME->parents_exclude_sheets = array(
    'bootstrapbase' => array(
        'moodle',
        'editor'
    )
);

$THEME->plugins_exclude_sheets = array(
    'block' => array(
        'html',
    ),
    'gradereport' => array(
        'grader',
    )
);

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_ai_process_css';

$THEME->blockrtlmanipulations = array(
    'side-pre' => 'side-post',
    'side-post' => 'side-pre'
);

// Additional block regions.
$THEME->layouts = array(
    // Main course page.
    'course' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'top', 'content-top', 'content-bottom', 'hidden-dock'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    'incourse' => array(
        'file' => 'columns3.php',
        'regions' => array('content-top', 'content-bottom', 'side-pre', 'side-post', 'top', 'hidden-dock'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    'coursehomepage' => array(
        'file' => 'columns3.php',
        'regions' => array('content-top', 'content-bottom', 'side-pre', 'side-post', 'top', 'hidden-dock'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true, 'nonavbar'=>true),
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'columns3_front.php',
        'regions' => array('side-pre', 'side-post', 'top', 'content-top', 'hidden-dock'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar'=>true),
    ),
    // My dashboard page.
    'mydashboard' => array(
        'file' => 'columns3.php',
        'regions' => array('content-top', 'content-bottom', 'side-pre', 'side-post', 'top', 'hidden-dock'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    // Blocks at the bottom.
    'blocksatbottom' => array(
        'file' => 'columns3blocksbottom.php',
        'regions' => array('side-pre', 'side-post', 'top', 'content-top', 'content-bottom', 'hidden-dock'),
        'defaultregion' => 'side-pre',
    )
);

// For the lesson pages force the default region to 'content-bottom'
// to place the 'Linked media' fake block away from the left column below the content.
// This change can only be done in the theme config.
global $cm;
if ($cm && property_exists($cm, 'modname') && $cm->modname === 'lesson') {
    $THEME->layouts['incourse']['defaultregion'] = 'content-bottom';
}
