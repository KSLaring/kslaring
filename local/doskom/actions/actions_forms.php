<?php
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
 * WSDOSKOM - Actions forms
 *
 * @package         local
 * @subpackage      doskom/actions
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    0709/2017
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/formslib.php');

class source_form extends moodleform {
    function definition() {
        /* Variables */
        $mform  = $this->_form;
        list($source,$action) = $this->_customdata;

        $mform->addElement('header', 'header_source', get_string('headersource','local_doskom'));

        // Source - End Point
        $mform->addElement('text', 'api', get_string('strsource','local_doskom'),'size=50');
        $mform->setType('api',PARAM_TEXT);
        $mform->addRule('api','required','required', null, 'client');

        // Label
        $mform->addElement('text', 'label', get_string('strlabel','local_doskom'));
        $mform->setType('label',PARAM_TEXT);
        $mform->addRule('label','required','required', null, 'client');

        $this->add_action_buttons(true, get_string('save','admin'));

        // Activon
        $mform->addElement('hidden','a');
        $mform->setDefault('a',$action);
        $mform->setType('a',PARAM_INT);
        // Source id
        $mform->addElement('hidden','id');
        $mform->setDefault('id',($source ? $source->id : 0));
        $mform->setType('id',PARAM_INT);

        // Set values
        if ($source) {
            $this->set_data($source);
        }//if_source

    }//definition

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // API
        if (actionsdk::source_exist($data['id'],'api',$data['api'])) {
            $errors['api'] = get_string('errexits','local_doskom');
        }

        // Label
        if (actionsdk::source_exist($data['id'],'label',$data['label'])) {
            $errors['label'] = get_string('errexits','local_doskom');
        }


        return $errors;
    }//validation
}//source_form

class company_form extends moodleform {
    function definition() {
        /* Variables */
        $mform  = $this->_form;
        list($company,$action) = $this->_customdata;

        $mform->addElement('header', 'header_company', get_string('headercompany','local_doskom'));

        // Company ID
        $mform->addElement('text', 'id', get_string('strcoid','local_doskom'),'size=50');
        $mform->setType('id',PARAM_TEXT);
        $mform->addRule('id','required','required', null, 'client');
        $mform->addRule('id', null, 'numeric', null, 'client');

        // Company Name
        $mform->addElement('text', 'name', get_string('strconame','local_doskom'),'size=50');
        $mform->setType('name',PARAM_TEXT);
        $mform->addRule('name','required','required', null, 'client');

        // User
        $mform->addElement('text', 'user', get_string('srtcouser','local_doskom'),'size=25');
        $mform->setType('user',PARAM_TEXT);
        $mform->addRule('user','required','required', null, 'client');

        // Token
        $mform->addElement('passwordunmask', 'token', get_string('strcotoken','local_doskom'),'size=25');
        $mform->setType('token',PARAM_RAW);
        $mform->addRule('token','required','required', null, 'client');

        // Source
        $options = actionsdk::get_list_sources();
        $mform->addElement('select','source',get_string('strsource','local_doskom'),$options);
        $mform->addRule('source','required','required', null, 'client');

        // Active
        $mform->addElement('checkbox', 'active', get_string('stractive','local_doskom'));
        $mform->setDefault('active',1);

        $this->add_action_buttons(true, get_string('save','admin'));

        // Activon
        $mform->addElement('hidden','a');
        $mform->setDefault('a',$action);
        $mform->setType('a',PARAM_INT);

        $mform->addElement('hidden','dkco');
        $mform->setDefault('dkco',($company ? $company->dkco :0));
        $mform->setType('dkco',PARAM_INT);

        // Set values
        if ($company) {
            $this->set_data($company);
        }//if_source
    }//definition

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // Company id
        if (actionsdk::company_field_exist('id',$data['id'])) {
            $errors['id'] = get_string('errexits','local_doskom');
        }

        // Company name
        if (actionsdk::company_field_exist('name',$data['name'])) {
            $errors['name'] = get_string('errexits','local_doskom');
        }

        // Company user
        if (actionsdk::company_field_exist('user',$data['user'])) {
            $errors['user'] = get_string('errexits','local_doskom');
        }

        // Company token
        if (actionsdk::company_field_exist('token',$data['token'])) {
            $errors['token'] = get_string('errexits','local_doskom');
        }

        return $errors;
    }//validation
}//company_form