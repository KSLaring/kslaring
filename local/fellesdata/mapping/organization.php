<?php
/**
 * Fellesdata Integration - Mapping Companies
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
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
$level      = required_param('le',PARAM_INT);
$pattern    = null;
$url        = new moodle_url('/local/fellesdata/mapping/organization.php',array('le' => $level));
$urlNew     = new moodle_url('/local/fellesdata/mapping/organization_new.php',array('le' => $level));
$return     = new moodle_url('/local/fellesdata/mapping/mapping_org.php');
$start      = 0;
$step       = 5;
$fsToMap    = null;
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

/* Get Search Pattern   */
if (isset($SESSION->pattern)) {
    $pattern = $SESSION->pattern;
}//if_pattern

/* To save the new companies */
if (!isset($SESSION->FS_COMP)) {
    $SESSION->FS_COMP = array();
}//isset

if (!isset($SESSION->notIn)) {
    $SESSION->notIn = array();
}

/* Get Companies to Map */
$notIn = 0;
if (($SESSION->notIn) && count($SESSION->notIn)) {
    $notIn = implode(',',$SESSION->notIn);
}

list($fsToMap,$total) = FS_MAPPING::FSCompaniesToMap($level,$pattern,$notIn,$start,$step);
$form    = new organization_map_form(null,array($level,$pattern,$fsToMap,$total));
if ($form->is_cancelled()) {
    unset($SESSION->FS_COMP);
    unset($SESSION->notIn);

    $_POST = array();
    redirect($return);
}else if ($data = $form->get_data()) {
    /* Matching   */
    list($matched,$notIn) = FS_MAPPING::MappingFSCompanies($fsToMap,$data);

    /* Redirect */
    if ($matched) {
        if (count($notIn)) {
            $SESSION->notIn = $notIn;
        }

        if (isset($SESSION->FS_COMP)) {
            if ($SESSION->FS_COMP) {
                $urlNew->param('o',1);
                redirect($urlNew);
            }
        }//FS_COMP

        redirect($url);
    }//matched
}//if_Else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_org', 'local_fellesdata'));

if ($fsToMap) {
    $form->display();
}else {
    if (($SESSION->notIn) && count($SESSION->notIn)) {
        unset($SESSION->FS_COMP);
        unset($SESSION->notIn);
        redirect($url);
    }else {
        unset($SESSION->FS_COMP);
        unset($SESSION->notIn);
        echo $OUTPUT->notification(get_string('no_companies_to_map','local_fellesdata'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
    }
}

/* Footer   */
echo $OUTPUT->footer();


