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
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_waitinglist\method\unnamedbulk;
 
require_once($CFG->libdir.'/formslib.php');

class enrolmethodunnamedbulk_enrolform extends \moodleform {
    protected $method;
    protected $waitinglist;
    protected $queuestatus;
    protected $toomany = true;

    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
		list( $waitinglist,$method,$queuestatus,$remainder) = $this->_customdata;
        $formid = $method->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        global $CFG,$USER;
        $infoRequest = null;
        $mform       = $this->_form;
        list( $waitinglist,$method,$queuestatus,$confirmed,$reminder) = $this->_customdata;
        $this->method = $method;
        $this->waitinglist = $waitinglist;
        $this->queuestatus=$queuestatus;

        $plugin = enrol_get_plugin('waitinglist');

        $heading = $plugin->get_instance_name($waitinglist);
        $mform->addElement('header', 'selfheader', $heading. ' : ' . get_string('unnamedbulk_menutitle','enrol_waitinglist'));

        $mform->addElement('static','formintro','',get_string('unnamedbulk_enrolformintro','enrol_waitinglist'));

        $buttonarray    = array();
        if ($reminder) {
            $buttonarray[]  = &$mform->createElement('submit', 'submitbutton', get_string('continue'));

            $mform->addElement('html','<div class="lbl_warning">');
            $mform->addElement('html','<h5>' . get_string('request_remainder','enrol_waitinglist',$reminder->timesent) . '</h5>');
            $mform->addElement('html','</div>');
        }else {

            $buttonarray[]  = &$mform->createElement('submit', 'submitbutton', get_string('reserveseats', 'enrol_waitinglist'));
            if (!$confirmed) {
                /**
                 * @updateDate  02/12/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Check vacancies. Not vacancies --> Warning Message
                 */

                if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                    $infoRequest = \Approval::Get_Request($USER->id,$waitinglist->courseid,$waitinglist->id);
                }
                //add caution for number of seats available, and waiting list size etc
                if($queuestatus->hasentry){
                    if ($queuestatus->assignedseats == $queuestatus->seats) {
                        $mform->addElement('static','aboutqueuestatus',
                            get_string('unnamedbulk_enrolformqueuestatus_label','enrol_waitinglist'),
                            get_string('unnamedbulk_enrolformqueuestatus_all','enrol_waitinglist',$queuestatus));
                    }else {
                        $mform->addElement('static','aboutqueuestatus',
                            get_string('unnamedbulk_enrolformqueuestatus_label','enrol_waitinglist'),
                            get_string('unnamedbulk_enrolformqueuestatus','enrol_waitinglist',$queuestatus));
                    }

                }else if ($infoRequest) {
                    $infoRequest->assignedseats = 0;
                    $infoRequest->waitingseats  = $infoRequest->seats;
                    $infoRequest->queueposition = 1;
                    $mform->addElement('static','aboutqueuestatus',
                        get_string('unnamedbulk_enrolformqueuestatus_label','enrol_waitinglist'),
                        get_string('unnamedbulk_enrolformqueuestatus','enrol_waitinglist',$infoRequest));
                }

                //add form input elements
                $mform->addElement('text','seats',  get_string('reserveseatcount', 'enrol_waitinglist'), array('size' => '8'));
                $mform->addRule('seats', null, 'numeric', null, 'client');
                $mform->setType('seats', PARAM_INT);
                $mform->setDefault('seats','');

                /**
                 * @updateDate  28/10/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add Invoice fields
                 */
                if ((!$queuestatus->hasentry) && ($waitinglist->{ENROL_WAITINGLIST_FIELD_INVOICE})) {
                    global $PAGE;
                    $PAGE->requires->js('/enrol/invoice/js/invoice.js');

                    \Invoices::AddElements_ToForm($mform);
                    $mform->addElement('hidden', 'invoicedata');
                    $mform->setType('invoicedata', PARAM_INT);
                    $mform->setDefault('invoicedata', 1);
                }//if_invoice

                /**
                 * @updateDate  29/12/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add approval data
                 */
                if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                    \Approval::AddElements_ToForm($mform);

                    if ($infoRequest) {
                        $mform->setDefault('seats',$infoRequest->seats);
                        $mform->setDefault('arguments',$infoRequest->arguments);
                    }
                }//if_approval

                //add submit + enter course
                if($queuestatus->assignedseats>0){
                    $url = $CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid;
                    $buttonarray[] = &$mform->createElement('button', 'entercoursebutton', get_string('entercoursenow', 'enrol_waitinglist'),array('class'=>'entercoursenowbutton','onclick'=>'location.href="' . $url .'"'));
                }

                $mform->addElement('hidden', 'confirm');
                $mform->setType('confirm', PARAM_INT);
                $mform->setDefault('confirm', 1);
            }else {
                $mform->addElement('html','<div class="lbl_warning">');
                $mform->addElement('html','<h5>' . get_string('seats_occupied','enrol_waitinglist') . '</h5>');
                $mform->addElement('html','</div>');

            }
        }//if_reminder


        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $waitinglist->courseid);
        $mform->addElement('hidden', 'waitinglist');
        $mform->setType('waitinglist', PARAM_INT);
        $mform->setDefault('waitinglist', $waitinglist->id);
        $mform->addElement('hidden', 'methodtype');
        $mform->setType('methodtype', PARAM_TEXT);
        $mform->setDefault('methodtype', $this->method->get_methodtype());
        $mform->addElement('hidden', 'datarecordid');
        $mform->setType('datarecordid', PARAM_INT);

        //use this in place of button group, if you don't need the go to course button
        //$this->add_action_buttons(false, get_string('reserveseats', 'enrol_waitinglist'));

    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $method = $this->method;
        $queuestatus = $this->queuestatus;
        $waitinglist = $this->waitinglist;


        if ($waitinglist->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
            $availabletouser = ($queuestatus->waitlistsize - $queuestatus->queueposition) +
                ($queuestatus->vacancies + $queuestatus->assignedseats);

            if($availabletouser  < $data['seats']){
                $available = $queuestatus->waitlistsize - $queuestatus->queueposition - $queuestatus->waitingseats;
                $a = new \stdClass;
                $a->available = $available;
                $a->vacancies =  $queuestatus->vacancies;
                $errors['seats'] = get_string('nomoreseats', 'enrol_waitinglist', $a);
                return $errors;
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
