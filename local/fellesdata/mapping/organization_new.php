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
 * Fellesdata Integration - Mapping New Companies
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    07/02/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('mapping_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$level          = required_param('le',PARAM_INT);
$org            = optional_param('o',0,PARAM_INT);
$parent         = optional_param('ks_parent',0,PARAM_INT);
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSearch      = optional_param('acompanies_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('scompanies_searchtext', '', PARAM_RAW);

$pattern    = null;
$url        = new moodle_url('/local/fellesdata/mapping/organization_new.php',array('le' => $level));
$urlOrg     = new moodle_url('/local/fellesdata/mapping/organization.php',array('le' => $level));
$return     = new moodle_url('/local/fellesdata/mapping/mapping_org.php');

$toMatch    = 0;

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('nav_mapping','local_fellesdata'),$return);
$PAGE->navbar->add(get_string('nav_map_org','local_fellesdata'));

/* ADD require_capability */
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* FORM */
$form = new organization_new_map_form(null,array($level,$addSearch,$removeSearch,$org));
if ($form->is_cancelled()) {
    $_POST = array();
    /* Check if all elements from FS_COMP has been added */
    /* Yes -> Nothing   */
    /* No -> Delete     */
    $toDelete = FS_MAPPING::GetNewCompaniesToDelete(implode(',',array_keys($SESSION->FS_COMP)));
    if ($toDelete) {
        FS_MAPPING::clean_new_companies($toDelete);
    }

    unset($SESSION->FS_COMP);
    if ($org) {
        redirect($urlOrg);
    }else {
        redirect($return);
    }
}else if($data = $form->get_data()) {
    if (!empty($data->add_sel)) {
        if (isset($data->acompanies)) {
            /* Update Parent --> Parent */
            FS_MAPPING::UpdateKSParent($data->acompanies,$data->ks_parent);
        }//if_addselect
    }//if_add_sel

    if (!empty($data->remove_sel)) {
        if (isset($data->scompanies)) {
            /* Update Parent --> 0 */
            FS_MAPPING::UpdateKSParent($data->scompanies,0);
        }//if_addselect
    }//if_remove_sel
    $_POST = array();
}

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_org_new', 'local_fellesdata'));

$form->display();

/* INI SELECTORS    */
FS_MAPPING::Init_FSCompanies_Selectors('ks_parent',$level,'scompanies','acompanies');
FS_MAPPING::Init_Search_Selectors($addSearch,$removeSearch,$level);
/* Footer   */
echo $OUTPUT->footer();