<?php
/**
 * Waiting List - Manual submethod load organization
 *
 * @package         enrol/waitinglist
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */
define('AJAX_SCRIPT', true);

require('../../../config.php');
require_once($CFG->dirroot . '/enrol/invoice/invoicelib.php');

/* PARAMS   */
$two         = required_param('two',PARAM_INT);
$three       = required_param('three',PARAM_INT);

$json           = array();
$data           = array();
$infoInvoice    = null;

$context        = context_system::instance();
$url            = new moodle_url('/enrol/waitinglist/invoice/invoicedata.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('invoice' => null);

$infoInvoice = Invoices::get_invoice_data($two,$three);

if (!$infoInvoice) {
    $infoInvoice = new stdClass();
    $infoInvoice->resource_number   = null;
    // ansvar
    $infoInvoice->ansvar            = null;
    if (isset($_COOKIE['ansvar_selected']) && $_COOKIE['ansvar_selected']) {
        $infoInvoice->ansvar = $_COOKIE['ansvar_selected'];
        $infoInvoice->ansvar_active = 1;
    }
    // tjeneste
    $infoInvoice->tjeneste          = null;
    if (isset($_COOKIE['tjeneste_selected']) && $_COOKIE['tjeneste_selected']) {
        $infoInvoice->tjeneste = $_COOKIE['tjeneste_selected'];
        $infoInvoice->tjeneste_active = 1;
    }
}else {
    $infoInvoice->ansvar_active     = 0;
    $infoInvoice->tjeneste_active   = 0;
}

if (isset($SESSION->resource_number) && $SESSION->resource_number) {
    $infoInvoice->resource_number = $SESSION->resource_number;
}
unset($SESSION->resource_number);

/* Add Company*/
$data['invoice'] = $infoInvoice;

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));