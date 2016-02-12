<?php
/**
 * Fellesdata Integration - Mapping
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/02/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once('../lib/mappinglib.php');
require_once('mapping_forms.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS   */
$source = optional_param('src',0,PARAM_INT);
$type   = optional_param('m',0,PARAM_TEXT);
$url    = new moodle_url('/local/fellesdata/mapping/mapping.php',array('src' => $source,'m' => $type));
$urlOrg = new moodle_url('/local/fellesdata/mapping/organization.php');
$urlJR  = new moodle_url('/local/fellesdata/mapping/jobroles.php');

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
FS_MAPPING::CleanOrganizationMapped();
FS_MAPPING::CleanJobRolesMapped();

/* FORM */
$form = new selector_form(null,array($type,$source));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($CFG->wwwroot);
}else if ($data = $form->get_data()) {
    $SESSION->pattern = $data->pattern;

    if (isset($data->mapping_co) && ($data->mapping_co)) {
        $urlOrg->param('le',$data->level);
        redirect($urlOrg);
    }//if_mapping_companies

    if ($data->mapping_jr) {
        $urlJR->param('le',$data->level);
        if (isset($data->jr_generic) && ($data->jr_generic)) {
            $urlJR->param('g',1);
        }else {
            $urlJR->param('g',0);
        }
        redirect($urlJR);
    }//if_mapping_js
}//if_else_form

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('header_fellesdata', 'local_fellesdata'));

$form->display();

/* Footer   */
echo $OUTPUT->footer();






