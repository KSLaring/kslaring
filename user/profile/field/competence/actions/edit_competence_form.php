<?php
/**
 * Extra Profile Field Competence - Edit Competence Form
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/01/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class competence_edit_competence_form extends moodleform {
    function definition() {
        /* Variables    */
        $levelZero      = null;
        $levelOne       = null;
        $levelTwo       = null;
        $levelThree     = null;
        $my_competence  = null;
        $my_Hierarchy   = null;
        $my_Roles       = null;
        $my_Generics    = null;

        /* Form */
        $form = $this->_form;
        list($user_id,$competence_data,$competence,$generics) = $this->_customdata;

        /* Description  */
        $form->addElement('html','<h3>'. get_string('edit_competence','profilefield_competence') .'</h3>');
        $form->addElement('static', 'edit-description', '', get_string('edit_competence_desc', 'profilefield_competence'));

        /* Get My Competence    */
        if (!$generics) {
            $my_competence = Competence::Get_CompetenceData($user_id,$competence_data,$competence);
            $my_hierarchy  = $my_competence[$competence_data];

            /* Company Structure    */
            $form->addElement('header', 'header_level', get_string('company_structure', 'report_manager'));
            $form->setExpanded('header_level',true);
            /* Add Hierarchy Level  */
            /* Level Zero   */
            $this->Add_CompanyLevel(0,$form,$my_hierarchy->levelZero,$my_hierarchy->levelOne,$my_hierarchy->levelTwo,$my_hierarchy->levelThree);
            /* Level One    */
            $this->Add_CompanyLevel(1,$form,$my_hierarchy->levelZero,$my_hierarchy->levelOne,$my_hierarchy->levelTwo,$my_hierarchy->levelThree);
            /* Level Two    */
            $this->Add_CompanyLevel(2,$form,$my_hierarchy->levelZero,$my_hierarchy->levelOne,$my_hierarchy->levelTwo,$my_hierarchy->levelThree);
            /* Level Three  */
            $this->Add_CompanyLevel(3,$form,$my_hierarchy->levelZero,$my_hierarchy->levelOne,$my_hierarchy->levelTwo,$my_hierarchy->levelThree);

            /* Add Job Roles    */
            /* Job Roles            */
            $form->addElement('header', 'header_jr', get_string('job_roles', 'report_manager'));
            $form->setExpanded('header_jr',true);
            $this->Add_JobRoleLevel($form,$my_hierarchy);
        }else {
            $my_generics    = Competence::GetCompetence_Generics($user_id);

            /* Add Hierarchy Level   */
            /* Level Zero   */
            $form->addElement('static', 'level_0', get_string('select_company_structure_level','report_manager',0), get_string('level_generic', 'profilefield_competence'));
            /* Level One    */
            $form->addElement('static', 'level_1', get_string('select_company_structure_level','report_manager',1), get_string('level_generic', 'profilefield_competence'));
            /* Level Two    */
            $form->addElement('static', 'level_2', get_string('select_company_structure_level','report_manager',2), get_string('level_generic', 'profilefield_competence'));
            /* Level Three  */
            $form->addElement('static', 'level_3', get_string('select_company_structure_level','report_manager',3), get_string('level_generic', 'profilefield_competence'));

            $this->Add_JobRolesGenerics($form,$my_generics->roles);
        }//if_generics

        $form->addElement('hidden','id');
        $form->setDefault('id',$user_id);
        $form->setType('id',PARAM_INT);

        /* Competence Data ID   */
        $form->addElement('hidden','icd');
        $form->setDefault('icd',$competence_data);
        $form->setType('icd',PARAM_INT);

        /* Competence Data  */
        $form->addElement('hidden','ic');
        $form->setDefault('ic',$competence);
        $form->setType('ic',PARAM_INT);

        /* Generics     */
        $form->addElement('hidden','ge');
        $form->setDefault('ge',$generics);
        $form->setType('ge',PARAM_INT);

        $this->add_action_buttons(true, get_string('btn_save', 'profilefield_competence'));
    }

    /**
     * @param           $level
     * @param           $form
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the level selector to the form
     */
    function Add_CompanyLevel($level,&$form,$levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables    */
        $my_ThreeIni = null;

        /* Add Level X      */
        /* Add Company List */
        $options = $this->getCompanyList($level,$levelZero,$levelOne,$levelTwo,$levelThree);
        $select  = &$form->addElement('select',
                                      'level_' . $level,
                                      get_string('select_company_structure_level','report_manager',$level),
                                      $options);
    }//Add_CompanyLevel

    /**
     * @param           $level
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     * @return          array
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     */
    function getCompanyList($level,$levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables    */
        $options = array();

        switch ($level) {
            case 0:
                $options = Competence::GetCompanies_Level($level);
                $options = array_intersect_key($options,array($levelZero => $levelZero));

                break;
            case 1:
                $options = Competence::GetCompanies_Level(1,$levelZero);
                $options = array_intersect_key($options,array($levelOne => $levelOne));

                break;
            case 2:
                $options = Competence::GetCompanies_Level(2,$levelOne);
                $options = array_intersect_key($options,array($levelTwo => $levelTwo));

                break;
            case 3:
                $options = Competence::GetCompanies_Level(3,$levelTwo);
                $options = array_intersect_key($options,array($levelThree => $levelThree));

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $form
     * @param           $my_hierarchy
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the job role selector to the form
     */
    function Add_JobRoleLevel(&$form,$my_hierarchy) {
        /* Variables    */
        $options = array();

        /* Level Three  */
        $options[0] = get_string('select_level_list','report_manager');
        Competence::GetJobRoles_Hierarchy($options,$my_hierarchy->levelZero,$my_hierarchy->levelOne,$my_hierarchy->levelTwo,$my_hierarchy->levelThree);
        $select= &$form->addElement('select','job_roles',
                                    get_string('select_job_role','report_manager'),
                                    $options);
        $select->setMultiple(true);
        $select->setSize(10);
        $form->addRule('job_roles','','required', null, 'server');
        $form->setDefault('job_roles',array_keys($my_hierarchy->roles));

        $form->disabledIf('job_roles' ,'level_0','eq',0);
        $form->disabledIf('job_roles' ,'level_1','eq',0);
        $form->disabledIf('job_roles' ,'level_2','eq',0);
        $form->disabledIf('job_roles' ,'level_3','eq',0);
    }//Add_JobRoleLevel

    /**
     * @param           $form
     * @param           $my_Roles
     *
     * @creationDate    29/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the job role selector for generics
     */
    function Add_JobRolesGenerics(&$form,$my_Roles){
        /* Variables    */
        $options = array();

        /* Level Three  */
        $options[0] = get_string('select_level_list','report_manager');
        Competence::GetJobRoles_Generics($options);

        $select= &$form->addElement('select','job_roles',
                                    get_string('select_job_role','report_manager'),
                                    $options);
        $select->setMultiple(true);
        $select->setSize(10);
        if ($my_Roles) {
            $form->setDefault('job_roles',array_keys($my_Roles));
        }
    }//Add_JobRolesGenerics

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* Job Roles    */
        if (!isset($data['job_roles'])) {
            $errors['job_roles'] = get_string('required');
        }//if_level_three

        /* Validation   */
        return $errors;
    }//validation
}//competence_edit_competence_form