<?php
/**
 * Fellesdata ks unconnected - javascript
 *
 * Description
 *
 * @package         local
 * @subpackage      fellesdata/unconnect
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    16/02/2017
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);


require_once('../../../config.php');
require_once('../lib/unconnectedlib.php');
require_once($CFG->libdir . '/adminlib.php');

/* PARAMS */
$level          = required_param('level',PARAM_INT);
$removesearch   = required_param('removesearch',PARAM_TEXT);
$addsearch      = required_param('addsearch',PARAM_TEXT);
$json           = array();
$data           = array();
$info           = null;
$unconnected    = null;
$tounconnect    = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/fellesdata/unconnected/ksunconnect.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

// Check access
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Data */
$data       = array('unconnected' => array(), 'tounconnect' => array());

$tounconnect = KS_UNCONNECT::find_ks_to_unconnect($level,$removesearch);
$unconnected = KS_UNCONNECT::find_ks_unconnected($level,$addsearch);
// KS to unconnect
if ($tounconnect) {
    foreach ($tounconnect as $key => $name) {
        /* Info Company */
        $info            = new stdClass;
        $info->id        = $key;
        $info->name      = $name;

        $data['tounconnect'][$info->id] = $info;
    }//for    
}//if_tounconnect

// KS unconnected
if ($unconnected) {
    foreach ($unconnected as $key => $name) {
        /* Info Company */
        $info            = new stdClass;
        $info->id        = $key;
        $info->name      = $name;

        $data['unconnected'][$info->id] = $info;
    }//for   
}//if_unconnected


/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));