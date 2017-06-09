<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Course Page - Sectors
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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