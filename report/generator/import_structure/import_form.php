<?php

/**
 * Report generator - Import Company structure.
 *
 * @package         report
 * @subpackage      generator/import_structure
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/libdev.js');

class generator_import_structure_form extends moodleform {
    function definition() {
        $form = $this->_form;

        list($level,$disabled) = $this->_customdata;

        $form->addElement('header', 'header_import', get_string('header_import', 'report_generator'));
        $form->addElement('html','</br>');

        /* Level to Import  */
        $options = Import_Companies::GetLevel_To_Import();
        $url = new moodle_url('/report/generator/import_structure/import.php');
        $form->addElement('select', 'level',get_string('level_to_import','report_generator'),$options,'onchange=getLevelImport("level","' . $url .'")');
        $form->addRule('level', null, 'required');
        /* Check the level  */
        if ($level) {
            $form->setDefault('level',$level);
        }//if_level

        switch ($level) {
            case 2:
                /* Company Parent   */
                $options = Import_Companies::GetParentList_ToImport($level);
                $form->addElement('select', 'parent_1',get_string('comp_parent_1','report_generator'),$options);
                $form->addRule('parent_1', null, 'required');
                break;
            case 3:
                /* Company Parent   */
                $options = Import_Companies::GetParentList_ToImport($level-1);
                $form->addElement('select', 'parent_1',get_string('comp_parent_1','report_generator'),$options,'onchange=getParentTwoImport("parent_1")');
                $form->addRule('parent_1', null, 'required');
                if (isset($_COOKIE['parentImportTwo']) && isset($_COOKIE['parentImportTwo']) != 0) {
                    $form->setDefault('parent_1',$_COOKIE['parentImportTwo']);
                    $options = Import_Companies::GetParentList_ToImport($level,$_COOKIE['parentImportTwo']);
                }else {
                    $form->setDefault('parent_1',0);
                    $options = Import_Companies::GetParentList_ToImport(0);
                }//if_cookie

                /* Company Parent   */
                $form->addElement('select', 'parent_2',get_string('comp_parent_2','report_generator'),$options);
                $form->disabledIf('parent_2','parent_1','eq',0);
                $form->addRule('parent_2', null, 'required');
                break;
            default:
                break;
        }//switch_level


        /* Import File */
        $form->addElement('filepicker', 'import_structure', get_string('import_file','report_generator'));
        $form->addRule('import_structure', null, 'required');

        $choices = csv_import_reader::get_delimiter_list();
        $form->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'report_generator'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $form->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $form->setDefault('delimiter_name', 'semicolon');
        } else {
            $form->setDefault('delimiter_name', 'comma');
        }

        $choices = core_text::get_encodings();
        $form->addElement('select', 'encoding', get_string('encoding', 'report_generator'), $choices);
        $form->setDefault('encoding', 'UTF-8');

        $this->add_action_buttons(true,get_string('btn_import','report_generator'));
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $level = $this->_customdata;
        if (!$data['level']) {
            $errors['level'] = get_string('required','report_generator');
            return $errors;
        }//if_data_level
        if ($level > 1) {
            if (isset($data['parent_1'])) {
                if (!$data['parent_1']) {
                    $errors['parent_1'] = get_string('required','report_generator');
                    return $errors;
                }
            }//if_parent

            if (isset($data['parent_2'])) {
                if (!$data['parent_2']) {
                    $errors['parent_2'] = get_string('required','report_generator');
                    return $errors;
                }
            }//if_parent
        }//if_level

        return $errors;
    }//validation
}//generator_import_structure_form