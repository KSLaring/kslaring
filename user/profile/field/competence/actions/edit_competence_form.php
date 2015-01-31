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
$PAGE->requires->js('/user/profile/field/competence/js/competence.js');

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
        list($user_id,$generics) = $this->_customdata;

        /* Get My Competence    */
        $my_competence = Competence::Get_CompetenceData($user_id);
        $my_Roles      = Competence::Get_MyJobRoles($user_id);

        /* Description  */
        $form->addElement('html','<h3>'. get_string('edit_competence','profilefield_competence') .'</h3>');
        $form->addElement('static', 'edit-description', '', get_string('edit_competence_desc', 'profilefield_competence'));

        /* Company Structure    */
        $form->addElement('header', 'header_level', get_string('company_structure', 'report_manager'));
        $form->setExpanded('header_level',true);
        if (!$generics) {
            /* Get Hierarchy Level Connected with the user   */
            list($levelZero,$levelOne,$levelTwo,$levelThree) = Competence::GetMyCompanies_By_Level($my_competence->companies);
            $levelZero[0]   = '0';
            $levelOne[0]    = '0';
            $levelTwo[0]    = '0';
            $levelThree[0]  = '0';

            /* Add Hierarchy Level  */
            /* Level Zero   */
            $this->Add_CompanyLevel(0,$form,$levelZero,$levelOne,$levelTwo,$levelThree);
            /* Level One    */
            $this->Add_CompanyLevel(1,$form,$levelZero,$levelOne,$levelTwo,$levelThree);
            /* Level Two    */
            $this->Add_CompanyLevel(2,$form,$levelZero,$levelOne,$levelTwo,$levelThree);
            /* Level Three  */
            $this->Add_CompanyLevel(3,$form,$levelZero,$levelOne,$levelTwo,$levelThree);

            /* Job Roles            */
            $form->addElement('header', 'header_jr', get_string('job_roles', 'report_manager'));
            $form->setExpanded('header_jr',true);
            $this->Add_JobRoleLevel($form,$levelThree,$my_Roles);
        }else {
            /* Add Hierarchy Level   */
            /* Level Zero   */
            $form->addElement('static', 'level_0', get_string('select_company_structure_level','report_manager',0), get_string('level_generic', 'profilefield_competence'));
            /* Level One    */
            $form->addElement('static', 'level_1', get_string('select_company_structure_level','report_manager',1), get_string('level_generic', 'profilefield_competence'));
            /* Level Two    */
            $form->addElement('static', 'level_2', get_string('select_company_structure_level','report_manager',2), get_string('level_generic', 'profilefield_competence'));
            /* Level Three  */
            $form->addElement('static', 'level_3', get_string('select_company_structure_level','report_manager',3), get_string('level_generic', 'profilefield_competence'));

            $this->Add_JobRolesGenerics($form,$my_Roles);
        }//if_generics

        $form->addElement('hidden','id');
        $form->setDefault('id',$user_id);
        $form->setType('id',PARAM_INT);

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
        if ($level == 3) {
            $select->setMultiple(true);
            $select->setSize(10);
            $form->addRule('level_' . $level,'','required', null, 'server');

            $my_ThreeIni = array_intersect_key($options,$levelThree);
            $form->addElement('hidden','my_ini_three');
            $form->setDefault('my_ini_three',implode(',',array_keys($my_ThreeIni)));
            $form->setType('my_ini_three',PARAM_TEXT);
        }//if_level_three

        $this->setLevelDefault($level,$form,$levelThree);
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
                $options = array_intersect_key($options,$levelZero);

                break;
            case 1:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $options = Competence::GetCompanies_Level(1,$_COOKIE['parentLevelZero']);

                }else {
                    $options = Competence::GetCompanies_Level(1,implode(',',$levelZero));
                }//if_levelZero

                $options = array_intersect_key($options,$levelOne);
                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $options = Competence::GetCompanies_Level(2,$_COOKIE['parentLevelOne']);
                }else {
                    $options = Competence::GetCompanies_Level(2,implode(',',$levelOne));
                }//if_levelOne

                $options = array_intersect_key($options,$levelTwo);
                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $options = Competence::GetCompanies_Level(3,$_COOKIE['parentLevelTwo']);
                }else {
                    $options = Competence::GetCompanies_Level(3,implode(',',$levelThree));
                }//if_levelTwo

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $form
     * @param           $levelThree
     * @return          mixed
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,&$form,$levelThree) {

        switch ($level) {
            case 0:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $form->setDefault('level_' . $level,$_COOKIE['parentLevelZero']);
                }else {
                    $form->setDefault('level_' . $level,0);
                }//if_cookie

                break;
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $form->setDefault('level_' . $level,$_COOKIE['parentLevelOne']);
                }else {
                    $form->setDefault('level_' . $level,0);
                }//if_cookie

                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $form->setDefault('level_' . $level,$_COOKIE['parentLevelTwo']);
                }else {
                    $form->setDefault('level_' . $level,0);
                }//if_cookie

                break;
            case 3:
                if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
                    $form->setDefault('level_' . $level,$_COOKIE['parentLevelThree']);
                }else {
                    if ($levelThree) {
                        unset($levelThree[0]);
                        $form->setDefault('level_' . $level,implode(',',$levelThree));
                    }else {
                        $form->setDefault('level_' . $level,-1);
                    }//if_levelThree

                }//if_cookie

                break;
        }//switch

        if ($level) {
            $form->disabledIf('level_'  . $level ,'level_'  . ($level - 1),'eq',0);
        }//if_elvel
    }//setLevelDefault

    /**
     * @param           $form
     * @param           $levelThree
     * @param           $my_Roles
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the job role selector to the form
     */
    function Add_JobRoleLevel(&$form,$levelThree,$my_Roles) {
        /* Variables    */
        $options = array();

        /* Level Three  */
        $options[0] = get_string('select_level_list','report_manager');
        if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
            /* Generics */
            Competence::GetJobRoles_Hierarchy($options,$_COOKIE['parentLevelZero'],$_COOKIE['parentLevelOne'],$_COOKIE['parentLevelTwo'],$_COOKIE['parentLevelThree']);
        }else {
            if (($levelThree) && isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                Competence::GetJobRoles_Hierarchy($options,$_COOKIE['parentLevelZero'],$_COOKIE['parentLevelOne'],$_COOKIE['parentLevelTwo'],implode(',',$levelThree));
            }
        }//if_level_three

        $select= &$form->addElement('select','job_roles',
                                    get_string('select_job_role','report_manager'),
                                    $options);
        $select->setMultiple(true);
        $select->setSize(10);
        $form->addRule('job_roles','','required', null, 'server');
        $form->setDefault('job_roles',$my_Roles);

        $my_RolesIni = array_flip(explode(',',$my_Roles));
        $my_RolesIni = array_intersect_key($options,$my_RolesIni);
        $form->addElement('hidden','my_ini_roles');
        $form->setDefault('my_ini_roles',implode(',',array_keys($my_RolesIni)));
        $form->setType('my_ini_roles',PARAM_TEXT);

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
        $form->setDefault('job_roles',$my_Roles);

        $my_RolesIni = array_flip(explode(',',$my_Roles));
        $my_RolesIni = array_intersect_key($options,$my_RolesIni);
        $form->addElement('hidden','my_ini_roles');
        $form->setDefault('my_ini_roles',implode(',',array_keys($my_RolesIni)));
        $form->setType('my_ini_roles',PARAM_TEXT);
    }//Add_JobRolesGenerics

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* Not Generics */
        if (!$data['ge']) {
            /* Level Three and Job Roles Required   */
            if (!isset($data['level_3'])) {
                $errors['level_3'] = get_string('required');
            }//if_level_three

            /* Job Roles    */
            if (!isset($data['job_roles'])) {
                $errors['job_roles'] = get_string('required');
            }//if_level_three
        }//if_not_generics

        /* Validation   */
        return $errors;
    }//validation
}//competence_edit_competence_form