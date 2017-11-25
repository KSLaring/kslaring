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
 * WSDOSKOM - Comapnies
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
require('actions_forms.php');

global $SESSION,$OUTPUT,$PAGE,$CFG,$SITE,$USER;

// Params
$action     = required_param('a',PARAM_INT);
$id         = optional_param('id',0,PARAM_INT);
$dk         = optional_param('dk',0,PARAM_INT);
$company    = null;
$url        = new moodle_url('/local/doskom/actions/companies.php');
$urldoskom  = new moodle_url('/admin/settings.php?section=local_doskom');
$urlview    = new moodle_url('/local/doskom/actions/view.php',array('t' =>COMPANIES));
$context    = CONTEXT_SYSTEM::instance();

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
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
$PAGE->navbar->add(get_string('headercompany','local_doskom'),$urlview);

// Get data connected
if ($id) {
    $company = actionsdk::get_doskom_company($id);
}


// Action to carry out
switch ($action) {
    case ADD_COMPANY:
    case EDIT_COMPANY:
        // form
        $form = new company_form(null,array($company,$action));
        if ($form->is_cancelled()) {
            $_POST = array();
            redirect($urlview);
        }else if($data = $form->get_data()) {
            // Process the action
            actionsdk::process_action_company($action,$data);

            $_POST = array();
            redirect($urlview);
        }//if_else

        break;
    case ACTIVATE_SOURCE:
    case DEACTIVATE_SOURCE:
        // Process the action
        if ($id) {
            actionsdk::process_action_company($action,$company);
        }
        redirect($urlview);

        break;
}//action

// Header
echo $OUTPUT->header();

switch ($action) {
    case ADD_COMPANY:
    case EDIT_COMPANY:
        $form->display();

        break;

    default:
        break;
}//switch_action

// Footer
echo $OUTPUT->footer();