<?php
/**
 * Fellesdata Integration - Unmap
 *
 * @package         local/fellesdata
 * @subpackage      unmap
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    17/11/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('unmap_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$url    = new moodle_url('/local/fellesdata/unmap/unmap.php');
$urlOrg = new moodle_url('/local/fellesdata/unmap/unmap_org.php');
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
$PAGE->navbar->add(get_string('nav_unmap','local_fellesdata'));
$PAGE->navbar->add( get_string('nav_unmap_org','local_fellesdata'));

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

$form    = new unmap_org_form(null,null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if ($data = $form->get_data()) {
    $SESSION->pattern = $data->pattern;

    $urlOrg->param('le',$data->level);
    redirect($urlOrg);
}//if_Else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('header_unmap_fellesdata', 'local_fellesdata'));

$form->display();

/* Footer   */
echo $OUTPUT->footer();