<?php
/**
 * Express Login - Auto Generated Express Login - Bulk Action
 *
 * @package         local
 * @subpackage      express_login/bulk_express
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    16/11/2015
 * @author          eFaktor     (fbv)
 *
 */
global $PAGE,$SITE,$OUTPUT,$CFG,$USER,$SESSION;

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once('../expressloginlib.php');

require_login();
// Checking access
if (isguestuser($USER)) {
    require_logout();
}
admin_externalpage_setup('userbulk');

// Params
$context    = context_system::instance();
$url        = new moodle_url('/local/express_login/bulk_express/user_bulk_express_login.php');
$return     = new moodle_url('/admin/user/user_bulk.php');


if (isset($SESSION->bulk_users)) {
    $users = implode(',',$SESSION->bulk_users);
}else {
    $users = '';
}//if_bulk_users

if (!isset($SESSION->fields)) {
    $SESSION->fields = array();
}

// Capability
require_capability('moodle/user:update', $context);

if (empty($users)) {
    redirect($return);
}//if_users

$PAGE->set_url($url);
$PAGE->set_context($context);

echo $OUTPUT->header();

if (Express_Login::Activate_AutoExpressLogin($users)) {
    echo $OUTPUT->notification(get_string('bulk_succesful','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);
}else {
    echo $OUTPUT->notification(get_string('err_generic','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);
}//if_else

echo $OUTPUT->footer();
