<?php
/**
 * Fellesdata Mapping Companies Search
 *
 * Description
 *
 * @package         local
 * @subpackage      fellesdata/mapping
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/06/2016
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once($CFG->libdir . '/adminlib.php');

/* PARAMS   */
$parent         = optional_param('parent',0,PARAM_INT);
$level          = required_param('level',PARAM_INT);
$search         = optional_param('search',null,PARAM_TEXT);
$selectorId     = required_param('selectorid',PARAM_ALPHANUM);

$optSelector    = null;
$class          = null;
$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/fellesdata/mapping/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Validate if exits the selector   */
if (!isset($USER->search_selectors[$selectorId])) {
    print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->search_selectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

/* Get Data */
$data       = array('name' => $optSelector['name'], 'comp' => array());
$results = FS_MAPPING::$class($level,$search,$parent);
foreach ($results as $key => $name) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $key;
    $infoCompany->name      = $name;

    /* FS Company - With Parents */
    $data['comp'][$infoCompany->id] = $infoCompany;
}

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));