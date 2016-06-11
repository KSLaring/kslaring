<?php
/**
 * Fellesdata Integration - Mapping Organizations
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    08/06/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('mapping_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$type   = optional_param('type',0,PARAM_TEXT);
$url    = new moodle_url('/local/fellesdata/mapping/mapping_jr.php');
$urlJR  = new moodle_url('/local/fellesdata/mapping/jobroles.php');
$return = $CFG->wwwroot;

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

/* Java Script  */
$PAGE->requires->js('/local/fellesdata/js/fellesdatajs.js');

/* ADD require_capability */
require_capability('local/fellesdata:manage', $siteContext);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Clean Tables */
unset($SESSION->FS_COMP);
unset($SESSION->notIn);

$form    = new map_org_jr(null,null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if ($data = $form->get_data()) {
    $SESSION->pattern = $data->pattern;

    if (isset($data->jr_generic)) {
        $urlJR->param('g',1);
    }else {
        $urlJR->param('g',0);
    }
    $urlJR->param('le',$data->level);
    redirect($urlJR);
}//if_Else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('nav_map_jr', 'local_fellesdata'));

$form->display();

/* Footer   */
echo $OUTPUT->footer();