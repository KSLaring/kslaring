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

/* Levels Company - Form  */
class manager_company_structure_form extends moodleform {
    function definition() {
        $m_form = $this->_form;

        /* My Access    */
        $myAccess = $this->_customdata;

        /* Add Level Company Structure      */
        /* Level Zero   */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,0);
        $this->AddLevel(0,$m_form,REPORT_MANAGER_IMPORT_0,$myCompanies);
        /* Level One    */
        /* Companies connected with super user  */
        $myCompanies = $this->Get_MyLevelAccess($myAccess,1);
        $this->AddLevel(1,$m_form,REPORT_MANAGER_IMPORT_1,$myCompanies);
        /* Level Two    */
        /* Companies connected with super user  */
        $myCompanies = $this->Get_MyLevelAccess($myAccess,2);
        $this->AddLevel(2,$m_form,REPORT_MANAGER_IMPORT_2,$myCompanies);
        /* Level Three  */
        /* Companies connected with super user  */
        $myCompanies = $this->Get_MyLevelAccess($myAccess,3);
        $this->AddLevel(3,$m_form,REPORT_MANAGER_IMPORT_3,$myCompanies);

        /* Level Four - Employees   */
        $parentThree  = optional_param(COMPANY_STRUCTURE_LEVEL . 3, 0, PARAM_INT);
        $options    = array();
        if ($parentThree) {
            $options = company_structure::Get_EmployeeLevel($parentThree);
        }//if
        $m_form->addElement('header', 'employees', get_string('company_structure_employees', 'report_manager'));
        $m_form->setExpanded('employees',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                REPORT_MANAGER_EMPLOYEE_LIST,
                                get_string('company_structure_employees', 'report_manager'),
                                $options,
                                'size = 10 multiple="true"');
            /* Options */
            $m_form->disabledIf(REPORT_MANAGER_EMPLOYEE_LIST,COMPANY_STRUCTURE_LEVEL . '3','eq',0);

            /* Variables    */
            $button = array();
            $button_array_attr = array('class' => 'submit-btn');

            /* Delete Employees   */
            $button[] = $m_form->createElement('button','btn-' . REPORT_MANAGER_DELETE_EMPLOYEES . '3',get_string('remove'),$button_array_attr);
            /* Delete All Employees */
            $button[] = $m_form->createElement('button','btn-' . REPORT_MANAGER_DELETE_ALL_EMPLOYEES . '3',get_string('removeall', 'bulkusers') ,$button_array_attr);
            $m_form->addGroup($button, 'btn' . REPORT_MANAGER_DELETE_EMPLOYEES, '&nbsp;', '&nbsp;', false);
        $m_form->addElement('html', '</div>');

        /* Cancel Button */
        $m_form->addElement('header');
        $m_form->addElement('submit','btn-' . REPORT_MANAGER_COMPANY_CANCEL . '1',get_string('btn_cancel','report_manager'));
    }//definition

    /**
     * @param           $level
     * @param           $form
     * @param           $level_import
     * @param           $myCompanies
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel($level,&$form,$level_import,$myCompanies){
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
            $options = $this->getCompanyList($level,$myCompanies);
            $form->addElement('select',
                              COMPANY_STRUCTURE_LEVEL . $level,
                              get_string('level'.$level,'report_manager'),
                              $options);

            $unlink_btn = $this->setLevelDefault($level,$form);
            if (($level == 0) && $myCompanies) {
                $unlink_btn = ' lnk_disabled';
            }

            /* Add Action Buttons   */
            $this->AddActionButtons($level,$form,$unlink_btn);
        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param               $myAccess
     * @param               $level
     *
     * @return          null|string
     * @throws              Exception
     *
     * @creationDate        23/10/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get companies connected with super user
     */
    function Get_MyLevelAccess($myAccess,$level) {
        /* Variables    */
        $myLevelAccess  = null;
        $parent         = null;

        try {
            if ($myAccess) {
                $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . 0, 0, PARAM_INT);

                switch ($level) {
                    case 0:
                        $myLevelAccess = implode(',',array_keys($myAccess));

                        break;
                    case 1:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelOne;
                        }//if_parent

                        break;
                    case 2:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelTwo;
                        }//if_parent

                        break;
                    case 3:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelThree;
                        }//if_parent

                        break;
                }//switch_level
            }

            return $myLevelAccess;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyLevelAccess

    /**
     * @param           $level
     * @param           $myCompanies
     *
     * @return          array
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List connected to the level and parent
     */
    function getCompanyList($level,$myCompanies) {
        /* Variables    */
        $options        = array();

        /* Get Company List */
        switch ($level) {
            case 0:
                /* Companies for Level Zero */
                $options    = CompetenceManager::GetCompanies_LevelList($level,0,$myCompanies);

                break;
            default:
                /* Parent*/
                $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

                /* Companies for the current level */
                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompanies);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_parent

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
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,&$form) {
        /* Variables    */
        $unlink     = '';
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

        if ($level > 0) {
            $parent  = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

            if ($parent) {
                if (company_structure::Company_CountParents($parent) <= 1) {
                    $unlink = 'lnk_disabled ';
                }
            }else {
                $unlink = ' lnk_disabled ';
            }//if_parent
        }//if_level

        /* Set Default  */
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);

        /* Deactivate levels    */
        if ($level) {
            $form->disabledIf(COMPANY_STRUCTURE_LEVEL . $level ,COMPANY_STRUCTURE_LEVEL . ($level - 1),'eq',0);
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
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to super users
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
        /* Manager Button   */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_MANAGERS_SELECTED . $level,get_string('btn_managers','report_manager'),$button_array_attr);
        /* Reporter Button */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_REPORTERS_SELECTED . $level,get_string('btn_reporters','report_manager'),$button_array_attr);

        /* Add Buttons  */
        $form->addElement('html', '<div class="btn-wrapper">');
            $form->addGroup($button, 'btn_' . $level, '&nbsp;', '&nbsp;', false);
        $form->addElement('html', '</div>');
        $form->addHelpButton('btn_' . $level ,'level_' . $level . '_btn','report_manager');
    }//AddActionButtons
}//manager_company_structure_1_form





