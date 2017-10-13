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
 * Fellesdata Integration - Mapping Organizations
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/06/2016
 * @author          eFaktor     (fbv)
 *
 */

global $CFG, $PAGE,$SITE,$OUTPUT,$SESSION;
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('mapping_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// Params
$level  = optional_param('le',0,PARAM_INT);
$url    = new moodle_url('/local/fellesdata/mapping/mapping_org.php');
$urlOrg = new moodle_url('/local/fellesdata/mapping/organization.php');
$return = $CFG->wwwroot;

// Set page
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('nav_mapping','local_fellesdata'));

// Capability
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Clean data
unset($SESSION->FS_COMP);
unset($SESSION->notIn);

$form    = new map_org_form(null,$level);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if ($data = $form->get_data()) {
    $SESSION->pattern = $data->pattern;

    $urlOrg->param('le',$data->hlevel);
    $urlOrg->param('ks',$data->hparent);

    $SESSION->fsparents = json_decode($data->hfsparents,true);
    redirect($urlOrg);
}//if_Else

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_org', 'local_fellesdata'));

$form->display();

FS_MAPPING::init_fsks_parent_selector('level','hlevel','ksparent','hparent');
// Footer
echo $OUTPUT->footer();