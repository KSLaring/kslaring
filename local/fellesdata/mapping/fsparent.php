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

global $CFG, $SITE,$PAGE,$OUTPUT,$SESSION;

require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$level          = required_param('level',PARAM_INT);
$parents        = null;
$json           = array();
$data           = array();

$context        = context_system::instance();
$url            = new moodle_url('/local/fellesdata/mapping/fsparent.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

// Correct access
require_login();
require_sesskey();

echo $OUTPUT->header();

// Data
$data   = array('parents' => array(),'fsparents');
list($parents,$fsparents) = FS_MAPPING::get_parents_synchronized($level);
// set data to send javascript
if ($parents) {
    foreach ($parents as $id => $parent) {
        if ($id != 0) {
            $key = "'" . $parent . "'#" . $id;
        }else {
            $key = $id;
        }
        $data['parents'][$key] = $parent;
    }
    $data['fsparents'] = $fsparents;
}//if_lstcategories

// Encode and send
$json[] = $data;
echo json_encode(array('results' => $json));
