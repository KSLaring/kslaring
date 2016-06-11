<?php
/**
 * Fellesdata Integration - Mapping Job Roles
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
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
$level      = required_param('le',PARAM_INT);
$generic    = required_param('g',PARAM_INT);

$pattern    = null;
$url        = new moodle_url('/local/fellesdata/mapping/jobroles.php',array('le' => $level,'g' => $generic));
$return     = new moodle_url('/local/fellesdata/mapping/mapping_jr.php');
$start      = 0;
$step       = 5;
$jrToMap    = null;
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
$PAGE->navbar->add(get_string('nav_map_jr','local_fellesdata'));

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

if (!isset($SESSION->notIn)) {
    $SESSION->notIn = array();
}

/* Get Job Roles to Map */
$jrToMap = FS_MAPPING::FSJobRolesToMap($level,$pattern,$generic,$start,$step);
$form = new jobroles_map_form(null,array($level,$pattern,$generic,$jrToMap));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if ($data = $form->get_data()) {
    /* Matching   */
    $matched = FS_MAPPING::MappingFSJobRoles($jrToMap,$data);

    /* Redirect */
    if ($matched) {
        redirect($url);
    }//matched
}//if_Else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_jr', 'local_fellesdata'));

if ($jrToMap) {
    $form->display();
}else {
    if (($SESSION->notIn) && count($SESSION->notIn)) {
        unset($SESSION->notIn);
        redirect($url);
    }else {
        unset($SESSION->notIn);
        echo $OUTPUT->notification(get_string('no_jr_to_map','local_fellesdata'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
    }
}

/* Footer   */
echo $OUTPUT->footer();

