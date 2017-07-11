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
 * Fellesdata Integration - Unconnected KS Organizations
 *
 * @package         local/fellesdata
 * @subpackage      unconnected
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/02/2017
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/unconnectedlib.php');
require_once('../lib/unmaplib.php');
require_once('unconnected_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$level          = optional_param('level',0,PARAM_INT);
$addSearch      = optional_param('aunconnect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('sunconnect_searchtext', '', PARAM_RAW);
$url            = new moodle_url('/local/fellesdata/unconnected/unconnected.php');
$companies      = null;

// System context
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* ADD require_capability */
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Form
$form = new unconnected_ks_form(null,array($level,$addSearch,$removeSearch));
if ($form->is_cancelled()) {
    $_POST = array();
}else if($data = $form->get_data()) {
    // To be unconnected (deleted from KS)
    if (!empty($data->add_sel)) {
        if (isset($data->aunconnect)) {
            // Add entry in mdl_ksfs_org_unmap
            KS_UNCONNECT::add_ks_to_unconnect($data->aunconnect);
        }//if_addselect
    }//if_add_sel

    // From unconnected to unconnect
    if (!empty($data->remove_sel)) {
        if (isset($data->sunconnect)) {
            //Delete from mdl_ksfs_org_unmap
            KS_UNCONNECT::remove_ks_to_unconnect($data->sunconnect);
        }//if_addselect
    }//if_add_sel

    $form = new unconnected_ks_form(null,array($level,$addSearch,$removeSearch));
}

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_unconnected', 'local_fellesdata'));

$form->display();

// Ini selector
KS_UNCONNECT::ini_KS_unconnect_selectors('level','sunconnect',$removeSearch,'aunconnect',$addSearch);

// Footer
echo $OUTPUT->footer();