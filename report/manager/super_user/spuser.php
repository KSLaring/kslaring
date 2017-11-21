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
 * Report Competence Manager - Super Users.
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    14/10/2015
 * @author          eFaktor     (fbv)
 */

global $CFG,$SESSION,$PAGE,$USER,$SITE,$OUTPUT;

require_once('../../../config.php');
require_once('spuser_form.php');
require_once('spuserlib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSelected    = optional_param_array('addselect',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$url            = new moodle_url('/report/manager/super_user/spuser.php');
$indexUrl       = new moodle_url('/report/manager/index.php');
$returnUrl      = new moodle_url('/report/manager/company_structure/company_structure.php');
$site_context   = context_system::instance();
$levelZero      = null;
$levelOne       = null;
$levelTwo       = null;
$levelThree     = null;

// Page settings
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('company_structure','report_manager'),$returnUrl);
$PAGE->navbar->add(get_string('spuser','report_manager'));

unset($SESSION->parents);

$PAGE->verify_https_required();

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}
require_capability('report/manager:edit', $site_context);

// Form
$form = new manager_spuser_form(null,array($addSearch,$removeSearch,$addSelected,$removeSelected));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {
    // Get levels super user
    list($levelZero,$levelOne,$levelTwo,$levelThree) = SuperUser::GetLevels_SuperUser($data);

    if (!empty($data->add_sel)) {
        if ($addSelected) {
            // Create super user
            SuperUser::AddSuperUsers($addSelected,$levelZero,$levelOne,$levelTwo,$levelThree);
        }//if_addselect
    }

    if (!empty($data->remove_sel)) {
        if ($removeSelected) {
            // Remove super user
            SuperUser::RemoveSuperUsers($removeSelected,$levelZero,$levelOne,$levelTwo,$levelThree);
        }//if_addselect
    }

    $_POST = array();
}//if_else

// Header
echo $OUTPUT->header();
// Tabs
$current_tab = 'spuser';
$show_roles = 1;
require('../tabs.php');

echo $OUTPUT->heading(get_string('spuser', 'report_manager'));

$form->display();

// Initialise Selectors
SuperUser::Init_SuperUsers_Selectors($addSearch,$removeSearch,$removeSelected);
// Initialise Organization Structure
SuperUser::Init_Organization_Structure();

// Footer
echo $OUTPUT->footer();