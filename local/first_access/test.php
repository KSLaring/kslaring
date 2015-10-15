<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 15/10/15
 * Time: 13:20
 * To change this template use File | Settings | File Templates.
 */
require_once('../../config.php');

$url            = new moodle_url('/local/first_access/test.php');

$userId         = optional_param('id',0,PARAM_INT);
$context        = context_system::instance();

$user_context   = context_user::instance($userId);

$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

echo "TEsting --> USER : " . $userId . "</br>";

if (isloggedin()) {
    echo " USER Log In" . "</br>";
}else {
    echo " USER NOT LOG IN " . "</br>";
}

$course = $SITE;

// Check that the user account is properly set up.
if (user_not_fully_set_up($USER)) {
    if ($preventredirect) {
        throw new require_login_exception('User not fully set-up');
    }
    if ($setwantsurltome) {
        $SESSION->wantsurl = qualified_me();
    }
    echo " NOT FULLY SET UP" . "</br>";
    //redirect($CFG->wwwroot .'/user/edit.php?id='. $USER->id .'&amp;course='. SITEID);
}

//require_login();

echo $OUTPUT->footer();