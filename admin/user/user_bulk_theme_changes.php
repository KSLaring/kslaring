<?php

/**
 * Theme Changes - Bulk Action
 *
 * @package         admin
 * @subpackage      user
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    18/03/2013
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('user_bulk_theme_changes_form.php');

require_login();
admin_externalpage_setup('userbulk');
require_capability('moodle/user:update', CONTEXT_SYSTEM::instance());

$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

$return = new moodle_url('/admin/user/user_bulk.php');
$users = $SESSION->bulk_users;
if (empty($users)) {
    redirect($return);
}
$form = new user_bulk_theme_changes_form(null);

if ($form->is_cancelled()) {
    redirect($return);
} else if ($data = $form->get_data()) {
    foreach ($users as $key => $id) {
        $instance = new stdClass();
        $instance->id = $id;
        $instance->theme = $data->theme;
        $DB->update_record('user',$instance);
    }
    redirect($return);
}//if_else

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('bulk_theme', 'local_theme_changes'));

echo $OUTPUT->box_start();
$form->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();