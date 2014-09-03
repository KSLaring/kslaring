<div class="top-border">
</div>

<div class="header-background">
    <div class="container-fluid">
        <div class="logo-area">
            <a class="logo" href="<?php echo $CFG->wwwroot;?>"><img class="logo" alt="kommit logo" src="<?php echo
                $OUTPUT->pix_url('logo', 'theme'); ?>"></a>
        </div>
        <div class="header-right">
            <div class="social">
                <div class="col1"><a href="http://facebook.com/kskommit" target=_blank" alt="facebook icon"><i class="fa fa-facebook
                fa-2x" id="icon" aria-hidden="true"></i></a></div>
                <div class="col2"><a href="https://twitter.com/KSKommIT" target=_blank" alt="twitter icon"><i class="fa fa-twitter
                fa-2x" id="icon" aria-hidden="true"></i></a></div>
            </div>
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