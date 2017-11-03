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
        <div class="stavanger-background-image clearfix">
            <div class="logo-area">
                <a class="logo" href="<?php echo $CFG->wwwroot; ?>">
                    <h1>SK Læring <span class="smaller">Kursadministrasjonssystem for Stavanger kommune</span></h1></a>
            </div>

            <div class="header-right">
                <?php if ($municipality) {
                    echo '<div class="muni-logo"><img class="logo" alt="' .
                        $municipality->name . '"src="' . $municipality->logo . '"/></div>';
                } ?>
            </div>
        </div>
    </div>
</div>

<div id="header" class="header<?php echo $loggedinclass; ?>">
    <header role="banner"
            class="navbar navbar-static-top<?php echo $html->navbarclass ?> moodle-has-zindex">
        <nav role="navigation" class="navbar-inner">
            <div class="container-fluid">
                <?php echo $OUTPUT->search_form(); ?>

                <div class="navbar-menues">
                    <?php echo $OUTPUT->navbar_button(); ?>
                    <?php echo $OUTPUT->user_menu(); ?>

                    <?php if (!$loggedin) : ?>
                        <div class="navbar-text"><?php echo $OUTPUT->login_info() ?></div>
                    <?php endif ?>

                    <div id="moodle-navbar" class="nav-collapse collapse">
                        <?php echo $OUTPUT->custom_menu(); ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</div>

<!--Put in header-->
<style type="text/css"></style>
<script type="text/javascript" language="javascript">
    ed24ChatObj = {
        'entrypoint': 'Kommuneforlaget_Main',
        'mainid': 'A53',
        'Online': '<img src="https://www.kommuneforlaget.no/filestore/dev/GFX/nettpratonline.png"/>',
        'Offline': '<img src="https://www.kommuneforlaget.no/filestore/dev/GFX/nettpratoffline.png"/>',
        'Busy': '<img src="https://login.edialog24.com/images/standard2-offline-no.jpg"/>',
        'webserverAddress': 'https://login.edialog24.com',
        'urlToOpen': 'https://login.edialog24.com/chattemplate/Kommuneforlaget_Main/index.html',
        'windowSettings': 'width=600,height=800,status=0,scrollbars=0,titlebar=0'
    };
</script>
<script src="https://login.edialog24.com/ChatClient3/EntrypointScript4.js"
        type="text/javascript"></script>
