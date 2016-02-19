<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Self enrol plugin implementation.
 *
 * @package    enrol_self
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @updateDate      28/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add Invoice information option
 */
namespace enrol_waitinglist\method\self;
 
require_once($CFG->libdir.'/formslib.php');

class enrolmethodself_enrolform extends \moodleform {
    protected $method;


    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
		list( $waitinglist,$method,$listtotal) = $this->_customdata;
        $formid = $method->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        $mform = $this->_form;
        list( $waitinglist,$method,$listtotal,$confirmed,$remainder) = $this->_customdata;
        $this->method = $method;
        $plugin = enrol_get_plugin('waitinglist');


        $heading = $plugin->get_instance_name($waitinglist);
        $mform->addElement('header', 'selfheader', $heading. ' : ' . get_string('self_menutitle','enrol_waitinglist'));

        if ($remainder) {
            $mform->addElement('html','<div class="lbl_warning">');
            $mform->addElement('html','<h5>' . get_string('request_remainder','enrol_waitinglist',$remainder->timesent) . '</h5>');
            $mform->addElement('html','</div>');

            $this->add_action_buttons(true, get_string('continue'));
        }else {
            /**
             * @updateDate  02/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add checking for vacancies and if the user wants to be set on the wait list or no.
             */
            if (!$confirmed) {
                //queuewarning
                if($listtotal>0){
                    $mform->addElement('static','queuewarning',get_string('self_queuewarning_label','enrol_waitinglist'),get_string('self_queuewarning','enrol_waitinglist',$listtotal));
                }

                if ($method->password) {
                    // Change the id of self enrolment key input as there can be multiple self enrolment methods.
                    //NB actually this probably doesnt apply to waitinglist self enrolment, but just to be safe
                    $mform->addElement('passwordunmask', 'enrolpassword', get_string('password', 'enrol_self'),
                        array('id' => 'enrolpassword_'.$method->id));
                } else {
                    $mform->addElement('static', 'nokey', '', get_string('nopassword', 'enrol_self'));
                }

                /**
                 * @updateDate  28/10/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add Invoice fields
                 */
                if ($waitinglist->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
                    global $PAGE;
                    $PAGE->requires->js('/enrol/invoice/js/invoice.js');
                    \Invoices::AddElements_ToForm($mform);

                    $mform->addElement('hidden', 'invoicedata');
                    $mform->setType('invoicedata', PARAM_INT);
                    $mform->setDefault('invoicedata', 1);
                }//if_invoice

                /**
                 * @updateDate  24/12/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add approval data
                 */
                if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                    global $PAGE;
                    \Approval::AddElements_ToForm($mform);
                }//if_approval

                $mform->addElement('hidden', 'confirm');
                $mform->setType('confirm', PARAM_INT);
                $mform->setDefault('confirm', 1);

                $this->add_action_buttons(true, get_string('enrolme', 'enrol_self'));
            }else {
                $mform->addElement('html','<div class="lbl_warning">');
                $mform->addElement('html','<h5>' . get_string('seats_occupied','enrol_waitinglist') . '</h5>');
                $mform->addElement('html','</div>');

                $this->add_action_buttons(true, get_string('continue'));
            }//if_vacancies


        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $waitinglist->courseid);

        $mform->addElement('hidden', 'methodtype');
        $mform->setType('methodtype', PARAM_TEXT);
        $mform->setDefault('methodtype', $this->method->get_methodtype());

        $mform->addElement('hidden', 'waitinglist');
        $mform->setType('waitinglist', PARAM_INT);
        $mform->setDefault('waitinglist', $waitinglist->id);
    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $method = $this->method;


        if ($method->password) {
            if ($data['enrolpassword'] !== $method->password) {
                if ($method->{enrolmethodself::MFIELD_GROUPKEY}) {
                    $groups = $DB->get_records('groups', array('courseid'=>$method->courseid), 'id ASC', 'id, enrolmentkey');
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
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }

                } else {
                    $plugin = enrol_get_plugin('self');
                    if ($plugin->get_config('showhint')) {
                        $hint = core_text::substr($method->password, 0, 1);
                        $errors['enrolpassword'] = get_string('passwordinvalidhint', 'enrol_self', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }
                }
            }
        }

        /**
         * @updateDate  30/10/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Validate invoice data
         */
        if (isset($data['invoicedata']) && $data['invoicedata']) {
            \Invoices::Validate_InvoiceData($data,$errors);
        }//if_invoicedata

        return $errors;
    }
}
