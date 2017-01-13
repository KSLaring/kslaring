<?php
/**
 * Fellesdata mapping companies
 *
 * Description
 *
 * @package         local
 * @subpackage      fellesdata/mapping
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/10/2015
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

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/fellesdata/mapping/fscompany.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Data */
$data       = array('withparent' => array(), 'noparent' => array());

/* FS With Company */
$withParent = FS_MAPPING::FindFSCompanies_WithParent($level,null,$parent);
foreach ($withParent as $key => $name) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $key;
    $infoCompany->name      = $name;

    /* FS Company - With Parents */
    $data['withparent'][$infoCompany->name] = $infoCompany;
}

/* FS Without Company */
$noParent   = FS_MAPPING::FindFSCompanies_WithoutParent($level,null);
foreach ($noParent as $key => $name) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $key;
    $infoCompany->name      = $name;

    /* FS Company - With Parents */
    $data['noparent'][$infoCompany->name] = $infoCompany;
}

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));
