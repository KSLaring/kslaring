<?php

/**
 * Express Login  - loginExpress - Form
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/12/2014
 * @author          eFaktor     (fbv)
 */
global $USER,$CFG,$OUTPUT,$PAGE;
include_once('../../config.php');
require_once('expressloginlib.php');


$PAGE->set_context(context_system::instance());
$PAGE->set_url("$CFG->httpswwwroot/login/index.php");

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

$relative_path = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relative_path, '/'));
$micro  = 0;
if (count($args) == 3) {
    $micro = $args[1] . '/' . $args[2];
}

$action = new moodle_url('/local/express_login/login/index.php');

if (isloggedin()) {
    $valid = Express_Login::Check_ExpressLink($args[0]);
    if ($valid != $USER->id) {
        $action = new moodle_url('/local/express_login/login/loginError.php',array('er' => ERROR_LINK_NOT_VALID));
        redirect($action);
    }
}

$valid = Express_Login::Check_ExpressLink($args[0]);
if (!$valid) {
    $action = new moodle_url('/local/express_login/login/loginError.php',array('er' => ERROR_LINK_NOT_VALID));
}

?>

<html>
<body  onload="document.express.submit();">
<form method="post"  name="express" action="<?php echo $action;?>">
    <div class="loginform">
        <div class="form-input"><input type="hidden" id="UserName" maxlength="50" name="UserName" type="text" value="<?php echo $valid ?>"></div>
        <div class="form-input"><input type="hidden" id="micro" name="micro" type="text" value="<?php echo $micro ?>"></div>
    </div>
</form>
</body>
</html>




