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
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once('expressloginlib.php');
require_once('index_form.php');

require_login();

/* Params   */
$user_id         = required_param('id',PARAM_INT);
$current_user    = ($user_id == $USER->id);
$current_page    = null;
$plugin_info     = null;
$return_url      = new moodle_url('/user/profile.php',array('id' => $user_id));
// Get the profile page.  Should always return something unless the database is broken.
if (!$current_page = my_get_page($user_id, MY_PAGE_PUBLIC)) {
    print_error('mymoodlesetup');
}

/* Settings Page    */
$PAGE->set_context(CONTEXT_USER::instance($user_id));
$PAGE->set_pagelayout('mypublic');
$PAGE->set_pagetype('user-profile');
$PAGE->set_url(new moodle_url('/local/express_login/index.php',array('id' => $user_id)));

// Start setting up the page.
$PAGE->set_subpage($current_page->id);
$PAGE->navbar->add(get_string('pluginname','local_express_login'));

/* Add the blocks that the user can manage  */
if ($current_user) {
    $PAGE->set_blocks_editing_capability('moodle/user:manageownblocks');
} else {
    $PAGE->set_blocks_editing_capability('moodle/user:manageblocks');
}

if (!$current_user) {
    $PAGE->navigation->extend_for_user($USER);
    if ($node = $PAGE->settingsnav->get('userviewingsettings'.$USER->id)) {
        $node->forceopen = true;
    }
} else if ($node = $PAGE->settingsnav->get('usercurrentsettings', navigation_node::TYPE_CONTAINER)) {
    $node->forceopen = true;
}
if ($node = $PAGE->settingsnav->get('root')) {
    $node->forceopen = false;
}

/* Plugins Info */
$plugin_info     = get_config('local_express_login');

/* Add Form     */
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
        }elseif ($data_link = $form->get_data()) {

        }

        echo $OUTPUT->header();
        echo '<script src="express/ZeroClipboard.js"></script>';
        $form->display();
        echo $OUTPUT->footer();
    }//if_force
}else {
    $form = new express_login_form(null,$plugin_info);

    if ($form->is_cancelled()) {
        $_POST = array();
        redirect($return_url);
    }else if ($data = $form->get_data()) {
        /* Generate Express Login */
        $express_login = Express_Login::Generate_ExpressLink($data,false);

        if ($express_login) {
            $form_link = new express_login_link_form(null,null);
            if ($form_link->is_cancelled()) {
                $_POST = array();
                redirect($return_url);
            }

            echo $OUTPUT->header();
                echo '<script src="express/ZeroClipboard.js"></script>';
                $form_link->display();
            echo $OUTPUT->footer();

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
}//if_exists


