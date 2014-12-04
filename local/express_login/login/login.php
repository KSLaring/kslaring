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

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

/* Get Data */
$frm    = data_submitted();
$_POST  = array();

/* Params   */
$valid      = null;
$err_code   = null;
$err_url    = null;

list($valid,$err_code) =  Express_Link::Validate_UserExpress($frm);

if (!$valid) {
    $err_url = new moodle_url('/local/express_login/login/loginError.php',array('er' => $err_code));

    if ($err_code == ERROR_EXPRESS_PIN_NOT_VALID) {
        Express_Link::Update_Attempts($frm->UserName,1);
        $num_attempts = Express_Link::Validate_UserAttempts($frm->UserName);
        if ($num_attempts) {
            $_SESSION['UserName'] = $frm->UserName;
            $err_url     = new moodle_url('/local/express_login/login/index.php',array('er'=>1));
        }else {
            $err_url->param('er',ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED);
        }//if_else
    }//if_er_

    redirect($err_url);
}//if_else_valid

Express_Link::Update_Attempts($frm->UserName);
$login = Express_Link::LoginUser($frm->UserName);

if ($login) {
    redirect($CFG->wwwroot);
    die();
}else {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('err_generic','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
    echo $OUTPUT->footer();
}


