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
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class enrolmethodself_enrolform extends \moodleform {
    protected $method;


    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        list( $waitinglist,$method,$listtotal,$confirmed,$remainder) = $this->_customdata;
        $formid = $method->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        global $USER;
        $mform = $this->_form;
        list($waitinglist,$method,$listtotal,$confirmed,$remainder,$onlist) = $this->_customdata;
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
                 * @updateDate  13/09/2016
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add selector companies
                 *
                 * @updateDate  26/0972016
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add selectors company depends on option from method
                 */
                if (($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} != COMPANY_NO_DEMANDED)
                    ||
                    ($waitinglist->{ENROL_WAITINGLIST_FIELD_INVOICE})) {
                    $selfClass      = new enrolmethodself();
                    $myCompetence   = $selfClass->GetCompetenceData($USER->id);
                    $mform->addElement('header', 'levels_connected', get_string('company_sel', 'enrol_waitinglist'));
                    /* Add Levels   */
                    for ($i = 0; $i <= 3; $i++) {
                        $this->Add_CompanyLevel($i,$this->_form,$myCompetence,$waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL});
                    }//for_levels
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
                    \Invoices::add_elements_to_form($mform);

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
                    \Approval::add_elements_form($mform);
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
            }//if_confirmed
        }

        $mform->addElement('hidden', 'onlist');
        $mform->setType('onlist', PARAM_INT);
        $mform->setDefault('onlist', $onlist);

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

    /**
     * @param       $level
     * @param       $form
     * @param       $myCompetence
     * @param       $notDemanded
     * 
     * @throws      Exception
     * @throws      coding_exception
     *
     * Description.
     * For admin users all companies
     * Normal users --> only companies connected with his/her profile
     */
    function Add_CompanyLevel($level,&$form,$myCompetence,$notDemanded) {
        /* Variables    */
        global $SESSION;
        $options        = array();
        $my             = null;
        $parent         = null;
        $inThree        = null;
        $levelZero      = null;
        $levelOne       = null;
        $levelTwo       = null;
        $manualClass    = null;
        $disabled       = '';

        /* Get Company List */
        switch ($level) {
            case 0:
                /* Companies for Level Zero */
                if ($myCompetence) {
                    $options    = \CompetenceManager::GetCompanies_LevelList($level,0,$myCompetence->levelzero);
                }else {
                    $options    = array();
                    $options[0] = get_string('select_level_list','report_manager');
                }

                break;
            default:
                /* Parent*/
                $parent     = optional_param('level_' . ($level-1), 0, PARAM_INT);
                if (!$parent) {
                    if (isset($_COOKIE['level_' . ($level-1)]) && $_COOKIE['level_' . ($level-1)]) {
                        $parent = $_COOKIE['level_' . ($level-1)];
                    }else if (isset($SESSION->onlyCompany)) {
                        $parent = $SESSION->onlyCompany[$level-1];
                    }
                }

                /* Companies for the current level */
                if ($parent) {
                    if ($myCompetence) {
                        switch ($level) {
                            case 1:
                                $options    = \CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompetence->levelone);

                                break;
                            case 2:
                                $options    = \CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompetence->leveltwo);

                                break;
                            case 3:
                                $options    = \CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompetence->levelthree);

                                break;
                        }
                    }else {
                        $options = \CompetenceManager::GetCompanies_LevelList($level,$parent);
                    }
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_parent

                break;
        }//level

        /* Add Level/ Company List  */
        $form->addElement('select','level_' . $level,get_string('select_company_structure_level','report_manager',$level), $options);

        /* Check Only One Company */
        $this->SetOnlyOneCompany($level,$options);

        /* Set  Default Values     */
        $this->setLevelDefault($form,$level);

        if ($level == '3') {
            if ($notDemanded != COMPANY_NO_DEMANDED) {
                $form->addRule('level_' . $level, get_string('required'), 'required', null, 'client');
                $form->addRule('level_' . $level, get_string('required'), 'nonzero', null, 'client');                
            }
        }
    }//Add_CompanyLevel

    /**
     * @param           $level
     * @param           $companiesLst
     * 
     * @throws          \Exception
     * 
     * @creationDate    15/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Check if there is only one company
     */
    function SetOnlyOneCompany($level,$companiesLst) {
        /* Variables    */
        global $SESSION;
        $aux            = null;
        $onlyCompany    = null;

        try {
            /* Check if there is only one company   */
            $aux = $companiesLst;
            unset($aux[0]);
            if (count($aux) == 1) {
                $onlyCompany = implode(',',array_keys($aux));
            }

            /* Save Company */
            if ($onlyCompany) {
                if (!isset($SESSION->onlyCompany)) {
                    $SESSION->onlyCompany = array();
                }

                /* Set the company */
                $SESSION->onlyCompany[$level] = $onlyCompany;
            }else {
                unset($SESSION->onlyCompany);
            }//if_oneCompany
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//SetOnlyOneCompany

    /**
     * @param       $form
     * @param       $level
     * 
     * @throws      \coding_exception
     * 
     * @creationDate    14/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Set default value
     */
    function setLevelDefault(&$form,$level) {
        /* Variables    */
        global $SESSION;
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        if (isset($SESSION->selection)) {
            $default = $SESSION->selection['level_' . $level];
        }else if (isset($SESSION->onlyCompany)) {
            $default = $SESSION->onlyCompany[$level];
        }else if (isset($_COOKIE['level_' . $level]) && $_COOKIE['level_' . $level]) { 
            $default = $_COOKIE['level_' . $level];
        }else {
            $default = optional_param('level_' . $level, 0, PARAM_INT);
        }

        /* Set Default  */
        $form->setDefault('level_' . $level,$default);
    }//setLevelDefault

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
            \Invoices::validate_invoice_data($data,$errors);
        }//if_invoicedata

        return $errors;
    }
}
