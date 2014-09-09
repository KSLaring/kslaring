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
 
require_once(dirname(__FILE__).'/pagesettings.php');
 
?>
<footer role="contentinfo" id="page-footer">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 pull-left">
                <div class="column">
                    <?php echo $OUTPUT->blocks('footer-left'); ?>
                </div>
            </div>
            <div class="span4 center">
                <div class="column">
                    <?php echo $OUTPUT->blocks('footer-middle'); ?>
                </div>
            </div>
            <div class="span4 pull-right">
                <div class="column">
                    <?php echo $OUTPUT->blocks('footer-right'); ?>
                </div>
            </div>
        </div>

        <div class="footerlinks row-fluid">
            <hr />
            <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')); ?></p>
        <?php if ($hascopyright) {
            echo '<p class="copy">&copy; '.date("Y").' '.$hascopyright.'</p>';
        } ?>

        <?php if ($hasfootnote) {
            echo '<div class="footnote">'.$hasfootnote.'</div>';
        } ?>
        </div>
        <?php echo $OUTPUT->standard_footer_html(); ?>
    </div>
</footer>

<script type="text/javascript">
jQuery(document).ready(function() {
    var offset = 220;
    var duration = 500;
    jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
    });
    
    jQuery('.back-to-top').click(function(event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, duration);
        return false;
    })
	$('.navbar').affix({
		  offset: {
			top: $('header').height()
		  }
	});	
});
</script>
<a href="#top" class="back-to-top" title="<?php print_string('backtotop', 'theme_essential'); ?>"><i class="fa fa-angle-up "></i></a>
<?php echo $OUTPUT->standard_end_of_body_html() ?>