<?php
/**
 * Report Competence Manager - Company structure - Reporter
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      company_structure/manager
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class report_manager_reporters_form extends moodleform {
    function definition() {
        global $OUTPUT;
        $parentInfo     = null;

        $mForm = $this->_form;

        list($level,$parents,$addSearch,$removeSearch) = $this->_customdata;

        /* Company Info */
        $mForm->addElement('header', 'managers', get_string('jr_connected', 'report_manager'));
        $mForm->setExpanded('managers',true);

        /* Add Parents && Current Level */
        for ($i = 0; $i <= $level; $i++) {
            $parentInfo = company_structure::Get_CompanyInfo($parents[$i]);
            $mForm->addElement('text','parent_' . $i,get_string('select_company_structure_level','report_manager',$i),'size = 50 readonly');
            $mForm->setDefault('parent_' . $i,$parentInfo->name);
            $mForm->setType('parent_' . $i,PARAM_TEXT);
        }//for

        /* Industry Code        */
        $mForm->addElement('text', 'industry_code', get_string('industry_code','report_manager'), 'size = 50 readonly');
        $mForm->setDefault('industry_code',$parentInfo->industrycode);
        $mForm->setType('industry_code',PARAM_TEXT);

        /* Public Check Box     */
        $mForm->addElement('checkbox', 'public','',get_string('public', 'report_manager'),'disabled');
        $mForm->setDefault('public',$parentInfo->public);

        /* USER SELECTORS */
        $mForm->addElement('header', 'users', get_string('users'));
        $mForm->setExpanded('users',true);
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Left.    Existing Users(Reporters)   */
            $schoices   = Reporters::FindReporters_Selector($removeSearch,$parents,$level);

            $mForm->addElement('html','<div class="sel_users_left">');
                $mForm->addElement('selectgroups','removeselect', '',$schoices,'multiple size="20" id="removeselect"');
                    $mForm->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $mForm->setType('removeselect_searchtext',PARAM_TEXT);
            $mForm->addElement('html','</div>');//sel_users_left

            /* Actions Buttons  */
            $mForm->addElement('html','<div class="sel_users_buttons">');
                /* Add Users        */
                $addBtn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $mForm->addElement('submit','add_sel',$addBtn);

                /* Separator    */
                $mForm->addElement('html','</br>');

                /* Remove Users     */
                $removeBtn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $mForm->addElement('submit','remove_sel',$removeBtn);
            $mForm->addElement('html','</div>');//sel_users_buttons

            /* Right.   Potential Users(Managers) */
            $achoices   = Reporters::FindPotentialReporters_Selector($addSearch,$parents,$level);
            $mForm->addElement('html','<div class="sel_users_right">');
                $mForm->addElement('selectgroups','addselect', '',$achoices,'multiple size="20" id="addselect"');
                    $mForm->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $mForm->setType('addselect_searchtext',PARAM_TEXT);
            $mForm->addElement('html','</div>');//sel_users_right
        $mForm->addElement('html','</div>');//userselector_managers

        $mForm->addElement('hidden','le');
        $mForm->setDefault('le',$level);
        $mForm->setType('le',PARAM_INT);

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $mForm->createElement('cancel','btn_back',get_string('back'));

        $mForm->addGroup($buttons, 'buttonar', '', array(' '), false);
        $mForm->setType('buttonar', PARAM_RAW);
        $mForm->closeHeaderBefore('buttonar');

        $this->set_data($level);
    }//definition

    function validation($data, $files) {
        /* Variables    */
        $errors = parent::validation($data, $files);

        /* Check there are users to add */
        if ((isset($data['add_sel']) && $data['add_sel'])) {
            if (!isset($data['addselect']))  {
                $errors['addselect'] = get_string('required');
            }//if_addselect
        }//if_add_sel

        /* Check there are users to remove  */
        if ((isset($data['remove_sel']) && $data['remove_sel'])) {
            if (!isset($data['removeselect'])) {
                $errors['removeselect'] = get_string('required');
            }//if_removeselect
        }//if_remove_sel

        return $errors;
    }//validation
}//report_manager_reporters_form