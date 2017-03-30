<?php
// This file is part of ksl
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../lib/kslib.php');

/* PARAMS   */
$parent         = required_param('parent', PARAM_INT);
$level          = required_param('level', PARAM_INT);
$myhierarchy    = null;
$mylevelzero    = null;
$mylevelone     = null;
$mylevelwwo     = null;
$mylevelthree   = null;

$json           = array();
$data           = array();
$info           = null;

$context        = context_system::instance();
$url            = new moodle_url('/local/historical/reports/organization.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();


/* Get Data */
$data       = array('name' => MY_LEVEL_STRUCTURE . $level, 'items' => array(), 'clean' => array());
$toclean    = array();

switch ($level) {
    case 0:
        $toclean[0] = MY_LEVEL_STRUCTURE . 0;
        $toclean[1] = MY_LEVEL_STRUCTURE . 1;
        $toclean[2] = MY_LEVEL_STRUCTURE . 2;
        $toclean[3] = MY_LEVEL_STRUCTURE . 3;

        break;
    case 1:
        $toclean[0] = MY_LEVEL_STRUCTURE . 1;
        $toclean[1] = MY_LEVEL_STRUCTURE . 2;
        $toclean[2] = MY_LEVEL_STRUCTURE . 3;

        break;
    case 2:
        $toclean[0] = MY_LEVEL_STRUCTURE . 2;
        $toclean[1] = MY_LEVEL_STRUCTURE . 3;

        break;
    case 3:

        break;
}//switch
$data['clean'] = $toclean;

/* Get Companies List   */
if ($parent) {

    $options = ksl::get_companies_level_lst($level, $parent);
} else {
    // First element of the list.
    $options[0] = get_string('selectone', 'local_ksl');
}//if_parent

foreach ($options as $companyid => $company) {

    // Info Company!
    $info            = new stdClass;
    $info->id        = $companyid;
    $info->name      = $company;

    // Add Company!
    $data['items'][$info->name] = $info;
}

// Encode and send!
$json[] = $data;
echo json_encode(array('results' => $json));

