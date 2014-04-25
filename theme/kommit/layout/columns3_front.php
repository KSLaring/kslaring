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

if (right_to_left()) {
    $regionbsid = 'region-bs-main-and-post';
} else {
    $regionbsid = 'region-bs-main-and-pre';
}

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

<div class="top-border">
</div>

<div class="header-background">
    <div class="container-fluid">
        <div class="logo-area">
            <a class="logo" href="<?php echo $url; ?>"><img class="logo" alt="kommit logo" src="<?php echo
                $OUTPUT->pix_url('logo', 'theme'); ?>"></a>
        </div>

        <div class="header-right">

            <div class="social">
                <div class="col1"><a href="#"><i class="fa fa-facebook fa-2x" id="icon"></i></a></div>
                <div class="col2"><a href="#"><i class="fa fa-twitter fa-2x" id="icon"></i></a></div>
                <div class="col1"><a href="#"><i class="fa fa-linkedin fa-2x" id="icon"></i></a></div>
            </div>
        </div>
    </div>

    <div id="header" class="header">

        <header role="banner" class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex">
            <nav role="navigation" class="navbar-inner">
                <div class="container-fluid">

                    <a class="btn btn-navbar" data-toggle="workaround-collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>

                    <div id="moodle-navbar" class="nav-collapse collapse">
                        <?php echo $OUTPUT->custom_menu(); ?>
                        <?php echo $OUTPUT->user_menu(); ?>

                    </div>
                </div>
            </nav>
        </header>

    </div>


    <div class="hero-unit">
        <div class="container-fluid">
            <div class="texts">
                <h1>Ny læringsarena</h1>

                <div class="lead">Kurs når det passer deg. Ressurser alltid tilgjengelig</div>
                <div class="buttons">
                    <button href="#">Finn kurs og dokumentasjon</button>
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

    </div>

    <footer id="page-footer">
        <div id="page-footer-inner" class="wrapper clearfix">

            <div class="column">
                <h4>Kontakt:</h4>

                <p>Telefon: +47 09088</br>
                    Epost: info@kommit.no
                </p>
                <img src="<?php echo $OUTPUT->pix_url('ks_footer_logo', 'theme'); ?>" width="71" height="35"/>
            </div>
            <div class="column">
                <h4>Siste nytt</h4>

                <p>Informasjonssikkerhet</br>
                    KOLS-kurs</br>
                    Saksbehandlers arkivrutiner</br>
                    Er du god i norsk og nynorsk?</br>
                    Introduksjonsprogrammet</br>
                </p>
            </div>
            <div class="column">
                <h4>Populære kurs</h4>

                <p>Informasjonssikkerhet</br>
                    KOLS-kurs</br>
                    Saksbehandlers arkivrutiner</br>
                    Er du god i norsk og nynorsk?</br>
                    Introduksjonsprogrammet</br>
                </p>
            </div>
            <div class="column">
                <h4>Populære nedlastinger</h4>

                <p>Informasjonssikkerhet</br>
                    KOLS-kurs</br>
                    Saksbehandlers arkivrutiner</br>
                    Er du god i norsk og nynorsk?</br>
                    Introduksjonsprogrammet</br>

                </p>
            </div>
            <div class="column">
                <h4>Siste fra brukerfora</h4>

                <p>Informasjonssikkerhet</br>
                    KOLS-kurs</br>
                    Saksbehandlers arkivrutiner</br>
                    Er du god i norsk og nynorsk?</br>
                    Introduksjonsprogrammet</br>
                </p>
            </div>
            <div class="column">
                <h4>Hyppige søk</h4>

                <p>Informasjonssikkerhet</br>
                    KOLS</br>
                    Saksbehandlers</br>
                    nynorsk?</br>
                    Introduksjonsprogram</br>
                </p>
            </div>
            <p>© 2014 KOMMUNESEKTORENS ORGANISASJON</p>

        </div>
    </footer>
</div>

<?php echo $OUTPUT->standard_end_of_body_html() ?>


</body>
</html>
