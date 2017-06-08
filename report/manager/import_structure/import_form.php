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
 * Report Competence Manager - Import Company structure.
 *
 * @package         report
 * @subpackage      manager/import_structure
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 *
 * @updateDate      26/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add level Zero
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/manager/js/manager.js');

class manager_import_structure_form extends moodleform {
    function definition() {
        $form = $this->_form;
        $isPublic   = null;
        $default    = 1;
        $attr       = '';

        $level = $this->_customdata;

        $form->addElement('header', 'header_import', get_string('header_import', 'report_manager'));
        $form->addElement('html','</br>');

        /* Level to Import  */
        $options = Import_Companies::GetLevel_To_Import();
        $form->addElement('select', 'import_level',get_string('level_to_import','report_manager'),$options);
        $form->addRule('import_level', get_string('required','report_manager'), 'required', null, 'client');
        if (isset($_COOKIE['parentImportLevel']) && ($_COOKIE['parentImportLevel'])) {
            $form->setDefault('import_level',$_COOKIE['parentImportLevel']);
            $level = $_COOKIE['parentImportLevel'];
        }else {
            $form->setDefault('import_level',0);
        }

        switch ($level) {
            case 1:
                /* Company Parent   - Level Zero    */
                $options = Import_Companies::GetParentList_ToImport($level-1);
                $this->Add_SelectParentImport($level-1,'import_0',$options,$form);
                if (isset($_COOKIE['parentImportZero']) && ($_COOKIE['parentImportZero'])) {
                    $form->setDefault('import_0',$_COOKIE['parentImportZero']);
                }

                break;
            case 2:
                /* Company Parent   - Level Zero    */
                $options = Import_Companies::GetParentList_ToImport($level-2);
                $this->Add_SelectParentImport($level-2,'import_0',$options,$form);
                if (isset($_COOKIE['parentImportZero']) && ($_COOKIE['parentImportZero'])) {
                    $form->setDefault('import_0',$_COOKIE['parentImportZero']);
                    $options = Import_Companies::GetParentList_ToImport($level-1,$_COOKIE['parentImportZero']);
                }else {
                    $options = Import_Companies::GetParentList_ToImport($level-1);
                }
                /* Company Parent - Level One       */
                $this->Add_SelectParentImport($level-1,'import_1',$options,$form);
                if (isset($_COOKIE['parentImportOne']) && ($_COOKIE['parentImportOne'])) {
                    $form->setDefault('import_1',$_COOKIE['parentImportOne']);
                }
                $form->disabledIf('import_1','import_0','eq',0);

                break;
            case 3:
                /* Company Parent   - Level Zero    */
                $options = Import_Companies::GetParentList_ToImport($level-3);
                $this->Add_SelectParentImport($level-3,'import_0',$options,$form);
                if (isset($_COOKIE['parentImportZero']) && ($_COOKIE['parentImportZero'])) {
                    $form->setDefault('import_0',$_COOKIE['parentImportZero']);
                    $options = Import_Companies::GetParentList_ToImport($level-2,$_COOKIE['parentImportZero']);
                }else {
                    $options = Import_Companies::GetParentList_ToImport($level-2);
                }
                $form->disabledIf('import_1','import_0','eq',0);

                /* Company Parent - Level One       */
                $this->Add_SelectParentImport($level-2,'import_1',$options,$form);
                if (isset($_COOKIE['parentImportOne']) && ($_COOKIE['parentImportOne'])) {
                    $form->setDefault('import_1',$_COOKIE['parentImportOne']);
                    $options = Import_Companies::GetParentList_ToImport($level-1,$_COOKIE['parentImportOne']);
                }else {
                    $options = Import_Companies::GetParentList_ToImport($level-1);
                }
                /* Company Parent - Level Two       */
                $this->Add_SelectParentImport($level-1,'import_2',$options,$form);
                if (isset($_COOKIE['parentImportTwo']) && ($_COOKIE['parentImportTwo'])) {
                    $form->setDefault('import_2',$_COOKIE['parentImportTwo']);
                }
                $form->disabledIf('import_2','import_1','eq',0);

                break;
            default:
                break;
        }//switch_level

        /* Public Checkbox  */
        if (isset($_COOKIE['parentImportLevel']) && ($_COOKIE['parentImportLevel']) && ($_COOKIE['parentImportLevel'] != 0)) {
            if (isset($_COOKIE['parentImportZero']) && ($_COOKIE['parentImportZero']) && ($_COOKIE['parentImportZero'] != 0)) {
                if (CompetenceManager::IsPublic($_COOKIE['parentImportZero'])) {
                    $default = 1;
                }else {
                    $default = 0;
                }
            }//if_parentZero
            $attr = 'disabled';
        }//if_level
        $form->addElement('checkbox', 'public','',get_string('public', 'report_manager'),$attr);
        $form->setDefault('public',$default);
        /* Public Parent Hide   */
        if (isset($_COOKIE['parentImportLevel']) && ($_COOKIE['parentImportLevel']) && ($_COOKIE['parentImportLevel'] != 0)) {
            $form->addElement('hidden','public_parent');
            $form->setDefault('public_parent',$default);
            $form->setType('public_parent',PARAM_INT);
        }

        /* Import File */
        $form->addElement('filepicker', 'import_structure', get_string('import_file','report_manager'));
        $form->addRule('import_structure', get_string('required','report_manager'), 'required',null,'client');

        $choices = csv_import_reader::get_delimiter_list();
        $form->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'report_manager'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $form->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $form->setDefault('delimiter_name', 'semicolon');
        } else {
            $form->setDefault('delimiter_name', 'comma');
        }

        $choices = core_text::get_encodings();
        $form->addElement('select', 'encoding', get_string('encoding', 'report_manager'), $choices);
        $form->setDefault('encoding', 'UTF-8');

        $form->addElement('hidden','level');
        $form->setDefault('level',$level);
        $form->setType('level',PARAM_INT);

        $this->add_action_buttons(true,get_string('btn_import','report_manager'));
        $this->set_data($level);
    }//definition

    /**
     * @param           $level
     * @param           $parent
     * @param           $options
     * @param           $form
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the selector for the parent level
     */
    function Add_SelectParentImport($level,$parent,$options,&$form) {
        $form->addElement('select', $parent,get_string('comp_parent','report_manager',$level),$options);
        $form->addRule($parent, get_string('required','report_manager'), 'required', null, 'client');
        $form->addRule($parent, get_string('required','report_manager'), 'nonzero', null, 'client');
    }//Add_CallParentImport

}//manager_import_structure_form