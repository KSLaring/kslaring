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
 * Fellesdata Integration - Mapping Job Roles
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/02/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('../lib/fellesdatalib.php');
require_once('mapping_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$jobrole        = optional_param('ks_jobrole',0,PARAM_INT);
$addSearch      = optional_param('ajobroles_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('sjobroles_searchtext', '', PARAM_RAW);

$url        = new moodle_url('/local/fellesdata/mapping/jobroles.php');
$start      = 0;
$step       = 5;
$jrToMap    = null;
$total      = 0;
$matched    = false;

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('nav_mapping','local_fellesdata'));
$PAGE->navbar->add(get_string('nav_map_jr','local_fellesdata'));

/* ADD require_capability */
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* FORM */
$form = new jobrole_map_form(null,array($addSearch,$removeSearch));
if ($form->is_cancelled()) {
    $_POST = array();
}else if($data = $form->get_data()) {
    if (!empty($data->add_sel)) {
        if (isset($data->ajobroles)) {
            FS_MAPPING::MappingFSJobRoles($data->ajobroles,$data->ks_jobrole,MAP);
        }//if_addselect
    }//if_add_sel

    if (!empty($data->remove_sel)) {
        if (isset($data->sjobroles)) {
            FS_MAPPING::MappingFSJobRoles($data->sjobroles,$data->ks_jobrole,UNMAP);
        }//if_addselect
    }//if_remove_sel
    $_POST = array();
}
/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_jr', 'local_fellesdata'));

$form->display();

/* INI SELECTORS    */
FS_MAPPING::Ini_FSJobroles_Selectors('ks_jobrole','sjobroles','ajobroles');
FS_MAPPING::Init_Search_Jobroles($addSearch,$removeSearch);
/* Footer   */
echo $OUTPUT->footer();

