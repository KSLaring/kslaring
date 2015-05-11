<?php
/**
 * Report Competence Manager - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 * @updateDate  24/01/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add Level Zero
 * Remove Counties/Municipalities
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/manager/js/manager.js');

/* Levels Company - Form  */
class manager_company_structure_form extends moodleform {
    function definition() {
        $m_form = $this->_form;

        /* Add Level Company Structure      */
        /* Level Zero   */
        $this->AddLevel(0,$m_form,REPORT_MANAGER_IMPORT_0);
        /* Level One    */
        $this->AddLevel(1,$m_form,REPORT_MANAGER_IMPORT_1);
        /* Level Two    */
        $this->AddLevel(2,$m_form,REPORT_MANAGER_IMPORT_2);
        /* Level Three  */
        $this->AddLevel(3,$m_form,REPORT_MANAGER_IMPORT_3);

        /* Level Four - Employees   */
        $options = array();
        if (isset($_COOKIE['parentLevelThree'])) {
            $options = company_structure::Get_EmployeeLevel($_COOKIE['parentLevelThree']);
        }//if
        $m_form->addElement('header', 'employees', get_string('company_structure_employees', 'report_manager'));
        $m_form->setExpanded('employees',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                'list-' . REPORT_MANAGER_EMPLOYEE_LIST,
                                get_string('company_structure_employees', 'report_manager'),
                                $options,
                                'size = 10');
            /* Options */
            $m_form->disabledIf('list-' . REPORT_MANAGER_EMPLOYEE_LIST,MANAGER_COMPANY_STRUCTURE_LEVEL . '3','eq',0);
        $m_form->addElement('html', '</div>');

        /* Cancel Button */
        $m_form->addElement('header');
        $m_form->addElement('submit','btn-' . REPORT_MANAGER_COMPANY_CANCEL . '1',get_string('btn_cancel','report_manager'));
    }//definition

    /**
     * @param           $level
     * @param           $form
     * @param           $level_import
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel($level,&$form,$level_import){
        /* Variables    */
        $header_ID      = 'header_level_' . $level;
        $header_Label   = get_string(REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL, 'report_manager', $level);


        /* URL to import level  */
        $url_import = new moodle_url('/report/manager/import_structure/import.php');

        /* Header  - Level */
        $form->addElement('header', $header_ID, $header_Label);
        $form->setExpanded($header_ID,true);

        /* Import Structure Link - Level X  */
        $url_import->params(array('level' => $level_import));
        $link = html_writer::link($url_import,get_string('link_level','report_manager',$level_import),array('style' => 'float: right; padding-left: 5px; font-weight:bold; color: #bc8f8f; position:relative;'));
        $form->addElement('html',$link);

        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            $options = $this->getCompanyList($level);
            $form->addElement('select',
                              MANAGER_COMPANY_STRUCTURE_LEVEL . $level,
                              get_string('level'.$level,'report_manager'),
                              $options);
            $unlink_btn = $this->setLevelDefault($level,$form);

            /* Add Action Buttons   */
            $this->AddActionButtons($level,$form,$unlink_btn);
        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param           $level
     * @return          array
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List connected to the level and parent
     */
    function getCompanyList($level) {
        /* Variables    */
        $options = array();

        switch ($level) {
            case 0:
                $options = CompetenceManager::GetCompanies_LevelList($level);

                break;
            case 1:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelZero']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelOne']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelTwo']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $form
     * @return          string
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,&$form) {
        /* Variables    */
        $unlink = 'class="submit-btn" ';

        switch ($level) {
            case 0:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelZero']);
                }else {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelOne']);
                    if (company_structure::Company_CountParents($_COOKIE['parentLevelOne']) <= 1) {
                        $unlink .= 'disabled ';
                    }
                }else {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,0);
                    $unlink .= 'disabled ';
                }//if_cookie

                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelTwo']);
                    if (company_structure::Company_CountParents($_COOKIE['parentLevelTwo']) <= 1) {
                        $unlink .= 'disabled ';
                    }
                }else {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,0);
                    $unlink .= 'disabled ';
                }//if_cookie

                break;
            case 3:
                if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . '3',$_COOKIE['parentLevelThree']);
                    if (company_structure::Company_CountParents($_COOKIE['parentLevelThree']) <= 1) {
                        $unlink .= 'disabled ';
                    }
                }else {
                    $form->setDefault(MANAGER_COMPANY_STRUCTURE_LEVEL . $level,0);
                    $unlink .= 'disabled ';
                }//if_cookie

                break;
        }//switch

        if ($level) {
            $form->disabledIf(MANAGER_COMPANY_STRUCTURE_LEVEL . $level ,MANAGER_COMPANY_STRUCTURE_LEVEL . ($level - 1),'eq',0);
        }//if_elvel

        return $unlink;
    }//setLevelDefault

    /**
     * @param           $level
     * @param           $form
     * @param           $unlink_btn
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the actions buttons
     *      - Add new level
     *      - Rename level
     *      - Delete level
     *      - Unlink level
     */
    function AddActionButtons($level,&$form,$unlink_btn){
        /* Variables    */
        $button = array();
        $button_array_attr = array('class' => 'submit-btn');

        /* Common Buttons   */
        /* Add Level    */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_ADD_ITEM . $level,get_string('add_item','report_manager'),$button_array_attr);
        /* Rename Level */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_RENAME_SELECTED . $level,get_string('rename_selected','report_manager'),$button_array_attr);
        /* Delete Level */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_DELETE_SELECTED . $level,get_string('delete_selected','report_manager'),$button_array_attr);
        /* Unlink Button --> Level > 0  */
        if ($level) {
            /* Unlink Button    */
            $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_UNLINK_SELECTED . $level,get_string('unlink_selected','report_manager'),$unlink_btn);
        }//if_level

        /* Add Buttons  */
        $form->addElement('html', '<div class="btn-wrapper">');
            $form->addGroup($button, 'btn_' . $level, '&nbsp;', '&nbsp;', false);
        $form->addElement('html', '</div>');
        $form->addHelpButton('btn_' . $level ,'level_' . $level . '_btn','report_manager');

        /* Activate /Deactivate buttons */
        if ($level) {
            $form->disabledIf('btn-' . REPORT_MANAGER_ADD_ITEM . $level,MANAGER_COMPANY_STRUCTURE_LEVEL . ($level-1),'eq',0);
            $form->disabledIf('btn-' . REPORT_MANAGER_RENAME_SELECTED . $level,MANAGER_COMPANY_STRUCTURE_LEVEL . $level,'eq',0);
            $form->disabledIf('btn-' . REPORT_MANAGER_DELETE_SELECTED . $level,MANAGER_COMPANY_STRUCTURE_LEVEL . $level,'eq',0);
            $form->disabledIf('btn-' . REPORT_MANAGER_UNLINK_SELECTED . $level,MANAGER_COMPANY_STRUCTURE_LEVEL . $level,'eq',0);
        }else {
            $form->disabledIf('btn-' . REPORT_MANAGER_RENAME_SELECTED . $level,MANAGER_COMPANY_STRUCTURE_LEVEL . $level,'eq',0);
            $form->disabledIf('btn-' . REPORT_MANAGER_DELETE_SELECTED . $level,MANAGER_COMPANY_STRUCTURE_LEVEL . $level,'eq',0);
        }//if_elvel_0
    }//AddActionButtons
}//manager_company_structure_1_form





