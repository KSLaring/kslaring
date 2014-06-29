<?php

/**
 * Local Municipality Block  - Edit Municipality
 *
 * @package         local
 * @subpackage      muni_block
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @author          efaktor     (fbv)
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('locallib.php');
require_once('edit_muni_form.php');

require_login();

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_url('/local/muni_block/edit_muni.php');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

$PAGE->verify_https_required();

/* SHOW FORM */
$form = new municipality_block_form(null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($CFG->wwwroot);
}else if ($data = $form->get_data()){
    /* Insert the Municipality to the user profile */
    local_muni_insert_municipality_user_profile($USER->id,$data->sel_muni);

    $_POST = array();
    redirect($CFG->wwwroot);
}//if_else

/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title', 'local_muni_block'));

$form->display();

/* Print Footer */
echo $OUTPUT->footer();