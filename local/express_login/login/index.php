<?php

include_once('../../../config.php');
require_once('loginlib.php');

global $PAGE,$SITE,$OUTPUT,$CFG,$USER;

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
}

// User
if (isset($_POST['UserName'])) {
    $username = $_POST['UserName'];
}else {
    $username = $_SESSION['UserName'];
    unset($_SESSION['UserName']);
}//if_user

// Micro learning
if (isset($_POST['micro'])) {
    $micro = $_POST['micro'];
}else {
    $micro = $_SESSION['micro'];
    unset($_SESSION['micro']);
}//if_delivery

if (isloggedin()) {
    $_SESSION['micro']  = $micro;
    $url                = new moodle_url('/local/express_login/login/login.php');
    redirect($url);
}//if_loggedin

list($num_attempts,$attempts) = Express_Link::Validate_UserAttempts($username);
if (!$num_attempts) {
    $err_url = new moodle_url('/local/express_login/login/loginError.php',array('er' => ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED));
    redirect($err_url);
}

// Params
$er = optional_param('er',0,PARAM_INT);

// Plugin info
$plugin_info     = get_config('local_express_login');
$minimum    = array('4','6','8');
$digits     = $minimum[$plugin_info->minimum_digits];

echo $OUTPUT->header();

if (!empty($er)) {
    echo html_writer::start_tag('div', array('class' => 'loginerrors'));
    echo html_writer::link('#', get_string('ERROR_EXPRESS_PIN_NOT_VALID','local_express_login',$attempts), array('id' => 'loginerrormessage', 'class' => 'accesshide'));
    echo $OUTPUT->error_text(get_string('ERROR_EXPRESS_PIN_NOT_VALID','local_express_login',$attempts));
    echo html_writer::end_tag('div');
}
?>

<html>
<div class="loginpanel">
    <form method="post"  id="login" action="<?php echo $CFG->httpswwwroot; ?>/local/express_login/login/login.php">
        <div class="form-label"><label for="pincode"><?php print_string("pin_code",'local_express_login') ?></label></div>
        <div class="form-input">
            <input type="password" name="pincode" id="pincode" size="15"  maxlength="<?php echo $digits ?>" value="" />
        </div>
        <div class="form-input">
            <div class="form-input"><input type="hidden" id="UserName" maxlength="50" name="UserName" type="text" value="<?php echo $username ?>"></div>
            <div class="form-input"><input type="hidden" id="micro" name="micro" type="text" value="<?php echo $micro ?>"></div>
        </div>
        <input type="submit" id="loginbtn" value="<?php print_string("login") ?>" />
    </form>
</div>
</html>

<?php
echo $OUTPUT->footer();

?>
