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