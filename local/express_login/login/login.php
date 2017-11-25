<?php
/**
 * Express Login  - Index
 *
 * @package         local
 * @subpackage      express_login/login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/12/2014
 * @author          eFaktor     (fbv)
 */
require_once('../../../config.php');
require_once('loginlib.php');

global $PAGE,$SITE,$OUTPUT,$CFG,$USER;

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

// Params
$valid      = null;
$err_code   = null;
$err_url    = null;

// Checking access
if (isguestuser($USER)) {
    $err_url = new moodle_url('/local/express_login/login/loginError.php');
    redirect($err_url);
    die();
}


// GET Data
if (isloggedin()) {
    $login          = true;
    $microSession   = $_SESSION['micro'];
    unset($_SESSION['micro']);
}else {
    $frm            = data_submitted();
    $microSession   = $frm->micro;
    $_POST          = array();

    list($valid,$err_code) =  Express_Link::Validate_UserExpress($frm);

    if (!$valid) {
        $err_url = new moodle_url('/local/express_login/login/loginError.php',array('er' => $err_code));

        if ($err_code == ERROR_EXPRESS_PIN_NOT_VALID) {
            Express_Link::Update_Attempts($frm->UserName,1);
            list($num_attempts,$attempts) = Express_Link::Validate_UserAttempts($frm->UserName);
            if ($num_attempts) {
                $_SESSION['UserName']   = $frm->UserName;
                $_SESSION['micro']      = $frm->micro;
                $err_url     = new moodle_url('/local/express_login/login/index.php',array('er'=>1));
            }else {
                $err_url->param('er',ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED);
            }//if_else
        }//if_er_

        redirect($err_url);
    }//if_else_valid

    // Reset attempts user
    Express_Link::Update_Attempts($frm->UserName);
    // Login user
    $login = Express_Link::LoginUser($frm->UserName);
}//if_isloggedin

if ($login) {
    // Redirect to the right page
    if ($microSession) {
        // Check That the delivery and module exists
        $micro_learning = explode('/', ltrim($microSession, '/'));
        $microURL = Express_Link::LoginMicroLearning($micro_learning,$login->id);

        // Redirect user to the correct activity into the course
        if ($microURL) {
            redirect($microURL);
            die();
        }else {
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('err_micro_lnk','local_express_login'), 'notifysuccess');
            echo $OUTPUT->continue_button($CFG->wwwroot);
            echo $OUTPUT->footer();
        }
    }else {
        redirect($CFG->wwwroot);
        die();
    }//if_delivery_module
}else {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('err_generic','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();
}


