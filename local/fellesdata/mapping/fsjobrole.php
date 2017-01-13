<?php
/**
 * Fellesdata mapping job roles
 *
 * Description
 *
 * @package         local
 * @subpackage      fellesdata/mapping
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/06/2016
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once($CFG->libdir . '/adminlib.php');

/* PARAMS */
$selector         = optional_param('selector',0,PARAM_INT);
$ks_jobrole       = required_param('ks_jobrole',PARAM_INT);

$json           = array();
$data           = array();
$infoJR         = null;
$mapped         = null;
$nomapped       = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/fellesdata/mapping/fsjobrole.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Data */
$data       = array('mapped' => array(), 'nomapped' => array());

/* Job Roles Mapped */
$mapped = FS_MAPPING::FindFSJobroles_Mapped($ks_jobrole,null);
foreach ($mapped as $key => $name) {
    /* Info Company */
    $infoJR            = new stdClass;
    $infoJR->id        = $key;
    $infoJR->name      = $name;

    /* FS Company - With Parents */
    $data['mapped'][$infoJR->name] = $infoJR;
}

/* Job Roles No Mapped */
$nomapped = FS_MAPPING::FindFSJobroles_NO_Mapped($ks_jobrole,null);
foreach ($nomapped as $key => $name) {
    /* Info Company */
    $infoJR            = new stdClass;
    $infoJR->id        = $key;
    $infoJR->name      = $name;

    /* FS Company - With Parents */
    $data['nomapped'][$infoJR->name] = $infoJR;
}


/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));
