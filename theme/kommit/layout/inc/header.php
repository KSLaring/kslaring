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

            <?php
            /**
             * @updateDate  09/09/2014
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add the Municipality log to the header
             */
            if ($logo) {
                echo '<div class="muni-logo">' . $logo . '</div>';
            }
            ?>

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