<?php
/**
 * Express Login  - Index
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/11/2014
 * @author          eFaktor     (fbv)
 */
global $CFG,$PAGE,$OUTPUT,$PAGE,$USER;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once('expressloginlib.php');
require_once('index_form.php');

// Params
$id              = optional_param('id',0,PARAM_INT);
$user_id         = $USER->id;
$current_page    = null;
$plugin_info     = null;
$return_url      = new moodle_url('/user/profile.php',array('id' => $user_id));

// Settings page
$PAGE->set_url(new moodle_url('/local/express_login/index.php'));
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

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}else if (!Express_Login::IsActivate()) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('express_disable','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();
    die();
}

// Check users
if ($id && ($user_id != $id)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('err_express_access','local_express_login'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
    echo $OUTPUT->footer();
    die();
}

// Plugin info
$plugin_info     = get_config('local_express_login');

// Add form
$exists_express = Express_Login::Exists_ExpressLogin($user_id);
if ($exists_express) {

    $force = Express_Login::Force_NewExpressLogin($plugin_info,$user_id);
    if ($force) {
        $url = new moodle_url('/local/express_login/change_express.php',array('id' => $user_id));
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('pin_code_expired','local_express_login'), 'notifysuccess');
        echo $OUTPUT->continue_button($url);
        echo $OUTPUT->footer();
        die();
    }else {
        $form = new express_login_link_form(null,null);
        if ($form->is_cancelled()) {
            $_POST = array();
            redirect($return_url);
        }

        echo $OUTPUT->header();
        $form->display();
        echo $OUTPUT->footer();
    }//if_force
}else {
    $form = new express_login_form(null,$plugin_info);

    if ($form->is_cancelled()) {
        $_POST = array();
        redirect($return_url);
    }else if ($data = $form->get_data()) {
        // Generate express login
        $express_login = Express_Login::Generate_ExpressLink($data,false);

        if ($express_login) {
            $form_link = new express_login_link_form(null,null);
            if ($form_link->is_cancelled()) {
                $_POST = array();
                redirect($return_url);
            }

            echo $OUTPUT->header();
            $form_link->display();
            echo $OUTPUT->footer();
        }else {
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('err_generic','local_express_login'), 'notifysuccess');
            echo $OUTPUT->continue_button($return_url);
            echo $OUTPUT->footer();
        }
    }else {
    echo $OUTPUT->header();
        $form->display();
    echo $OUTPUT->footer();
    }//if_form
}//if_exists


