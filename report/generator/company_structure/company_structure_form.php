<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/libdev.js');

/* Levels Company - Form  */
class generator_company_structure_form extends moodleform {
    function definition() {
        /* General Settings */
        $button_array_attr = array(
            'class' => 'submit-btn'
        );

        /* URL to import level  */
        $url_import = new moodle_url('/report/generator/import_structure/import.php');
        $m_form = $this->_form;

        /* Level 1   */
        $m_form->addElement('header', 'level_1', get_string(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL, 'report_generator', '1'));
        /* Import Structure Link - Level 1  */
        $url_import->params(array('level' => REPORT_GENERATOR_IMPORT_1));
        $link = html_writer::link($url_import,get_string('link_level','report_generator',REPORT_GENERATOR_IMPORT_1),array('style' => 'float: right; padding-left: 5px; font-weight:bold; color: #bc8f8f; position:relative;'));
        $m_form->addElement('html',$link);

        $m_form->addElement('html', '<div class="level-wrapper">');
            $options = company_structure::Get_Companies_LevelList(1);
            $m_form->addElement('select',
                                REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1',
                                get_string('level1','report_generator'),
                                $options,
                                'onchange=GetLevelTwo("company_structure_level1")');

            if (isset($_COOKIE['parentLevelOne']) && isset($_COOKIE['parentLevelOne']) != 0) {
                $m_form->setDefault(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1',$_COOKIE['parentLevelOne']);
            }else {
                $m_form->setDefault(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1',0);
            }//if_cookie

            $m_form->addElement('html', '<div class="btn-wrapper">');
                $button = array();
                $button[] = $m_form->createElement('submit',
                                                   'btn-' . REPORT_GENERATOR_ADD_ITEM . '1',
                                                   get_string('add_item','report_generator'),
                                                   $button_array_attr);
                $button[] = $m_form->createElement('submit',
                                                   'btn-' . REPORT_GENERATOR_RENAME_SELECTED . '1',
                                                   get_string('rename_selected','report_generator'),
                                                   $button_array_attr);
                $button[] = $m_form->createElement('submit',
                                                   'btn-' . REPORT_GENERATOR_DELETE_SELECTED . '1',
                                                   get_string('delete_selected','report_generator'),
                                                   $button_array_attr);
                $m_form->addGroup($button, 'btn_1', '&nbsp;', '&nbsp;', false);
            $m_form->addElement('html', '</div>');

            $m_form->addHelpButton('btn_1','level_1_btn','report_generator');
            /* Options Buttons */
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_RENAME_SELECTED . '1',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_DELETE_SELECTED . '1',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1','eq',0);
        $m_form->addElement('html', '</div>');

        /* Level 2 */
        $options = array();
        if (isset($_COOKIE['parentLevelOne'])) {
            $options = company_structure::Get_Companies_LevelList(2,$_COOKIE['parentLevelOne']);
        }else {
            $options[0] = get_string('select_level_list','report_generator');
        }//IF_COOKIE
        $m_form->addElement('header', 'level_2', get_string(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL, 'report_generator', '2'));
        $m_form->setExpanded('level_2',true);
        /* Import Structure Link - Level 2  */
        $url_import->params(array('level' => REPORT_GENERATOR_IMPORT_2));
        $link = html_writer::link($url_import,get_string('link_level','report_generator',REPORT_GENERATOR_IMPORT_2),array('style' => 'float: right; padding-left: 5px; font-weight:bold; color: #bc8f8f; position:relative;'));
        $m_form->addElement('html',$link);

        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2',
                                get_string('level2','report_generator'),
                                $options,
                                'onchange=GetLevelTree("company_structure_level2")');
            $attributes = 'class="submit-btn" ';
            if (isset($_COOKIE['parentLevelTwo'])) {
                $m_form->setDefault(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2',$_COOKIE['parentLevelTwo']);
                if (company_structure::Company_CountParents($_COOKIE['parentLevelTwo']) <= 1) {
                    $attributes .= 'disabled ';
                }
            }else {
                $m_form->setDefault(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2',0);
                $attributes .= 'disabled ';
            }//if_cookie
            $m_form->disabledIf(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1','eq',0);
            $m_form->addElement('html', '<div class="btn-wrapper">');
                        $button = array();
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_ADD_ITEM . '2',
                                                           get_string('add_item','report_generator'),
                                                           $button_array_attr);
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_RENAME_SELECTED . '2',
                                                           get_string('rename_selected','report_generator'),
                                                           $button_array_attr);
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_DELETE_SELECTED . '2',
                                                           get_string('delete_selected','report_generator'),
                                                           $button_array_attr);
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_UNLINK_SELECTED . '2',
                                                           get_string('unlink_selected','report_generator'),
                                                           $attributes);
                        $m_form->addGroup($button, 'btn_3', '&nbsp;', '&nbsp;', false);
            $m_form->addElement('html', '</div>');

            $m_form->addHelpButton('btn_3' ,'level_2_btn','report_generator');
            /* Options Buttons */
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_ADD_ITEM . '2',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_RENAME_SELECTED . '2',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_DELETE_SELECTED . '2',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_UNLINK_SELECTED . '2',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2','eq',0);
        $m_form->addElement('html', '</div>');

        /* Level 3 */
        $options = array();
        if (isset($_COOKIE['parentLevelTwo'])) {
            $options = company_structure::Get_Companies_LevelList(3,$_COOKIE['parentLevelTwo']);
        }else {
            $options[0] = get_string('select_level_list','report_generator');
        }//IF_COOKIE
        $m_form->addElement('header', 'level_3', get_string(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL, 'report_generator', '3'));
        $m_form->setExpanded('level_3',true);
        /* Import Structure Link - Level 3  */
        $url_import->params(array('level' => REPORT_GENERATOR_IMPORT_3));
        $link = html_writer::link($url_import,get_string('link_level','report_generator',REPORT_GENERATOR_IMPORT_3),array('style' => 'float: right; padding-left: 5px; font-weight:bold; color: #bc8f8f; position:relative;'));
        $m_form->addElement('html',$link);

        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3',
                                get_string('level3','report_generator'),
                                $options,
                                'onchange=GetLevelEmployee("company_structure_level3")');
            $attributes = 'class="submit-btn" ';
            if (isset($_COOKIE['parentLevelTree'])) {
                $m_form->setDefault(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3',$_COOKIE['parentLevelTree']);
                if (company_structure::Company_CountParents($_COOKIE['parentLevelTree']) <= 1) {
                    $attributes .= 'disabled ';
                }
            }else {
                $m_form->setDefault(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3',0);
            }//if_cookie
            $m_form->disabledIf(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2','eq',0);
            $m_form->addElement('html', '<div class="btn-wrapper">');
                        $button = array();
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_ADD_ITEM . '3',
                                                           get_string('add_item','report_generator'),
                                                           $button_array_attr);
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_RENAME_SELECTED . '3',
                                                           get_string('rename_selected','report_generator'),
                                                           $button_array_attr);
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_DELETE_SELECTED . '3',
                                                           get_string('delete_selected','report_generator'),
                                                           $button_array_attr);
                        $button[] = $m_form->createElement('submit',
                                                           'btn-' . REPORT_GENERATOR_UNLINK_SELECTED . '3',
                                                           get_string('unlink_selected','report_generator'),
                                                           $attributes);
                        $m_form->addGroup($button, 'btn_5', '&nbsp;', '&nbsp;', false);
            $m_form->addElement('html', '</div>');
            $m_form->addHelpButton('btn_5' ,'level_3_btn','report_generator');

            /* Options Buttons */
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_ADD_ITEM . '3',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_RENAME_SELECTED . '3',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_DELETE_SELECTED . '3',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3','eq',0);
            $m_form->disabledIf('btn-' . REPORT_GENERATOR_UNLINK_SELECTED . '3',REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3','eq',0);
        $m_form->addElement('html', '</div>');

        /* Employees */
        $options = array();
        if (isset($_COOKIE['parentLevelTree'])) {
            $options = company_structure::Get_EmployeeLevel($_COOKIE['parentLevelTree']);
        }//if
        $m_form->addElement('header', 'employees', get_string('company_structure_employees', 'report_generator'));
        $m_form->setExpanded('employees',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                'list-' . REPORT_GENERATOR_EMPLOYEE_LIST,
                                get_string('company_structure_employees', 'report_generator'),
                                $options,
                                'size = 10');
            /* Options */
            $m_form->disabledIf('list-' . REPORT_GENERATOR_EMPLOYEE_LIST,REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3','eq',0);
        $m_form->addElement('html', '</div>');

        /* Cancel Button */
        $m_form->addElement('header');
        $m_form->addElement('submit','btn-' . REPORT_GENERATOR_COMPANY_CANCEL . '1',get_string('btn_cancel','report_generator'));

    }//definition
}//generator_company_structure_1_form





