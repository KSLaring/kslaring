<?php
/**
 * Course Page - Sectors
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    21/03/2016
 * @author          eFaktor     (fbv)
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('locallib.php');

/* PARAMS   */
$location       = required_param('lo',PARAM_INT);
$json           = array();
$data           = array();
$infoSector     = null;
$lstSectors     = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/course_page/sectors.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get sectors */
$data       = array('items' => array());
$lstSectors = course_page::get_sectors_locations_list($location);

foreach ($lstSectors as $id => $sector) {
    /* Info Sector */
    $infoSector            = new stdClass;
    $infoSector->id        = $id;
    $infoSector->sector    = $sector;

    /* Add Company*/
    $data['items'][] = $infoSector;
}


/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));