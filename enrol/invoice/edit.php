<?php
/**
 * Invoice Enrolment - Edit Form
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 *
 * Description
 *  - Add a new instance of Invoice enrollment to specified course or edits current instance.
 */

require('../../config.php');
require_once('edit_form.php');

$courseid   = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT);

$course     = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context    = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/invoice:config', $context);

$PAGE->set_url('/enrol/invoice/edit.php', array('courseid'=>$course->id, 'id'=>$instanceid));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('invoice')) {
    redirect($return);
}

/** @var enrol_invoice_plugin $plugin */
$plugin = enrol_get_plugin('invoice');

if ($instanceid) {
    $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'invoice', 'id'=>$instanceid), '*', MUST_EXIST);

} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

    $instance = (object)$plugin->get_instance_defaults();
    $instance->id       = null;
    $instance->courseid = $course->id;
    $instance->status   = ENROL_INSTANCE_ENABLED; // Do not use default for automatically created instances here.
}//if_instaceid

// Merge these two settings to one value for the single selection element.
if ($instance->notifyall and $instance->expirynotify) {
    $instance->expirynotify = 2;
}
unset($instance->notifyall);

$mform = new enrol_invoice_edit_form(NULL, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    if ($data->expirynotify == 2) {
        $data->expirynotify = 1;
        $data->notifyall = 1;
    } else {
        $data->notifyall = 0;
    }
    if (!$data->expirynotify) {
        // Keep previous/default value of disabled expirythreshold option.
        $data->expirythreshold = $instance->expirythreshold;
    }
    if (!isset($data->customint6)) {
        // Add previous value of newenrols if disabled.
        $data->customint6 = $instance->customint6;
    }

    if ($instance->id) {
        $reset = ($instance->status != $data->status);

        $instance->status         = $data->status;
        $instance->name           = $data->name;
        $instance->password       = $data->password;
        $instance->customint1     = $data->customint1;
        $instance->customint2     = $data->customint2;
        $instance->customint3     = $data->customint3;
        $instance->customint4     = $data->customint4;
        $instance->customint5     = $data->customint5;
        $instance->customint6     = $data->customint6;
        $instance->customtext1    = $data->customtext1;
        $instance->roleid         = $data->roleid;
        $instance->enrolperiod    = $data->enrolperiod;
        $instance->expirynotify   = $data->expirynotify;
        $instance->notifyall      = $data->notifyall;
        $instance->expirythreshold = $data->expirythreshold;
        $instance->enrolstartdate = $data->enrolstartdate;
        $instance->enrolenddate   = $data->enrolenddate;
        $instance->timemodified   = time();
        $DB->update_record('enrol', $instance);

        if ($reset) {
            $context->mark_dirty();
        }

    } else {
        $fields = array(
            'status'          => $data->status,
            'name'            => $data->name,
            'password'        => $data->password,
            'customint1'      => $data->customint1,
            'customint2'      => $data->customint2,
            'customint3'      => $data->customint3,
            'customint4'      => $data->customint4,
            'customint5'      => $data->customint5,
            'customint6'      => $data->customint6,
            'customtext1'     => $data->customtext1,
            'roleid'          => $data->roleid,
            'enrolperiod'     => $data->enrolperiod,
            'expirynotify'    => $data->expirynotify,
            'notifyall'       => $data->notifyall,
            'expirythreshold' => $data->expirythreshold,
            'enrolstartdate'  => $data->enrolstartdate,
            'enrolenddate'    => $data->enrolenddate);
        $plugin->add_instance($course, $fields);
    }

    /* Update Format Options from Enrol instance    */
    if (file_exists($CFG->dirroot . '/course/format/sandnes/sandnes_format.php')) {
        require_once($CFG->dirroot . '/course/format/sandnes/sandnes_format.php');
        sandnes_format::sandnes_UpdateFormatOptionsFromEnrolInstance($courseid,$data->enrolenddate,$data->customint3);
    }//if_exists

    redirect($return);
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_invoice'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_invoice'));
$mform->display();
echo $OUTPUT->footer();