<?php
/**
 * Fellesdata Integration - Mapping New Companies
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
$level          = required_param('le',PARAM_INT);
$org            = optional_param('o',0,PARAM_INT);
$parent         = optional_param('ks_parent',0,PARAM_INT);
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);

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
if (isset($SESSION->FS_COMP)) {
    if (count($SESSION->FS_COMP)) {
        $toMatch = implode(',',array_keys($SESSION->FS_COMP));
    }
}

$form = new organization_new_map_form(null,array($level,$toMatch,$addSearch,$removeSearch,$org));
if ($form->is_cancelled()) {
    $_POST = array();
    /* Check if all elements from FS_COMP has been added */
    /* Yes -> Nothing   */
    /* No -> Delete     */

    if ($org) {
        redirect($urlOrg);
        unset($SESSION->FS_COMP);
    }else {
        redirect($return);
        unset($SESSION->FS_COMP);
    }
}else if($data = $form->get_data()) {
    if (!empty($data->add_sel)) {
        if (isset($data->acompanies)) {
            /* Update Parent --> Parent */
            FS_MAPPING::UpdateKSParent($data->acompanies,$data->ks_parent);
        }//if_addselect
    }

    if (!empty($data->remove_sel)) {
        if (isset($data->scompanies)) {
            /* Update Parent --> 0 */
            FS_MAPPING::UpdateKSParent($data->scompanies,0);
        }//if_addselect
    }

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