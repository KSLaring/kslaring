<?php // All PHP goes to the top
/**
 * @updateDate  09/09/2014
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add the Municipality log to the header
 */
$municipality = null;
require_once($CFG->dirroot . '/blocks/municipality/municipalitylib.php');

$loggedin = isloggedin();
$loggedinclass = ' not-loggedin';
if ($loggedin) {
    /* Get the municipality connected with the user */
    $municipality = Municipality::municipality_ExitsMuni_User($USER->id);
    $loggedinclass = ' loggedin';
}
?>

<div class="top-border">
</div>

<div class="header-background">
    <div class="container-fluid">
        <div class="logo-area">
            <a class="logo" href="<?php echo $CFG->wwwroot; ?>">
                <img class="logo"
                     alt="frikomport logo"
                     src="<?php echo
                     $OUTPUT->pix_url('frikomport-logo', 'theme'); ?>">
            </a>
        </div>

        <div class="header-right">
            <?php
            /**
             * @updateDate  09/09/2014
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add the Municipality log to the header
             */
            if ($municipality) {
                echo '<div class="muni-logo"><img class="logo" alt="' .
                    $municipality->name . '"src="' . $municipality->logo . '"/></div>';
            }
            ?>
        </div>
    </div>
</div>

<div id="header" class="header<?php echo $loggedinclass; ?>">
    <header role="banner"
            class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex">
        <nav role="navigation" class="navbar-inner">
            <div class="container-fluid">

                <a class="btn btn-navbar" data-toggle="workaround-collapse"
                   data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <?php echo $OUTPUT->search_form(); ?>

                <?php if (!$loggedin || isguestuser()) : ?>
                    <div class="navbar-text"><?php echo $OUTPUT->login_info() ?></div>
                <?php endif ?>

                <div id="moodle-navbar" class="nav-collapse collapse">
                    <?php echo $OUTPUT->custom_menu(); ?>
                    <?php echo $OUTPUT->user_menu(); ?>
                </div>
            </div>
        </nav>
    </header>
</div>
