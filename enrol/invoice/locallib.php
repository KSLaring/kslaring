<?php
/**
 * Invoice Enrolment Plugin - Local Library Plugin Implementation
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_invoice_enrol_form extends moodleform {
    protected $instance;
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple invoice enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id.'_'.get_class($this);
        return $formid;
    }//get_form_identifier

    public function definition() {

        $mform                  = $this->_form;
        $instance               = $this->_customdata;
        $this->instance         = $instance;
        $plugin                 = enrol_get_plugin('invoice');

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'invoice_header', $heading);

        Invoices::add_elements_to_form($mform);

        if ($instance->password) {
            // Change the id of invoice enrolment key input as there can be multiple invoice enrolment methods.
            $mform->addElement('passwordunmask', 'enrolpassword', get_string('password', 'enrol_invoice'),array('id' => 'enrolpassword_'.$instance->id));
        } else {
            $mform->addElement('static', 'nokey', '', get_string('no_password', 'enrol_invoice'));
        }

        $this->add_action_buttons(false, get_string('enrol_me', 'enrol_invoice'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }//definitions


    public function validation($data, $files) {
        global $DB, $CFG;
        $msg_error = '';

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }

        if ($instance->password) {
            if ($data['enrolpassword'] !== $instance->password) {
                if ($instance->customint1) {
                    $groups = $DB->get_records('groups', array('courseid'=>$instance->courseid), 'id ASC', 'id, enrolmentkey');
                    $found = false;
                    foreach ($groups as $group) {
                        if (empty($group->enrolmentkey)) {
                            continue;
                        }
                        if ($group->enrolmentkey === $data['enrolpassword']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // We can not hint because there are probably multiple passwords.
                        $errors['enrolpassword'] = get_string('password_invalid', 'enrol_invoice');
                    }

                } else {
                    $plugin = enrol_get_plugin('invoice');
                    if ($plugin->get_config('show_hint')) {
                        $hint = core_text::substr($instance->password, 0, 1);
                        $errors['enrolpassword'] = get_string('password_invalid_hint', 'enrol_invoice', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('password_invalid', 'enrol_invoice');
                    }
                }
            }
        }

        /* Validate Invoice Data    */
        Invoices::validate_invoice_data($data,$errors);

        return $errors;
    }//validation
}//enrol_invoice_enrol_form
