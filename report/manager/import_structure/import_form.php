<?php

/**
 * Report Competence Manager - Import Company structure.
 *
 * @package         report
 * @subpackage      manager/import_structure
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
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

        $level = $this->_customdata;

        $form->addElement('header', 'header_import', get_string('header_import', 'report_manager'));
        $form->addElement('html','</br>');

        /* Level to Import  */
        $options = Import_Companies::GetLevel_To_Import();
        $url = new moodle_url('/report/manager/import_structure/import.php');
        $form->addElement('select', 'import_level',get_string('level_to_import','report_manager'),$options);
        $form->addRule('import_level', null, 'required');
        if (isset($_COOKIE['parentImportLevel']) && ($_COOKIE['parentImportLevel'])) {
            $form->setDefault('import_level',$_COOKIE['parentImportLevel']);
            $level = $_COOKIE['parentImportLevel'];
        }else {
            $form->setDefault('import_level',$level);
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


        /* Import File */
        $form->addElement('filepicker', 'import_structure', get_string('import_file','report_manager'));
        $form->addRule('import_structure', null, 'required');

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

        $this->add_action_buttons(true,get_string('btn_import','report_manager'));
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
        $form->addRule($parent, null, 'required');
    }//Add_CallParentImport

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $level = $this->_customdata;

        if ($level) {
            if (isset($data['import_0'])) {
                if (!$data['import_0']) {
                    $errors['import_0'] = get_string('required','report_manager');
                    return $errors;
                }
            }//if_parent

            if (isset($data['import_1'])) {
                if (!$data['import_1']) {
                    $errors['import_1'] = get_string('required','report_manager');
                    return $errors;
                }
            }//if_parent

            if (isset($data['import_2'])) {
                if (!$data['import_2']) {
                    $errors['import_2'] = get_string('required','report_manager');
                    return $errors;
                }
            }//if_parent
        }//if_level

        return $errors;
    }//validation
}//manager_import_structure_form