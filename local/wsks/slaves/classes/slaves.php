<?php
/**
 * Web Services KS - Main page
 *
 * @package         local/wsks
 * @subpackage      slaves/classes
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
 * @author          eFaktor     (fbv)
 */

require_once( '../../../../config.php');
require_once('../lib/slaveslib.php');

global $USER,$PAGE,$OUTPUT;

// Params
$url        = new moodle_url('/local/wsks/slaves/classes/slaves.php');
$context    = context_system::instance();
$lstSlaves  = null;

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

// Slaves systems
$lstSlaves = Slaves::GetSlavesSystems();
$out = Slaves::Display_SlavesSystems($lstSlaves);

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('lst_slaves','local_wsks'));

echo $out;

// Footer
echo $OUTPUT->footer();
