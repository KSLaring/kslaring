<div class="top-border">
</div>

<?php
    /**
     * @updateDate  09/09/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the Municipality log to the header
     */
    $muni = null;
    $logo = null;
    require_once($CFG->dirroot . '/blocks/municipality/municipalitylib.php');
    if (isloggedin()) {
        /* Get the municipality connected with the user */
        $muni = Municipality::municipality_ExitsMuni_User($USER->id);
        /* Get the municipality logo */
        $logo = Municipality::municipality_GetLogo($muni);
    }
?>

<div class="header-background">
    <div class="container-fluid">
        <div class="logo-area">
            <a class="logo" href="<?php echo $CFG->wwwroot;?>"><img class="logo" alt="kommit logo" src="<?php echo
                $OUTPUT->pix_url('logo', 'theme'); ?>"></a>
        </div>

        <div class="header-right">
            <div class="social">
                <div class="col1"><a href="http://facebook.com/kskommit" target=_blank" alt="facebook icon"><i class="fa fa-facebook
                fa-2x" id="icon_facebook" aria-hidden="true"></i></a></div>
                <div class="col2"><a href="https://twitter.com/KSKommIT" target=_blank" alt="twitter icon"><i class="fa fa-twitter
                fa-2x" id="icon_twitter" aria-hidden="true"></i></a></div>
            </div>
        </div>
        <?php
            /**
             * @updateDate  09/09/2014
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add the Municipality log to the header
             */
            if ($logo) {
                echo '<div class="header-center">' . $logo . '</div>';
            }
        ?>
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