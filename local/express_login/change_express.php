<?php
/**
 * Express Login  - Change PIN Code
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    02/12/2014
 * @author          eFaktor     (fbv)
 */
global $CFG,$PAGE,$OUTPUT,$PAGE,$USER;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once('expressloginlib.php');
require_once('index_form.php');

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Params
$id              = optional_param('id',0,PARAM_INT);
$user_id         = $USER->id;
$current_page    = null;
$plugin_info     = null;
$return_url      = new moodle_url('/user/profile.php',array('id' => $user_id));

$PAGE->set_url(new moodle_url('/local/express_login/change_express.php'));

// Settings page
$PAGE->set_context(CONTEXT_USER::instance($user_id));
$PAGE->set_pagelayout('mypublic');
$PAGE->set_pagetype('user-profile');

// Get the profile page.  Should always return something unless the database is broken.
if (!$current_page = my_get_page($user_id, MY_PAGE_PUBLIC)) {
    print_error('mymoodlesetup');
}
// Start setting up the page.
$PAGE->set_subpage($current_page->id);
$PAGE->navbar->add(get_string('pluginname','local_express_login'));

// Check user
if ($id && ($user_id != $id)) {
    $PAGE->set_context(CONTEXT_SYSTEM::instance());
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('err_express_access','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
    echo $OUTPUT->footer();
    die();
}

// Plugins info
$plugin_info     = get_config('local_express_login');

// Add form
$exists_express = Express_Login::Exists_ExpressLogin($user_id);
$form = new express_login_change_pin_code(null,array($plugin_info,$exists_express));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    // Generate express login
    $express_login = Express_Login::Generate_ExpressLink($data,true);
    if ($express_login) {

        $form_link = new express_login_link_form(null,null);
        if ($form_link->is_cancelled()) {
            $_POST = array();
            redirect($return_url);
        }elseif ($data_link = $form_link->get_data()) {
            $_POST = array();
            redirect($return_url);
        }

        echo $OUTPUT->header();
            echo '<script src="express/ZeroClipboard.js"></script>';
            $form_link->display();
        echo $OUTPUT->footer();
        $_POST = array();

        die();
    }else {
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('err_generic','local_express_login'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
        echo $OUTPUT->footer();
        die();
    }
}//if_form

echo $OUTPUT->header();
    $form->display();
echo $OUTPUT->footer();
