<?php
/**
 * Municipality Block - Edit Muni Form
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @updateDate      20/08/2014
 * @author          efaktor     (fbv)
 */

require_once('../../config.php');
require_once('edit_muni_form.php');
require('municipalitylib.php');


require_login();

/* Start the page */
$site_context = context_system::instance();


//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_url('/blocks/municipality/edit_muni.php');
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
    Municipality::municipality_InsertMunicipality_UserProfile($USER->id,$data->sel_muni);

    $_POST = array();
    redirect($CFG->wwwroot);
}//if_else

/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title', 'block_municipality'));

$form->display();

/* Print Footer */
echo $OUTPUT->footer();