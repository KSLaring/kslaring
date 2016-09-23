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

require_once(__DIR__ . '/../kommit/lib.php');

/**
 * Let the theme modify the page object before the page is generated.
 *
 * Add a course format hook with the method "page_init" in the course format
 * lib.php. On non course pages the format library is not loaded -
 * call the course format hook only on course related pages.
 *
 * @param moodle_page $page
 */
function theme_trondheim_page_init(moodle_page $page) {
    theme_kommit_page_init($page);
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string       $css   The CSS
 * @param theme_config $theme The theme config object.
 *
 * @return string The parsed CSS The parsed CSS.
 */
function theme_trondheim_process_css($css, $theme) {
    return theme_kommit_process_css($css, $theme);
}

/**
 * Include the Awesome Font.
 */
function theme_trondheim_set_fontwww($css) {
    return theme_kommit_set_fontwww($css);
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css  The CSS.
 * @param string $logo The URL of the logo.
 *
 * @return string The parsed CSS
 */
function theme_trondheim_set_logo($css, $logo) {
    return theme_kommit_set_logo($css, $logo);
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context  $context
 * @param string   $filearea
 * @param array    $args
 * @param bool     $forcedownload
 * @param array    $options
 *
 * @return bool
 */
function theme_trondheim_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
    array $options = array()) {
    static $theme;

    if (empty($theme)) {
        $theme = theme_config::load('trondheim');
    }

    return theme_kommit_pluginfile_serve($course, $cm, $context, $filearea, $args,
        $forcedownload, $options, $theme);
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css       The original CSS.
 * @param string $customcss The custom CSS to add.
 *
 * @return string The CSS which now contains our custom CSS.
 */
function theme_trondheim_set_customcss($css, $customcss) {
    return theme_kommit_set_customcss($css, $customcss);
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page   $page   Pass in $PAGE.
 *
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default
 *      heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_trondheim_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;
    $strhome = get_string('home');
    $strfootertext = get_string('footertext', 'theme_trondheim');
    $strfooterhelpurl = get_string('footerhelpurl', 'theme_trondheim');
    $strhelp = get_string('help');
    $footerbrukerhelp = get_string('footerbrukerhelp', 'theme_trondheim');
    $return->headertxtlaering = get_string('laering', 'theme_trondheim');
    $return->headertxtsystem = get_string('system', 'theme_trondheim');
    $return->heroheadline = '';

    $return->herolead = '';
    $return->herolinktext = '';
    $return->herolink = '';

    if (!empty($page->theme->settings->heroheadline)) {
        $return->heroheadline = format_text($page->theme->settings->heroheadline);
    }

    if (!empty($page->theme->settings->herolead)) {
        $return->herolead = format_text($page->theme->settings->herolead);
    }

    if (!empty($page->theme->settings->herolinktext)) {
        $return->herolinktext = format_text($page->theme->settings->herolinktext);
    }

    if (!empty($page->theme->settings->herolink)) {
        $return->herolink = $page->theme->settings->herolink;
    }

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::link($CFG->wwwroot, '',
            array('title' => $strhome, 'class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footertext = $strfootertext;

    $footerhelp = '';
    $footerhelp .= '<a href="' . $strfooterhelpurl . '">';
    $footerhelp .= '<img class="help icon" src="' . $output->pix_url('help') . '"/>';
    $footerhelp .= '&nbsp;' . $strhelp . '<br/>' . $footerbrukerhelp;
    $footerhelp .= '</a>';
    $return->footerhelp = $footerhelp;

    return $return;
}

/**
 * Returns true if the user can see the hidden blocks area.
 * On the front page always invisible for:
 *   Guest
 *   Student
 * ON other pages always visible for:
 *   Superuser
 *   Course creator
 *   teacher
 *   non-editing teacher
 *
 * @param moodle_page $page Pass in $PAGE.
 *
 * @return bool
 */
function theme_trondheim_show_hidden_blocks() {
    return theme_kommit_show_hidden_blocks();
}
