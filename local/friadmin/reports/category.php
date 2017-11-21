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
 * Friadmin - Category reports (Get subcategories)
 *
 * @package         local/friadmin
 * @subpackage      reports
 * @copyright       2012        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    28/08/2017  (nas)
 * @author          eFaktor
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('lib/categoryrptlib.php');

global $PAGE,$USER;

// Params
$parent         = optional_param('parent', 0,PARAM_INT);
$json           = array();
$data           = null;
$info           = null;
$lstcategories  = null;
$category       = null;
$categories     = array();
$context        = context_system::instance();
$url            = new moodle_url('/local/friadmin/reports/category.php');

// Set page
$PAGE->set_context($context);
$PAGE->set_url($url);

// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Access
require_login();
require_sesskey();

// Get categories
// Categories connected with the user
$mycategories   = friadminrpt::get_my_categories_by_context($USER->id);

//Categories / Subcategories
if ($parent) {
    // Subcateogries connected with the parent
    $lstcategories = friadminrpt::get_my_categories_by_depth($mycategories,null,$parent);
}else {
    // Categories first level
    $lstcategories = friadminrpt::get_my_categories_by_depth($mycategories,1,null);
}//if_parent

// set data to send javascript
$data   = array('categories' => array(),'parentcat' => null);
if ($lstcategories) {
    foreach ($lstcategories as $id => $category) {
        $info       = new stdClass();
        $info->id   = $id;
        $info->name = $category;

        $data['categories'][$info->id] = $info;
    }
}//if_lstcategories

// Parent info
$parentcat = new stdClass();
$parentcat->id      = $parent;
$parentcat->name    = ($parent ? friadminrpt::get_category_name($parent) : '');
$data['parentcat']  = $parentcat;

// Send data
$json[] = $data;
echo json_encode(array('results' => $json));
