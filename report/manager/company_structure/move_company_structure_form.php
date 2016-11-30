<?php
/**
 * Report Competence Manager - Move Company structure.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/company_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/04/2016
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class move_company_structure_form extends moodleform {
    function definition() {
        global $SESSION;
        $parents        = $SESSION->parents;
        $companyName    = null;
        $moveFrom       = array();
        list($company,$level) = $this->_customdata;

        $mForm = $this->_form;

        /* Move From    */
        $mForm->addElement('header', 'header_from' , get_string('move_from','report_manager'));
        $mForm->setExpanded('header_from',true);
        /* Company  */
        $companyName = company_structure::get_company_name($company);
        $mForm->addElement('text', 'name', get_string('select_company_structure_level','report_manager',$level), 'size = 50 readonly');
        $mForm->setDefault('name',$companyName);
        $mForm->setType('name',PARAM_TEXT);

        for ($i = ($level-1); $i >= 0; $i--) {
            $moveFrom[$i] = company_structure::get_company_info($parents[$i]);
            $mForm->addElement('text','from_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $mForm->setDefault('from_' . $i,$moveFrom[$i]->name);
            $mForm->setType('from_' . $i,PARAM_TEXT);
        }//for

        /* Move To      */
        $mForm->addElement('header', 'header_to' , get_string('move_to','report_manager'));
        $mForm->setExpanded('header_to',true);
        for ($i = 0; $i < $level-1; $i++) {
            $parentInfo = company_structure::get_company_info($parents[$i]);
            $mForm->addElement('text','to_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $mForm->setDefault('to_' . $i,$parentInfo->name);
            $mForm->setType('to_' . $i,PARAM_TEXT);
        }//for

        /* Get companies where it's possible to move */
        switch ($level) {
            case 1:
                $moveTo = CompetenceManager::GetCompanies_LevelList($level-1);
                break;
            default:
                $moveTo = CompetenceManager::GetCompanies_LevelList($level-1,$parents[$level-2]);
                break;
        }

        unset($moveTo[$parents[$level-1]]);
        $mForm->addElement('select','move_to',get_string('comp_parent','report_manager', ($level-1)),$moveTo);
        $mForm->addRule('move_to', get_string('required','report_manager'), 'required', null, 'client');
        $mForm->addRule('move_to', get_string('required','report_manager'), 'nonzero', null, 'client');

        /* Company */
        $mForm->addElement('hidden','id');
        $mForm->setDefault('id',$company);
        $mForm->setType('id',PARAM_INT);

        /* Level */
        $mForm->addElement('hidden','le');
        $mForm->setDefault('le',$level);
        $mForm->setType('le',PARAM_INT);

        $this->add_action_buttons(true);
    }//definition
}//move_company_structure_form