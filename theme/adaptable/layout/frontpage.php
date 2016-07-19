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
 * Version details
 *
 * @package    theme_adaptable
 * @copyright 2015 Jeremy Hopkins (Coventry University)
 * @copyright 2015 Fernando Acedo (3-bits.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(dirname(__FILE__) . '/includes/header.php');

$left = theme_adaptable_get_block_side();

$hasfootnote = (!empty($PAGE->theme->settings->footnote));

if (!empty($PAGE->theme->settings->sliderenabled)) {
    echo $OUTPUT->get_frontpage_slider();
}

if (!empty($PAGE->theme->settings->infobox)) {
    if (!empty($PAGE->theme->settings->infoboxfullscreen)) {
        echo '<div id="theinfo">';
    } else {
        echo '<div id="theinfo" class="container">';
    }
?>
            <div class="row-fluid">
                <?php echo $OUTPUT->get_setting('infobox', 'format_html'); ?>
            </div>
        </div>
 
<?php
}
?>

<?php if (!empty($PAGE->theme->settings->frontpagemarketenabled)) {
    echo $OUTPUT->get_marketing_blocks();
} ?>

<?php if (!empty($PAGE->theme->settings->frontpageblocksenabled)) { ?>
    <div id="frontblockregion" class="container">
        <div class="row-fluid">
            <?php echo $OUTPUT->get_block_regions(); ?>
        </div>
    </div>
<?php
}
?>

<?php
if (!empty($PAGE->theme->settings->infobox2)) {
    if (!empty($PAGE->theme->settings->infoboxfullscreen)) {
        echo '<div id="themessage">';
    } else {
        echo '<div id="themessage" class="container">';
    }
?>

    <div id="themessage-internal">
        <div class="row-fluid">
<?php echo $OUTPUT->get_setting('infobox2', 'format_html');; ?>
        </div>
    </div>
</div>
<?php
}
?>

<div class="container outercont">
    <div id="page-content" class="row-fluid">
     <div id="page-navbar" class="span12">
            <nav class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></nav>
            <?php echo $OUTPUT->navbar(); ?>
    </div>
<?php
if ($left == 1) {
    echo $OUTPUT->blocks('side-post', 'span3 desktop-first-column');
}

// Control span to display course tiles.
if (!isloggedin()) {
    echo '<section id="region-main">';
} else {
    echo '<section id="region-main" class="span9 desktop-first-column">';
} ?>


<?php
echo $OUTPUT->course_content_header();
echo $OUTPUT->main_content();
echo $OUTPUT->course_content_footer();
?>
</section>

<?php
if ($left == 0) {
    echo $OUTPUT->blocks('side-post', 'span3');
}
?>
</div>

<?php
if (is_siteadmin()) {
?>
      <div class="hidden-blocks">
        <div class="row-fluid">
          <h4><?php echo get_string('frnt-footer', 'theme_adaptable') ?></h4>
          <?php
            echo $OUTPUT->blocks('frnt-footer');
          ?>
        </div>
      </div>
    <?php
}
?>
</div>

<?php
require_once(dirname(__FILE__) . '/includes/footer.php');
