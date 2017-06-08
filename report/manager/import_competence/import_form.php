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
 * Report Competence Manager - Import Competence Data.
 *
 * @package         report
 * @subpackage      manager/import_competence
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    25/08/2015
 * @author          eFaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class import_competence_form extends moodleform {
    function definition() {
        $form = $this->_form;

        /* Header   */
        $form->addElement('header','header_import',get_string('upload'));
        $form->addElement('html','</br>');

        /* Import File */
        $form->addElement('filepicker', 'import_competence', get_string('import_file','report_manager'));
        $form->addRule('import_competence', get_string('required','report_manager'), 'required',null,'client');

        $choices = csv_import_reader::get_delimiter_list();
        $form->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'report_manager'), $choices);
        $form->setDefault('delimiter_name', 'semicolon');

        $choices = core_text::get_encodings();
        $form->addElement('select', 'encoding', get_string('encoding', 'report_manager'), $choices);
        $form->setDefault('encoding', 'UTF-8');

        $this->add_action_buttons(true,get_string('btn_import','report_manager'));
    }//definition
}//import_competence_form