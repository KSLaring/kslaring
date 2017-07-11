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
 * Fellesdata Integration - Unmap organizations
 *
 * @package         local/fellesdata
 * @subpackage      unmap
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/11/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/unmaplib.php');
require_once('unmap_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$level          = required_param('le',PARAM_INT);
$start          = 0;
$step           = 5;
$pattern        = null;
$url            = new moodle_url('/local/fellesdata/unmap/unmap_org.php',array('le' => $level));
$return         = new moodle_url('/local/fellesdata/unmap/unmap.php');
$fsMapped       = null;
$total          = 0;
$infoMapped     = null;
$ref            = null;
$toUnMap        = null;

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('nav_unmap','local_fellesdata'),$return);
$PAGE->navbar->add( get_string('nav_unmap_org','local_fellesdata'));

/* ADD require_capability */
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Get Search Pattern   */
if (isset($SESSION->pattern)) {
    $pattern = $SESSION->pattern;
}//if_pattern

/* To save the new companies */
if (!isset($SESSION->FS_COMP)) {
    $SESSION->FS_COMP = array();
}//isset

if (!isset($SESSION->start_block)) {
    $SESSION->start_block = 0;
}else {
    $start = $SESSION->start_block;
}

list($fsMapped,$total) = FS_UnMap::FSCompaniesMapped($level,$pattern,$start,$step);
if ($total) {
    $form    = new organizations_unmap_form(null,array($level,$pattern,$fsMapped));
    if ($form->is_cancelled()) {
        unset($SESSION->FS_COMP);
        unset($SESSION->notIn);
        unset($SESSION->start_block);

        $_POST = array();
        redirect($return);
    }else if ($data = $form->get_data()) {
        if ((isset($data->submitbutton) && ($data->submitbutton))) {
            /* Unmap process */
            $toUnMap = array();
            foreach ($fsMapped as $infoMapped) {
                /* Referencia   */
                $ref = "ID_FS_KS_" . $infoMapped->id;

                /**
                 * Companies that have to be unmapped
                 */
                if (isset($data->$ref)) {
                    $toUnMap[$infoMapped->id] = $infoMapped;
                }//if_rdf
            }//for_rdo

            /**
             * Unmap companies
             */
            if ($toUnMap) {
                FS_UnMap::UnMap($toUnMap);
            }
        }else if ((isset($data->submitbutton2) && ($data->submitbutton2))) {
            /* Next - First Block no data to unmap  */
            $SESSION->start_block +=  $step;
        }

        redirect($url);
    }//if_Else    
}//if_total

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_unmap_org', 'local_fellesdata'));

if ($total) {
    $form->display();
}else {
    echo $OUTPUT->notification(get_string('none_unmapped','local_fellesdata'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);
}


/* Footer   */
echo $OUTPUT->footer();