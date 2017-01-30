<?php defined('MOODLE_INTERNAL') || die; ?>
<footer id="page-footer">
    <div id="page-footer-inner" class="container-fluid clearfix">
        <div class="footer-text-container row-fluid">
            <?php echo $html->footertext ?>
        </div>
    </div>
</footer>
<footer id="sub-footer" class="container-fluid">
    <div class="row-fluid">
        <?php echo $OUTPUT->return_to_role(); ?>
    </div>
</footer>
