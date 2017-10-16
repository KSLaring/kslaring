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
 * Fellesdata Integration - Mapping Companies
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
global $CFG,$PAGE,$OUTPUT,$SITE,$SESSION;
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('mapping_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// Params
$level      = required_param('le',PARAM_INT);
$parentid   = optional_param('ks',0,PARAM_INT);
$fsparents  = null;
$pattern    = null;
$url        = new moodle_url('/local/fellesdata/mapping/organization.php',array('le' => $level,'ks' => $parentid));
$return     = new moodle_url('/local/fellesdata/mapping/mapping_org.php');
$start      = 0;
$step       = 50;
$fstomap    = null;
$total      = 0;
$matched    = false;
$out        = null;

// Set page
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

// Capability
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

if (isset($SESSION->pattern)) {
    $pattern = $SESSION->pattern;
}//if_pattern

// Get companies to map
$notin      = 0;
if (isset($SESSION->notIn) && count($SESSION->notIn)) {
    $notin = implode(',',$SESSION->notIn);
}

// Get info parent
if (($level > 1) && (!$parentid)) {
    $out = get_string('errorpaernt','local_fellesdata',$level);
}else {
    if (isset($SESSION->fsparents)) {
        $fsparents = $SESSION->fsparents[$parentid];
        echo " --" . $fsparents . "</br>";
    }else {
        $fsparents = 0;
    }

    $parent = FS_MAPPING::get_company_ks_info($parentid);
    list($fstomap,$total) = FS_MAPPING::fs_companies_to_map($level,$parent,$fsparents,$pattern,$notin,$start,$step);

    $form    = new organization_map_form(null,array($level,$parent,$pattern,$fstomap,$total));
    if ($form->is_cancelled()) {
        unset($SESSION->notIn);

        $_POST = array();
        redirect($return);
    }else if ($data = $form->get_data()) {
        // Matching
        list($matched,$notin) = FS_MAPPING::mapping_fs_companies($fstomap,$parent,$data);

        // Redirect
        if ($matched) {
            if (count($notin)) {
                $SESSION->notIn = $notin;
            }

            redirect($url);
        }//matched
    }//if_form
    $out = null;
}


// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_org', 'local_fellesdata'));

if ($out) {
    $return->param('le',$level);
    echo $OUTPUT->notification($out, 'notifysuccess');
    echo $OUTPUT->continue_button($return);
}else {
    $form->display();
}


// Footer
echo $OUTPUT->footer();


