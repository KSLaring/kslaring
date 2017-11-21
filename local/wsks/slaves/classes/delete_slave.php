<?php
/**
 * Web Services KS - Delete Slave
 *
 * @package         local/wsks
 * @subpackage      slaves/classes
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    08/11/2016
 * @author          eFaktor     (fbv)
 */
require_once( '../../../../config.php');
require_once('../lib/slaveslib.php');

global $USER,$PAGE,$OUTPUT;

// Params
$slaveId        = required_param('id',PARAM_INT);
$confirm        = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/local/wsks/slaves/classes/delete_slave.php',array('id' => $slaveId));
$returnUrl      = new moodle_url('/local/wsks/slaves/classes/slaves.php');
$confirmUrl     = new moodle_url('/local/wsks/slaves/classes/delete_slave.php',array('id' => $slaveId,'confirm' => true));
$strMessages    = null;
$slave          = null;
$context        = context_system::instance();

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

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('del_slave','local_wsks'));

if ($confirm) {
    // Delete slave system
    if (Slaves::Delete_SlaveSystem($slaveId)) {
        echo $OUTPUT->notification(get_string('slave_deleted','local_wsks'), 'notifysuccess');
        echo $OUTPUT->continue_button($returnUrl);
    }else {
        echo $OUTPUT->notification(get_string('delete_error','local_wsks'), 'notifysuccess');
        echo $OUTPUT->continue_button($returnUrl);
    }
}else {
    // Ask for confirmation
    $slave = Slaves::GetSlave($slaveId);
    $strMessages = get_string('delete_slave_sure','local_wsks',$slave->slave);
    echo $OUTPUT->confirm($strMessages,$confirmUrl,$returnUrl);
}

// Footer
echo $OUTPUT->footer();