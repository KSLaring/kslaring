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
 * Moodle's stavanger theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_stavanger
 * @copyright 2016 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the HTML for the settings bits.
$html = theme_stavanger_get_html_for_settings($OUTPUT, $PAGE);
$show_hidden_blocks = theme_stavanger_show_hidden_blocks($PAGE);
$str_visibleadminonly = get_string('visibleadminonly', 'theme_stavanger');

$left = (!right_to_left()); // To know if to add 'pull-right' and 'desktop-first-column' classes in the layout for LTR.

// Get the URL for the logo link
$url = new moodle_url('/', array('redirect' => 0));

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>"/>
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes('two-column'); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php include 'inc/header.php'; ?>

<div id="page" class="container-fluid">

    <header id="page-header" class="clearfix">
        <?php echo $OUTPUT->context_header(); ?>
        <div id="page-navbar" class="clearfix">
            <div class="breadcrumb-nav"><?php echo $OUTPUT->navbar(); ?></div>
            <nav class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></nav>
        </div>

        <?php echo $OUTPUT->blocks('top', 'top-blocks', 'section'); ?>
    </header>

    <div id="page-content" class="row-fluid">
        <section id="region-main" class="span9<?php if ($left) {
            echo ' pull-right';
        } ?>">
            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->blocks('content-top', 'content-top-blocks', 'section');
            echo $OUTPUT->main_content();
            echo $OUTPUT->blocks('content-bottom', 'content-bottom-blocks', 'section');
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
        <?php
        $classextra = '';
        if ($left) {
            $classextra = ' desktop-first-column';
        }
        echo $OUTPUT->blocks('side-pre', 'span3' . $classextra);
        ?>
    </div>

    <?php if ($show_hidden_blocks) : ?>
    <div id="hidden-blocks-admin" class="clearfix">
        <h4><?php echo $str_visibleadminonly; ?></h4>
        <?php echo $OUTPUT->blocks('hidden-dock', 'hidden-dock-blocks', 'div'); ?>
    </div>
    <?php endif ?>
</div>

<?php include 'inc/footer.php'; ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>

</body>
</html>
