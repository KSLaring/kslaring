<?php
/**
 * Report Competence Manager - Unlink Company structure.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/company_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class unlink_company_structure_form extends moodleform {
    function definition() {
        list($company_id) = $this->_customdata;

        $m_form = $this->_form;

        /* Header */
        $m_form->addElement('header', 'unlink' , get_string('unlink_title','report_manager'));
        /* Company Name */
        $company_name = company_structure::get_company_name($company_id);
        $m_form->addElement('text', 'name', get_string('txt_item','report_manager'), 'class="text-input" size=50 disabled');
        $m_form->setDefault('name',$company_name);
        $m_form->setType('name',PARAM_TEXT);

        /* Parent List  */
        $parent_lst = company_structure::company_get_parent_list($company_id);
        $m_form->addElement('select','parent_sel',get_string('unlink_from','report_manager'),$parent_lst);
        $m_form->addRule('parent_sel', get_string('required','report_manager'), 'required', null, 'client');
        $m_form->addRule('parent_sel', get_string('required','report_manager'), 'nonzero', null, 'client');

        $m_form->addElement('hidden','id');
        $m_form->setDefault('id',$company_id);
        $m_form->setType('id',PARAM_INT);

        $this->add_action_buttons(true);
        $this->set_data($company_id);
    }//definition
}//unlink_company_structure_form