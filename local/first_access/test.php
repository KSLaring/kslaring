<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 15/10/15
 * Time: 13:20
 * To change this template use File | Settings | File Templates.
 */
require_once('../../config.php');

$userId         = $USER->id;
$context        = context_system::instance();

$user_context   = context_user::instance($userId);

$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

echo "TEsting";

echo $OUTPUT->footer();