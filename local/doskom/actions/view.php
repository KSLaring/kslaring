<?php
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
 * WSDOSKOM - View (Companies or Sources)
 *
 * @package         local
 * @subpackage      doskom/actions
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    04/09/2017
 * @author          eFaktor     (fbv)
 *
 */
require( '../../../config.php' );
require('../lib/actionslib.php');

global $SESSION,$OUTPUT,$PAGE,$CFG,$SITE;

// Params
$type           = required_param('t',PARAM_INT);
$url            = new moodle_url('/local/doskom/actions/view.php');
$urldoskom      = new moodle_url('/admin/settings.php?section=local_doskom');
$context        = CONTEXT_SYSTEM::instance();
$lstsources     = null;
$lstcompanies   = null;
$out            = null;
$strbar         = null;

require_login();
if (isloggedin()) {
    if (!has_capability('local/doskom:manage', $context)) {
        print_error('nopermissions', 'error', '', 'local/doskom:manage');
    }//if_permission
}//if_loggin

// Start PAGE
$PAGE->https_required();

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->verify_https_required();
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('admin-setting-local_doskom');
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('plugins','admin'));
$PAGE->navbar->add(get_string('localplugins'));
$PAGE->navbar->add(get_string('pluginname','local_doskom'),$urldoskom);
switch ($type) {
    case SOURCE:
        $strbar = get_string('headersource','local_doskom');

        break;
    case COMPANIES:
        $strbar = get_string('headercompany','local_doskom');

        break;
}
$PAGE->navbar->add($strbar);

// Get info to display

switch ($type) {
    case SOURCE:
        $lstsources = actionsdk::get_sources_companies();
        // Get sources table to display
        $out = actionsdk::display_sources($lstsources);

        break;
    case COMPANIES:
        $lstcompanies = actionsdk::get_companies_sources();
        // Get companies table to display
        $out = actionsdk::display_companies($lstcompanies);

        break;
    default:
        break;
}//switch_type


// Header
echo $OUTPUT->header();

echo $out;

// Footer
echo $OUTPUT->footer();