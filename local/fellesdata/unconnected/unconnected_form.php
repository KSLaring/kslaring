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
 * Fellesdata Integration - Unconnected KS Organizations
 *
 * @package         local/fellesdata
 * @subpackage      unconnected
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/02/2017
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class unconnected_ks_form extends moodleform {
    function definition() {
        /* Variables */
        global $OUTPUT;
        $addSearch      = null;
        $removeSearch   = null;
        $level          = null;
        $schoices       = array();
        $achoices       = array();
        
        list($level,$addSearch,$removeSearch) = $this->_customdata;
        $form   = $this->_form;

        // Level
        $options = KS_UNCONNECT::get_levels();
        $form->addElement('select','level',get_string('sel_level','local_fellesdata'),$options);
        $form->addRule('level','required','required', null, 'client');
        if ($level) {
            $form->setDefault('level',$level);
        }
        // Heder
        $form->addElement('header','head_fellesdata',get_string('unconnected','local_fellesdata'));

        // Selectors
        $form->addElement('html','<div class="userselector" id="addselect_wrapper">');
            // KS Organizations to delete from KS site
            $schoices = KS_UNCONNECT::find_ks_to_unconnect($level,$removeSearch);
            $form->addElement('html','<div class="sel_comp_left">');
                $form->addElement('html','<label class="lbl_connector">' . get_string('to_delete','local_fellesdata') . '</label>');
                $form->addElement('select','sunconnect','',$schoices,'multiple size="15"');
                $form->addElement('text','sunconnect_searchtext',get_string('search'),'id="sunconnect_searchtext"');
                $form->setType('sunconnect_searchtext',PARAM_TEXT);
                if ($removeSearch) {
                    $form->setDefault('sunconnect_searchtext',$removeSearch);
                }
            $form->addElement('html','</div>');//sel_comp_left
        
            // Buttons
            $form->addElement('html','<div class="sel_comp_buttons">');
                /* Add   */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $form->addElement('submit','add_sel',$add_btn);
        
                $form->addElement('html','</br>');
                $form->addElement('html','</br>');
        
                /* Remove  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $form->addElement('submit','remove_sel',$remove_btn);
            $form->addElement('html','</div>');//sel_users_buttons
        
            // KS Organizations unconnected
            $achoices = KS_UNCONNECT::find_ks_unconnected($level,$addSearch);
            $form->addElement('html','<div class="sel_comp_right">');
                $form->addElement('html','<label class="lbl_connector">' . get_string('no_mapped','local_fellesdata') . '</label>');
                $form->addElement('select','aunconnect','',$achoices,'multiple size="15"');
                $form->addElement('text','aunconnect_searchtext',get_string('search'),'id="aunconnect_searchtext"');
                $form->setType('aunconnect_searchtext',PARAM_TEXT);
                if ($addSearch) {
                    $form->setDefault('aunconnect_searchtext',$addSearch);
                }
            $form->addElement('html','</div>');
        $form->addElement('html','</div>');//userselector
        
        // Level
        //$form->addElement('hidden','le');
        //$form->setType('le',PARAM_INT);
        //$form->setDefault('le',$level);
        
        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('cancel','btn_back',get_string('back'));

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');
    }//definition
}//unconnected_ks_form