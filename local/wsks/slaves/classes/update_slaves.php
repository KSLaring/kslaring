<?php
/**
 * Web Services KS - Update Slaves
 *
 * @package         local/wsks
 * @subpackage      slaves/classes
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    08/11/2016
 * @author          eFaktor     (fbv)
 */

require_once( '../../../../config.php');
require_once('slaves_forms.php');
require_once('../lib/slaveslib.php');

/* PARAMS */
$url        = new moodle_url('/local/wsks/slaves/classes/update_slaves.php');
$returnUrl  = new moodle_url('/local/wsks/slaves/classes/slaves.php');
$returnErr  = new moodle_url('/local/wsks/slaves/classes/result.php');
$context    = context_system::instance();
require_login();

/* Capability   */
require_capability('local/wsks:manage',$context);

/* Start Page */
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);

/**
 * Form
 */
$form = new update_slaves_systems_form(null,null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {
    /**
     * Update slaves systems
     */
    $msgErr = Slaves::Process_Update_SlavesSystems($data->services);
    $returnErr->param('er',$msgErr);
    redirect($returnErr);
}

/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('update_slaves','local_wsks'));

$form->display();

/* Print Footer */
echo $OUTPUT->footer();