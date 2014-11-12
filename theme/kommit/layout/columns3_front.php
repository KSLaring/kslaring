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

// Get the HTML for the settings bits.
$html = theme_kommit_get_html_for_settings($OUTPUT, $PAGE);
$str_visibleadminonly = get_string('visibleadminonly', 'theme_kommit');

if (right_to_left()) {
    $regionbsid = 'region-bs-main-and-post';
} else {
    $regionbsid = 'region-bs-main-and-pre';
}

// Get the URL for the logo link
$url = new moodle_url('/', array('redirect' => 0));
$url_course_lst = new moodle_url('/course/index.php');
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?> xmlns="http://www.w3.org/1999/html">
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>"/>
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>


<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php include 'inc/header.php'; ?>

    <div class="hero-unit">
        <div class="container-fluid">
            <div class="texts">
                <h1>Ny læringsarena</h1>

                <div class="lead">Kurs når det passer deg. Ressurser alltid tilgjengelig</div>
                <div class="buttons">
                    <a href="<?php echo $url_course_lst;?>"><button>Finn kurs og dokumentasjon</button></a>
                </div>
            </div>
        </div>
    </div>

    <div id="page" class="container-fluid">
        <header id="page-header" class="clearfix">
            <div id="page-navbar" class="clearfix">
                <div class="breadcrumb-nav"><?php echo $OUTPUT->navbar(); ?></div>
                <nav class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></nav>
            </div>

            <div id="course-header">
                <?php echo $OUTPUT->course_header(); ?>
            </div>

            <?php echo $OUTPUT->blocks('top', 'top-blocks', 'section'); ?>
        </header>

        <div id="page-content" class="row-fluid">
            <div id="<?php echo $regionbsid ?>" class="span9">
                <div class="row-fluid">
                    <section id="region-main" class="span8 pull-right">
                        <?php
                        //echo $html->heading;
                        echo $OUTPUT->course_content_header();
                        echo $OUTPUT->blocks('content-top', 'content-top-blocks', 'section');
                        echo $OUTPUT->main_content();
                        echo $OUTPUT->course_content_footer();
                        ?>
                    </section>
                    <?php echo $OUTPUT->blocks('side-pre', 'span4 desktop-first-column'); ?>
                </div>
            </div>
            <?php echo $OUTPUT->blocks('side-post', 'span3'); ?>
        </div>

        <?php if (is_siteadmin()) : ?>
        <div id="hidden-blocks-admin" class="clearfix">
            <h4><?php echo $str_visibleadminonly; ?></h4>
            <?php echo $OUTPUT->blocks('hidden-dock'); ?>
        </div>
        <?php endif ?>
    </div>

<?php include 'inc/footer.php'; ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>


</body>
</html>
