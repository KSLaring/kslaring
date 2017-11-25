<?php
/**
 * KS Læring Integration - Login via Feide
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

global $CFG,$PAGE,$SESSION,$OUTPUT,$USER;

include_once('../../../config.php');
require_once('feidelib.php');

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

// Params
$action         = new moodle_url('/local/wsks/feide/index.php');
$errUrl         = null;
$args           = null;
$relativePath   = null;

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

$relativePath = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relativePath, '/'));


if (count($args) != 2) {
    /* Redirect Error   */
    $errUrl = new moodle_url('/local/wsks/feide/error.php',array('er' => FEIDE_NOT_VALID));
    redirect($errUrl);
}

/* Validate USer    */
$userInfo   = null;
$errCode    = null;
list($userInfo,$errCode) = KS_FEIDE::ValidateUser($args);

if ($errCode != FEIDE_NON_ERROR) {
    /* Redirect Error   */
    $errUrl = new moodle_url('/local/wsks/feide/error.php',array('er' => $errCode));
    redirect($errUrl);
}

$_SESSION['user'] = $userInfo;

?>

<html>
<body  onload="document.feide.submit();">
<form method="post"  name="feide" action="<?php echo $action;?>">
    <div class="loginform">
    </div>
</form>
</body>
</html>