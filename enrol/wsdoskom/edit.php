<?php

/**
 * Single Sign On Enrolment Plugin - Edit Form
 *
 * @package         enrol
 * @subpackage      wsdoskom
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2015
 * @author          efaktor     (fbv)
 *
 * Description
 *  - Add a new instance of approval enrolment to specified course or edits current instance.
 */

require('../../config.php');
require_once('edit_form.php');
require_once($CFG->libdir.'/adminlib.php');
require_once ('../../local/doskom/wsDOSKOMlib.php');
//require_once('locallib.php');

/* PARAMS */
$courseid   = required_param('courseid', PARAM_INT);
/* Current Instance */
$instanceid = optional_param('id', 0, PARAM_INT);

$course     = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context    = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/wsdoskom:config', $context);

$url    = new moodle_url('/enrol/wsdoskom/edit.php', array('courseid'=>$course->id));
$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('wsdoskom')) {
    redirect($return);
}

/* Start Page */
$PAGE->set_url('/enrol/wsdoskom/edit.php', array('courseid'=>$course->id, 'id'=>$instanceid));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/enrol/wsdoskom/js/doskom.js'));
$plugin_config  = get_config('enrol_wsdoskom');
$plugin         = enrol_get_plugin('wsdoskom');
$company_lst    = null;
$instance       = null;

if ($instanceid) {
    $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'wsdoskom', 'id'=>$instanceid), '*', MUST_EXIST);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // no instance yet, we have to add new instance
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id               = null;
    $instance->courseid         = $course->id;
    $instance->customint1       = 0;
}

/* Available Companies  */
if (!isset($SESSION->Companies)) {
    $SESSION->Companies = array();
}//companies

/* Selected Companies   */
if (!isset($SESSION->selCompanies)) {
    $SESSION->selCompanies = array();
}//selCompanies


if ($instanceid) {
    if (!isset($SESSION->selCompanies)) {
        $SESSION->selCompanies = array();
    }//selCompanies
    if ($instance->company) {
        $aux_company = explode(',',$instance->company);
        foreach ($aux_company as $company) {
            $SESSION->selCompanies[$company] = $company;
        }
    }
}//if_instance_company

/* Add all companies    */
if (!isset($SESSION->addAll)) {
    $SESSION->addAll = false;
}//id_addAll

/* Remove Companies */
if (!isset($SESSION->removeAll)) {
    $SESSION->removeAll = false;
}//if_removeAll

/* Company List */
$company_lst            = WS_DOSKOM::getCompanyList();

/* FORM */
$m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context));
if ($m_form->is_cancelled()) {
    unset($SESSION->addAll);
    unset($SESSION->removeAll);
    unset($SESSION->selCompanies);
    unset($SESSION->Companies);

    redirect($return);
}else if ($data = $m_form->get_data()) {
    $SESSION->addAll    = false;
    $SESSION->removeAll = false;

    /* Add All companies        */
    if (isset($data->add_all) && ($data->add_all)) {
        $SESSION->addAll        = true;
        $SESSION->selCompanies = array();
        $SESSION->Companies = array();

        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context));
    }//add_all_companies

    /* Remove all companies     */
    if (isset($data->remove_all) && ($data->remove_all)) {
        $SESSION->removeAll     = true;
        $SESSION->selCompanies = array();
        $SESSION->Companies = array();

        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context));
    }//remove_all_companies

    /* Add selected companies   */
    if (isset($data->add_sel) && ($data->add_sel)) {
        foreach($data->acompanies as $key=>$value) {
            $SESSION->selCompanies[$value] = $value;
        }
        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context));
    }//if_add_companies

    /* Remove selected companies    */
    if (isset($data->remove_sel) && ($data->remove_sel)) {
        foreach($data->scompanies as $key=>$value) {
            unset($SESSION->selCompanies[$value]);
            $SESSION->Companies[$value] = $value;
        }
        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context));
    }//if_remove_companies

    /* Create Instance  */
    if ((isset($data->submitbutton) && $data->submitbutton)) {
        if ($instance->id) {
            /* Update Instance */
            $reset = ($instance->status != $data->status);

            $instance->status         = $data->status;
            $instance->name           = $data->name;
            $instance->company        = implode(',',$SESSION->selCompanies);
            $instance->customint3     = $data->participants;
            $instance->roleid         = $data->roleid;
            $instance->enrolperiod    = $data->enrolperiod;
            $instance->enrolstartdate = $data->enrolstartdate;
            $instance->enrolenddate   = $data->enrolenddate;
            $instance->timemodified   = time();

            $DB->update_record('enrol', $instance);

            if ($reset) {
                $context->mark_dirty();
            }
        }else {
            /* Insert Instance */
            $fields = array('status'        =>  $data->status,
                            'name'          =>  $data->name,
                            'company'       =>  implode(',',$SESSION->selCompanies),
                            'customint3'    =>  $data->participants,
                            'roleid'        =>  $data->roleid,
                            'enrolperiod'   =>  $data->enrolperiod,
                            'enrolstartdate'=>  $data->enrolstartdate,
                            'enrolenddate'  =>  $data->enrolenddate);

            $plugin->add_instance($course, $fields);
        }//if_else_instance

        unset($SESSION->addAll);
        unset($SESSION->removeAll);
        unset($SESSION->selCompanies);
        unset($SESSION->Companies);

        redirect($return);
    }//if_button_submission_next

}//if_else_form

/* Displayed Form */
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_wsdoskom'));

echo $OUTPUT->header();

$m_form->display();

echo $OUTPUT->footer();