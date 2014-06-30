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
 * Moodle's kommit theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_kommit
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'kommit';

/////////////////////////////////
// The only thing you need to change in this file when copying it to
// create a new theme is the name above. You also need to change the name
// in version.php and lang/en/theme_kommit.php as well.
//////////////////////////////////
//
$THEME->doctype = 'html5';
$THEME->parents = array('bootstrapbase');
$THEME->sheets = array('font-awesomemin', 'moodle', 'theme', 'custom');
$THEME->supportscssoptimisation = false;
$THEME->yuicssmodules = array();

// add custom javascripts here
//---------------------------------


// this will allow for moodle to be used on sites like responsinator
//---------------------------------
$CFG->allowframembedding = true;

$THEME->editor_sheets = array('editor');

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
$THEME->csspostprocess = 'theme_kommit_process_css';

$THEME->blockrtlmanipulations = array(
    'side-pre' => 'side-post',
    'side-post' => 'side-pre'
);
// additional block regions
$THEME->layouts = array(
    // Main course page.
    'course' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'top', 'bottom', 'content-top'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'columns3_front.php',
        'regions' => array('side-pre', 'side-post', 'top', 'bottom', 'content-top'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar'=>true),
    )
);