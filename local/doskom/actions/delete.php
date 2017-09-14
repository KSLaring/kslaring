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
 * WSDOSKOM - Delete source or company
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
$id             = required_param('id',PARAM_INT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/local/doskom/actions/delete.php',array('t' => $type, 'id' => $id));
$confirm_url    = new moodle_url('/local/doskom/actions/delete.php',array('t' => $type, 'id' => $id,'confirm' => true));
$urldoskom      = new moodle_url('/admin/settings.php?section=local_doskom');
$urlview        = new moodle_url('/local/doskom/actions/view.php',array('t' =>$type));
$context        = CONTEXT_SYSTEM::instance();
$data           = null;
$strconfirm     = null;
$strmessage     = null;
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
$PAGE->navbar->add($strbar,$urlview);


// Header
echo $OUTPUT->header();

if ($confirmed) {
    // Delete data
    switch ($type) {
        case SOURCE:
            $data = actionsdk::get_source($id);
            if (actionsdk::process_action_source(DELETE_SOURCE,$data)) {
                $strmessage = get_string('deletedendpoint','local_doskom',$data->api);
            }else {
                $strmessage = get_string('error_deleted','local_doskom');
            }
            echo $OUTPUT->notification($strmessage, 'notifysuccess');
            echo $OUTPUT->continue_button($urlview);


            break;
        case COMPANIES:
            $data = actionsdk::get_doskom_company($id);
            if (actionsdk::process_action_company(DELETE_COMPANY,$data)) {
                $strmessage = get_string('deletedcompany','local_doskom',$data->name);
            }else {
                $strmessage = get_string('error_deleted','local_doskom');
            }
            echo $OUTPUT->notification($strmessage, 'notifysuccess');
            echo $OUTPUT->continue_button($urlview);

            break;
    }//switch_type
}else {
    // Get data connected
    switch ($type) {
        case SOURCE:
            $data = actionsdk::get_source($id);
            // companies connected with or not
            if ($data->companies) {
                $strconfirm = get_string('delete_endpoint_companies_are_you_sure','local_doskom',$data->api);
            }else {
                $strconfirm = get_string('delete_endpoint_are_you_sure','local_doskom',$data->api);
            }


            break;
        case COMPANIES:
            $data = actionsdk::get_doskom_company($id);
            $strconfirm = get_string('delete_company_are_you_sure','local_doskom',$data->name);
            break;
    }//switch_type

    echo $OUTPUT->confirm($strconfirm,$confirm_url,$urlview);
}

// Footer
echo $OUTPUT->footer();

