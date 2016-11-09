<?php
/**
 * Web Services KS - Add Slave
 *
 * @package         local/wsks
 * @subpackage      slaves/classes
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
 * @author          eFaktor     (fbv)
 */

require_once( '../../../../config.php');
require_once('slaves_forms.php');
require_once('../lib/slaveslib.php');

/* PARAMS */
$url        = new moodle_url('/local/wsks/slaves/classes/add_slave.php');
$returnUrl  = new moodle_url('/local/wsks/slaves/classes/slaves.php');
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
$form = new add_slave_form(null,null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {
    /**
     * Create new slave system
     */
    Slaves::Process_New_SlaveSystem($data);
    redirect($returnUrl);
}

/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('new_slave','local_wsks'));

$form->display();

/* Print Footer */
echo $OUTPUT->footer();
