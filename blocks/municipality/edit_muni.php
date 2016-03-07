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
require_once('municipalitylib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/profile/field/municipality/field.class.php');

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

/* Check if the Municpality User Profile is installed   */
$exits_MunicipalityProfile = Municipality::ExistsMunicipality_Profile();
if ($exits_MunicipalityProfile) {
    /* SHOW FORM */
    $form = new municipality_block_form(null);
    if ($form->is_cancelled()) {
        $_POST = array();
        redirect($CFG->wwwroot);
    }else if ($data = $form->get_data()){
        // Save custom profile fields data.
        profile_save_data($data);

        $_POST = array();
        redirect($CFG->wwwroot);
    }//if_else
}


/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title', 'block_municipality'));

if ($exits_MunicipalityProfile) {
    $form->display();
}else {
    echo $OUTPUT->notification(get_string('install_municipality','block_municipality'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
}


/* Print Footer */
echo $OUTPUT->footer();