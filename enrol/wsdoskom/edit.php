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
$courseid       = required_param('courseid', PARAM_INT);
/* Current Instance */
$instanceid     = optional_param('id', 0, PARAM_INT);
$mycompanies    = null;
$course         = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context        = context_course::instance($course->id, MUST_EXIST);

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
$tosave         = null;

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
    $instance->company          = 0;

    $fields = array(
        'status'        =>  0,
        'company'       =>  0,
        'customint3'    =>  0);

    $instance->id = $plugin->add_instance($course,$field);
}



/* Company List         */
$company_lst    = WS_DOSKOM::getCompanyList();
// Companies connected with enrolment
$mycompanies = array_flip(explode(',',$instance->company));

/* FORM */
$m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context,$company_lst,$mycompanies));
if ($m_form->is_cancelled()) {
    redirect($return);
}else if ($data = $m_form->get_data()) {
    /* Add All companies        */
    if (isset($data->add_all) && ($data->add_all)) {
        $tosave = $company_lst;
        unset($tosave[0]);
        $instance->company        = trim(implode(',',array_keys($tosave)));

        // First element
        $first = substr($instance->company,0,1);
        if ($first == ",") {
            $instance->company = substr($instance->company,1);
        }
        $DB->update_record('enrol', $instance);

        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context,$company_lst,$company_lst));
    }//add_all_companies

    /* Remove all companies     */
    if (isset($data->remove_all) && ($data->remove_all)) {
        $instance->company        = 0;
        $DB->update_record('enrol', $instance);

        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context,$company_lst,null));
    }//remove_all_companies

    /* Add selected companies   */
    if (isset($data->add_sel) && ($data->add_sel)) {
        if (isset($data->acompanies) && $data->acompanies) {
            foreach($data->acompanies as $key=>$value) {
                $mycompanies[$value] = $value;
            }
            $tosave = $mycompanies;
            unset($tosave[0]);
            $instance->company        = implode(',',array_keys($tosave));

            // First element
            $first = substr($instance->company,0,1);
            if ($first == ",") {
                $instance->company = substr($instance->company,1);
            }
            $DB->update_record('enrol', $instance);
        }
        
        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context,$company_lst,$mycompanies));
    }//if_add_companies

    /* Remove selected companies    */
    if (isset($data->remove_sel) && ($data->remove_sel)) {
        if (isset($data->scompanies) && $data->scompanies) {
            foreach($data->scompanies as $key=>$value) {
                unset($mycompanies[$value]);
            }
            if ($mycompanies) {
                $tosave = $mycompanies;
                unset($tosave[0]);
                $instance->company        = implode(',',array_keys($tosave));

                // First element
                $first = substr($instance->company,0,1);
                if ($first == ",") {
                    $instance->company = substr($instance->company,1);
                }
            }else {
                $instance->company = 0;
            }

            $DB->update_record('enrol', $instance);
        }

        $m_form = new enrol_wsdoskom_edit_form(NULL, array($instance, $plugin_config, $context,$company_lst,$mycompanies));
    }//if_remove_companies

    /* Create Instance  */
    if ((isset($data->submitbutton) && $data->submitbutton)) {
        if ($instance->id) {
            /* Update Instance */
            $reset = ($instance->status != $data->status);

            $instance->status         = $data->status;
            $instance->name           = $data->name;
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
        }//if_else_instance

        redirect($return);
    }//if_button_submission_next

}//if_else_form

/* Displayed Form */
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_wsdoskom'));

echo $OUTPUT->header();

$m_form->display();

echo $OUTPUT->footer();