<?php
/**
 * Extra Profile Field Competence - Add Competence Form
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/user/profile/field/competence/js/competence.js');

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
        $select= &$form->addElement('select',
                                    'level_' . $level,
                                    get_string('select_company_structure_level','report_manager',$level),
                                    $options);
        if ($level == 3) {
            //$select->setMultiple(true);
            //$select->setSize(10);
            $form->addRule('level_' . $level,'','required', null, 'server');
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
     */
    function Add_JobRoleLevel(&$form) {
        /* Variables    */
        $options = array();

        /* Job Roles    */
        $options[0] = get_string('select_level_list','report_manager');

        /* Level Three  */
        if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
            /* Add Generics --> Only Public Job Roles   */
            if (Competence::IsPublic($_COOKIE['parentLevelThree'])) {
                Competence::GetJobRoles_Generics($options);
            }//if_isPublic

            Competence::GetJobRoles_Hierarchy($options,$_COOKIE['parentLevelZero'],$_COOKIE['parentLevelOne'],$_COOKIE['parentLevelTwo'],$_COOKIE['parentLevelThree']);
        }//if_level_three

        $select= &$form->addElement('select','job_roles',
                                    get_string('select_job_role','report_manager'),
                                    $options);
        $select->setMultiple(true);
        $select->setSize(10);
        $form->addRule('job_roles','','required', null, 'server');

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
     */
    function getCompanyList($level,$my_companies) {
        /* Variables    */
        $options = array();

        switch ($level) {
            case 0:
                $options = Competence::GetCompanies_Level($level);

                break;
            case 1:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $options = Competence::GetCompanies_Level(1,$_COOKIE['parentLevelZero']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_levelZero

                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $options = Competence::GetCompanies_Level(2,$_COOKIE['parentLevelOne']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_levelOne

                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $options = Competence::GetCompanies_Level(3,$_COOKIE['parentLevelTwo'],$my_companies);
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
     */
    function setLevelDefault($level,&$form) {

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
                    $form->setDefault('level_' . $level,-1);
                }//if_cookie

                break;
        }//switch

        if ($level) {
            $form->disabledIf('level_'  . $level ,'level_'  . ($level - 1),'eq',0);
        }//if_elvel
    }//setLevelDefault

    function validation($data, $files) {
        list($user_id,$my_companies) = $this->_customdata;

        $errors = parent::validation($data, $files);

        /* Level Three  */
        if (!isset($data['level_3'])) {
            $errors['level_3'] = get_string('required');
        }//if_level_three

        /* Job Roles    */
        if (!isset($data['job_roles'])) {
            $errors['job_roles'] = get_string('required');
        }//if_level_three

        /* Validation   */
        return $errors;
    }//validation
}//competence_add_comptence_form