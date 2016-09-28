<?php
/**
 * Waiting List - Manual submethod load organization
 *
 * @package         enrol/waitinglist
 * @subpackage      yui
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */
define('AJAX_SCRIPT', true);

require('../../config.php');
require_once($CFG->dirroot . '/enrol/invoice/invoicelib.php');

/* PARAMS   */
$two         = required_param('two',PARAM_INT);
$three       = required_param('three',PARAM_INT);

$json           = array();
$data           = array();
$infoInvoice    = null;

$context        = context_system::instance();
$url            = new moodle_url('/enrol/waitinglist/invoicedata.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('invoice' => null);

$infoInvoice = Invoices::GetInvoiceData($two,$three);

if (!$infoInvoice) {
    $infoInvoice = new stdClass();
    $infoInvoice->tjeneste  = null;
    $infoInvoice->ansvar    = null;
}

/* Add Company*/
$data['invoice'] = $infoInvoice;

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));