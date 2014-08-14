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
 * @copyright   2014 Gareth J Barnard, David Bezemer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'essential';

// The only thing you need to change in this file when copying it to
// create a new theme is the name above. You also need to change the name
// in version.php and lang/en/theme_essential.php as well.

$THEME->doctype = 'html5';
$THEME->yuicssmodules = array();
$THEME->parents = array('bootstrapbase');
$THEME->sheets = array('fontawesome');
/*
global $PAGE;
if (strpos($PAGE->bodyclasses, 'dir-rtl') === false) { // TODO - only works with TDM off.
    $THEME->sheets[] = 'essential';
} else {
    $THEME->sheets[] = 'essential-rtl';
    $THEME->sheets[] = 'rtl';
} */
$THEME->sheets[] = 'essential'; // LTR.
//$THEME->sheets[] = 'essential-rtl'; // RTL.
//$THEME->sheets[] = 'rtl'; // RTL.
// Note: Swap above and do a purge all caches if using an RTL language.
$THEME->sheets[] = 'custom';
$THEME->supportscssoptimisation = false;

if ($CFG->version >= 2014051200.10 ) {
    $THEME->enable_dock = true;
}

$THEME->editor_sheets = array();

$THEME->plugins_exclude_sheets = array();

$THEME->layouts = array(
    // Most backwards compatible layout without the blocks - this is the layout used by default.
    'base' => array(
        'file' => 'columns1.php',
        'regions' => array(),
        'defaultregion' => '',
        'options' => array('noblocks'=>true),
    ),
    // Front page.
    'frontpage' => array(
        'file' => 'frontpage.php',
        'regions' => array('side-pre', 'home-left', 'home-middle', 'home-right', 'footer-left', 'footer-middle', 'footer-right', 'hidden-dock'),
        'defaultregion' => 'hidden-dock',
        'options' => array('nonavbar'=>true),
    ),
    // Standard layout with blocks, this is recommended for most pages with general information.
    'standard' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-post',
    ),
    // Main course page.
    'course' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-post',
        'options' => array('langmenu'=>true),
    ),
    'coursecategory' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-post',
    ),
    // part of course, typical for modules - default page layout if $cm specified in require_login().
    'incourse' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre','side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-post',
    ),
    // Server administration scripts.
    'admin' => array(
        'file' => 'columns2.php',
        'regions' => array('side-pre', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-pre',
    ),
    // My dashboard page.
    'mydashboard' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-post',
        'options' => array('langmenu'=>true),
    ),
    // My public page.
    'mypublic' => array(
        'file' => 'columns3.php',
        'regions' => array('side-pre', 'side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-post',
    ),
    'login' => array(
        'file' => 'login.php',
        'regions' => array('footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => '',
        'options' => array('langmenu'=>true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'popup.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'columns1.php',
        'regions' => array('footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'footer-right',
        'options' => array('nofooter'=>true, 'nocoursefooter'=>true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible.
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'defaultregion' => '',
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, links, or API calls that would lead to database or cache interaction.
    // Please be extremely careful if you are modifying this layout.
    'maintenance' => array(
        'file' => 'maintenance.php',
        'regions' => array(),
        'defaultregion' => '',
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'columns1.php',
        'regions' => array('footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'footer-right',
        'options' => array('nofooter'=>true, 'nonavbar'=>false),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'defaultregion' => '',
    ),
    // The pagelayout used for reports.
    'report' => array(
        'file' => 'columns2.php',
        'regions' => array('side-pre', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-pre',
    ),
    // The pagelayout used for safebrowser and securewindow.
    'secure' => array(
        'file' => 'secure.php',
        'regions' => array('side-pre', 'side-post', 'footer-left', 'footer-middle', 'footer-right'),
        'defaultregion' => 'side-pre'
    ),
);

$THEME->javascripts = array(
    'coloursswitcher',
);

$THEME->rendererfactory = 'theme_overridden_renderer_factory';

$THEME->csspostprocess = 'theme_essential_process_css';

$useragent = '';
if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    $useragent = $_SERVER['HTTP_USER_AGENT'];
}

if (core_useragent::is_ie() && !core_useragent::check_ie_version('9.0')) {
    $THEME->javascripts[] = 'html5shiv';
}