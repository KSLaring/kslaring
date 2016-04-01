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
 * Moodle's Starter theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package    theme_frikomport
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Let the theme modify the page object before the page is generated.
 *
 * Add a course format hook with the method "page_init" in the course format
 * lib.php. On non course pages the format library is not loaded -
 * call the course format hook only on course related pages.
 *
 * @param moodle_page $page
 */
function theme_frikomport_page_init(moodle_page $page) {
    global $CFG;

    $jshead = <<<EOT
    <script type='text/javascript' src="//wurfl.io/wurfl.js"></script>
EOT;

    if (!empty($CFG->additionalhtmlhead)) {
        $CFG->additionalhtmlhead .= $jshead;
    } else {
        $CFG->additionalhtmlhead = $jshead;
    }

    $jsbodyopen = <<<EOT
    <script type='text/javascript'>
        YUI().use('node', function(Y) {
            var bd = Y.one('body');
            if (bd && WURFL) {
                if (WURFL.is_mobile) {
                    bd.addClass('is-mobile');
                }
                if (WURFL.form_factor === 'Desktop') {
                    bd.addClass('is-desktop');
                }
            }
        });
    </script>
EOT;

    if (!empty($CFG->additionalhtmltopofbody)) {
        $CFG->additionalhtmltopofbody .= $jsbodyopen;
    } else {
        $CFG->additionalhtmltopofbody = $jsbodyopen;
    }

    if (function_exists('course_get_format')) {
        $courseformat = course_get_format($page->course);

        // Call a hook in the course format class to enable page manipulation
        // in the course format.
        if (method_exists($courseformat, 'page_init')) {
            $courseformat->page_init($page);
        }
    }
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_frikomport_process_css($css, $theme) {

    // Set the background image for the logo.
    $logo = $theme->setting_file_url('logo', 'logo');
    $css = theme_frikomport_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_frikomport_set_customcss($css, $customcss);

    $css = theme_frikomport_set_fontwww($css);

    return $css;
}

/**
 * Include the Awesome Font.
 */
function theme_frikomport_set_fontwww($css) {
    global $CFG, $PAGE;
    if(empty($CFG->themewww)){
        $themewww = $CFG->wwwroot."/theme";
    } else {
        $themewww = $CFG->themewww;
    }
    $tag = '[[setting:fontwww]]';

    $theme = theme_config::load('frikomport');
    if (!empty($theme->settings->bootstrapcdn)) {
    	$css = str_replace($tag, '//netdna.bootstrapcdn.com/font-awesome/4.0.0/fonts/', $css);
    } else {
    	$css = str_replace($tag, $themewww.'/frikomport/fonts/', $css);
    }
    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_frikomport_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_frikomport_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'logo') {
        $theme = theme_config::load('frikomport');
        return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_frikomport_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_frikomport_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::link($CFG->wwwroot, '', array('title' => get_string('home'), 'class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.$page->theme->settings->footnote.'</div>';
    }

    $return->manualcompletionhtml = '';
    if (function_exists('course_get_format')) {
        $courseformat = course_get_format($page->course);
        if (method_exists($courseformat, 'get_manualcompletionhtml') &&
            !is_null($courseformat->get_manualcompletionhtml())
        ) {
            $return->manualcompletionhtml = $courseformat->get_manualcompletionhtml();
        }
    }

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
 * @return bool
 */
function theme_frikomport_show_hidden_blocks() {
    global $COURSE;
    $return = false;

    $coursecontext = context_course::instance($COURSE->id);
    if (has_capability('theme/frikomport:viewhiddenblocks', $coursecontext)) {
        $return = true;
    }

    return $return;
}

/**
 * All theme functions should start with theme_frikomport_
 * @deprecated since 2.5.1
 */
function frikomport_process_css() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_frikomport_
 * @deprecated since 2.5.1
 */
function frikomport_set_logo() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_frikomport_
 * @deprecated since 2.5.1
 */
function frikomport_set_customcss() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}
