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

// Get the HTML for the settings bits.
$html = theme_kommit_get_html_for_settings($OUTPUT, $PAGE);

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div class="container-fluid">

    <div class="logo-area">
        <?php
        $url = new moodle_url('/',array('redirect' => 0));
        ?>
        <a class="logo" href="<?php echo $url;?>">
            <img class="logo" alt="kommit logo" src="<?php echo $OUTPUT->pix_url('logo', 'theme'); ?>"
        </a>
    </div>
    <div id="set-homepage">
        <ul class="nav pull-right">
            <li><?php
                /**
                 * @updateDate  23/01/2014
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add a button to set your My home
                 */
                $my_page = get_user_preferences('user_home_page_preference');
                if ($my_page == $PAGE->url) {
                    echo $OUTPUT->single_button(new moodle_url('/local/mypage/rebuild.php'), get_string('resethome', 'theme_kommit'));
                }else {
                    $page_type = $PAGE->pagetype;
                    $found = ((strpos($page_type,'course-index-category') === false) ? false : true) ||
                        ((strpos($page_type,'course-view') === false) ? false : true) ||
                        ((strpos($page_type,'profile') === false) ? false : true);

                    if ($found) {
                        echo $OUTPUT->single_button(new moodle_url('/local/mypage/rebuild.php',array('url' => $PAGE->url)), get_string('sethome', 'theme_kommit'));
                    }
                }
                ?>
            </li>
            <li>&nbsp;</li>
            <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
        </ul>

    </div>

</div>

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
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div id="page" class="container-fluid">

    <header id="page-header" class="clearfix">
        <div id="page-navbar" class="clearfix">
            <div class="breadcrumb-nav"><?php echo $OUTPUT->navbar(); ?></div>
            <nav class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></nav>
        </div>
        <div id="course-header">
            <?php echo $OUTPUT->course_header(); ?>
        </div>
    </header>

    <div id="page-content" class="row-fluid">
        <section id="region-main" class="span12">
            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
    </div>

</div>


<footer id="page-footer">
    <div id="page-footer-inner" class="wrapper clearfix">
        <div class="column">
            <h4>Besøksadresse:</h4>
            <p>Oslo universitetssykehus - Ullevål<br>
                Bygning 31, inngang B, 3. etasje<br>
                Kirkeveien 166<br>
                0407 OSLO<br>
            </p>

        </div>
        <div class="column">
            <h4>Postadresse</h4>
            <p>Nasjonal kompetansetjeneste for prehospital akuttmedisin (kommit)<br>
                Oslo universitetssykehus HF<br>
                Ullevål sykehus<br>
                Postboks 4956 Nydalen<br>
                0424 OSLO

            </p>
        </div>
        <div class="column">
            <h4>Kontakt oss:</h4>
            <p>E-post<br>
                <a href="mailto:postmottak@kommit.no">postmottak@kommit.no</a><br>
                Faks<br>
                23 02 62 11<br>
                Telefon<br>
                23 02 62 10
            </p>
        </div>
        <div class="column">
            <h4>Informasjon</h4>
            <p>Om kommit<br>
            <p>Ansatte</p>

            <img class="social"
                 src="<?php echo $OUTPUT->pix_url('vimeo', 'theme'); ?>"
                 width="40" height="42"/>
            <img class="social"
                 src="<?php echo $OUTPUT->pix_url('facebook', 'theme'); ?>"
                 width="40" height="42"/>
            <img class="social"
                 src="<?php echo $OUTPUT->pix_url('twitter', 'theme'); ?>"
                 width="40" height="42"/>
            <img class="social"
                 src="<?php echo $OUTPUT->pix_url('rss', 'theme'); ?>"
                 width="40" height="42"/>

        </div>
    </div>

    </div>
</footer>

<?php echo $OUTPUT->standard_end_of_body_html() ?>


</body>
</html>
