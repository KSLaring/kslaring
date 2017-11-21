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

global $USER,$PAGE,$OUTPUT;

// Params
$url        = new moodle_url('/local/wsks/slaves/classes/add_slave.php');
$returnUrl  = new moodle_url('/local/wsks/slaves/classes/slaves.php');
$context    = context_system::instance();

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Capability
require_capability('local/wsks:manage',$context);

// Start page
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);

// Form
$form = new add_slave_form(null,null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {
    // Create new slave system
    Slaves::Process_New_SlaveSystem($data);
    redirect($returnUrl);
}

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('new_slave','local_wsks'));

$form->display();

// Footer
echo $OUTPUT->footer();
