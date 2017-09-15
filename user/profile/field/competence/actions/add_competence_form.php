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
 * Extra Profile Field Competence - Add Competence Form
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      27/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * New js to load the company structure
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class competence_add_competence_form extends moodleform {
    function definition() {
        $form = $this->_form;

        list($user_id,$my_companies) = $this->_customdata;

        /* Description  */
        $form->addElement('html','<h3>'. get_string('add_competence','profilefield_competence') .'</h3>');
        $form->addElement('static', 'add-description', '', get_string('add_competence_desc', 'profilefield_competence'));

        /* Company Structure    */
        $form->addElement('header', 'header_level', get_string('company_structure', 'report_manager'));
        $form->setExpanded('header_level',true);
        /* Level Zero   */
        $this->Add_CompanyLevel(0,$form);
        /* Level One    */
        $this->Add_CompanyLevel(1,$form);
        /* Level Two    */
        $this->Add_CompanyLevel(2,$form);
        /* Level Three  */
        $this->Add_CompanyLevel(3,$form,$my_companies);

        $form->addElement('html','<div id="approval" class="no_visible">'. get_string('alert_approve','profilefield_competence') .'</div>');

        /* Job Roles            */
        $form->addElement('header', 'header_jr', get_string('job_roles', 'report_manager'));
        $form->setExpanded('header_jr',true);
        $this->Add_JobRoleLevel($form);

        /* Another Company From Parent Level    */
        $form->addElement('hidden','id');
        $form->setDefault('id',$user_id);
        $form->setType('id',PARAM_INT);

        $this->add_action_buttons(true, get_string('btn_add', 'profilefield_competence'));
    }//definition

    /**
     * @param           $level
     * @param           $form
     * @param           $my_companies
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the level selector to the form
     */
    function Add_CompanyLevel($level,&$form,$my_companies= null) {
        /* Add Level X      */
        /* Add Company List */
        $options = $this->getCompanyList($level,$my_companies);
        $select  = &$form->addElement('select',
                                      'level_' . $level,
                                      get_string('select_company_structure_level','report_manager',$level),
                                      $options);
        if ($level == 3) {
            $form->addRule('level_' . $level,'','required', null, 'client');
        }//if_level_three

        $this->setLevelDefault($level,$form);
    }//Add_CompanyLevel

    /**
     * @param           $form
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the job role selector to the form
     *
     * @updateDate      17/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Job role no compulsory
     *
     * @updateDate      27/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * New js to load the company structure
     */
    function Add_JobRoleLevel(&$form) {
        /* Variables    */
        $options    = array();
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;

        /* Get Levels     */
        $levelZero      = optional_param('level_' . 0, 0, PARAM_INT);
        $levelOne       = optional_param('level_' . 1, 0, PARAM_INT);
        $levelTwo       = optional_param('level_' . 2, 0, PARAM_INT);
        $levelThree     = optional_param('level_' . 3, 0, PARAM_INT);

        /* Job Roles    */
        $options[0] = get_string('select_level_list','report_manager');
        if ($levelThree) {
            /* Add Generics --> Only Public Job Roles   */
            if (Competence::is_public($levelThree)) {
                Competence::get_jobroles_generics($options);
            }//if_isPublic

            Competence::get_jobroles_hierarchy($options,$levelZero,$levelOne,$levelTwo,$levelThree);
        }//if_level_three

        $select= &$form->addElement('select','job_roles',
                                    get_string('select_job_role','report_manager'),
                                    $options);
        $select->setMultiple(true);
        $select->setSize(10);

        $form->disabledIf('job_roles' ,'level_0','eq',0);
        $form->disabledIf('job_roles' ,'level_1','eq',0);
        $form->disabledIf('job_roles' ,'level_2','eq',0);
        $form->disabledIf('job_roles' ,'level_3','eq',0);
    }//Add_JobRoleLevel

    /**
     * @param           $level
     * @param           $my_companies
     * @return          array
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     *
     * @updateDate      27/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * New js to load the company structure
     */
    function getCompanyList($level,$my_companies) {
        /* Variables    */
        $options = array();

        /* Parent*/
        $parent     = optional_param('level_' . ($level-1), 0, PARAM_INT);

        switch ($level) {
            case 0:
                $options = Competence::get_companies_level($level);

                break;
            case 1:
                if ($parent) {
                    $options = Competence::get_companies_level(1,$parent);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_levelZero

                break;
            case 2:
                if ($parent) {
                    $options = Competence::get_companies_level(2,$parent);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_levelOne

                break;
            case 3:
                if ($parent) {
                    $options = Competence::get_companies_level(3,$parent,$my_companies);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_levelTwo

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $form
     * @return          mixed
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     *
     * @updateDate      27/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * New js to load the company structure
     */
    function setLevelDefault($level,&$form) {
        /* Variables    */
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        $default = optional_param('level_'  . $level, 0, PARAM_INT);
        /* Set Default  */
        if ($level == 3) {
            if (!$default) {
                $default = -1;
            }
        }
        $form->setDefault('level_' . $level,$default);
    }//setLevelDefault

    /**
     * @param       array $data
     * @param       array $files
     * @return      array
     *
     * @updateDate  17/09/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Job roles no compulsory
     */
    function validation($data, $files) {
        list($user_id,$my_companies) = $this->_customdata;

        $errors = parent::validation($data, $files);

        /* Level Three  */
        if (!isset($data['level_3'])) {
            $errors['level_3'] = get_string('required');
        }else if (!$data['level_3']) {
            $errors['level_3'] = get_string('required');
        }//if_level_three

        /* Validation   */
        return $errors;
    }//validation
}//competence_add_comptence_form