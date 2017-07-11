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
 * Fellesdata Integration - Unmap Form
 *
 * @package         local/fellesdata
 * @subpackage      unmap
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    17/11/2016
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class unmap_org_form extends moodleform {
    function definition() {
        // TODO: Implement definition() method.
        /* Variables    */
        $form       = null;
        $form       = $this->_form;

        /* Options Mapping */
        $form->addElement('header','header_map',get_string('unmap_opt','local_fellesdata'));
        /* Level to Map */
        /* Change Selector */
        $options = FS_MAPPING::getLevelsMapping();
        $form->addElement('select','level',get_string('level_unmap','local_fellesdata'),$options);
        $form->addRule('level','required','required', null, 'client');
        /* Pattern  */
        $form->addElement('text','pattern',get_string('pattern','local_fellesdata'));
        $form->addHelpButton('pattern','pattern','local_fellesdata');
        $form->setType('pattern',PARAM_TEXT);

        /* Add Action Buttons   */
        $this->add_action_buttons(true,get_string('continue'));
    }//definition
}//unmap_org_form

class organizations_unmap_form extends moodleform {
    function definition() {
        // TODO: Implement definition() method.
        /* Variables    */
        $titleLeft      = get_string('mapped_with','local_fellesdata');
        $titleFS        = get_string('fs_company','local_fellesdata');
        $titleUnMap     = get_string('to_unmapp','local_fellesdata');
        $level          = null;
        $pattern        = null;
        $remain         = 0;
        $class          = '';

        $form = $this->_form;
        list($level,$pattern,$fsMapped) = $this->_customdata;

        /*
         * Header
         * Level and sector
         */
        /* Level */
        $form->addElement('text','level',get_string('level_unmap','local_fellesdata'),'size="5" readonly');
        $form->setDefault('level',$level);
        $form->setType('level',PARAM_TEXT);
        /* Sector */
        $form->addElement('text','sector',get_string('pattern','local_fellesdata'),'size="25" readonly');
        $form->setDefault('sector',$pattern);
        $form->setType('sector',PARAM_TEXT);

        $form->addElement('static','static_error');

        $form->addElement('html','<div class="unmap_process_title">');
            /* Title   FS     */
            $form->addElement('html','<div class="area_unmap_fs_left title_matching ">');
                $form->addElement('html','<h6>' . $titleFS . '</h6>');
            $form->addElement('html','</div>');//area_left

            /* Title Mapped */
            $form->addElement('html','<div class="area_unmap_fs_left title_matching ">');
                $form->addElement('html','<h6>' . $titleLeft . '</h6>');
            $form->addElement('html','</div>');//area_left

            /* Title Right  */
            $form->addElement('html','<div class="area_unmap_right title_matching">');
                $form->addElement('html','<h6>' . $titleUnMap . '</h6>');
            $form->addElement('html','</div>');//area_right
        $form->addElement('html','</div>');//matching_process

        /* Add data   */
        $this->OrganizationMapped($fsMapped,$form);
        
        /* Level */
        $form->addElement('hidden','le');
        $form->setDefault('le',$level);
        $form->setType('le',PARAM_INT);

        /* Add Action Buttons   */
        //$this->add_action_buttons(true,get_string('nav_unmap','local_fellesdata'));

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('submit','submitbutton',get_string('nav_unmap','local_fellesdata'));
        $buttons[] = $form->createElement('submit','submitbutton2',get_string('next'));
        $buttons[] = $form->createElement('cancel');

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');

    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        list($level,$pattern,$fsMapped) = $this->_customdata;

        if ((isset($data['submitbutton']) && ($data['submitbutton']))) {
            $toUnMap = array();
            foreach ($fsMapped as $infoMapped) {
                /* Referencia   */
                $ref = "ID_FS_KS_" . $infoMapped->id;

                /**
                 * Companies that have to be unmapped
                 */
                if (isset($data[$ref])) {
                    $toUnMap[$infoMapped->id] = $ref;
                }//if_rdf
            }//for_rdo

            /**
             * Unmap companies
             */
            if (!$toUnMap) {
                $errors['static_error'] = get_string('no_selection','local_fellesdata');
            }
        }

        return $errors;
    }//validation

    function OrganizationMapped($fsMapped,&$form) {
        /* Variables    */
        $ref      = null;
        $ro       = ' ';

        /* Companies Mapped */
        foreach ($fsMapped as $infoMapped) {
            /* Referencia   */
            $ref = "ID_FS_KS_" . $infoMapped->id;
            
            /* Display  */
            $form->addElement('html','<div class="unmap_process ">');
                /* FS Company  */
                $form->addElement('html','<div class="area_unmap_fs_left ' . $ro . '">');
                    $form->addElement('html',$infoMapped->fscompany . ' - ' . $infoMapped->fsname);
                $form->addElement('html','</div>');//area_left

                /* Mapped With  */
                $form->addElement('html','<div class="area_unmap_ks_left ' . $ro . '">');
                    $form->addElement('html',$infoMapped->ksname);
                $form->addElement('html','</div>');//area_left
            
                /* Checkbox */
                $form->addElement('html','<div class="area_unmap_right ' . $ro . '">');
                    $form->addElement('checkbox',$ref);
                $form->addElement('html','</div>');//area_right
            $form->addElement('html','</div>');//matching_process

            /* Line */
            $form->addElement('html','<hr class="line_rpt_unmap">');
            
            if ($ro == ' ') {
                $ro = 'ro';
            }else {
                $ro = ' ';
            }
        }//for_fsMapped
    }//OrganizationMapped
}//jobrole_unmap_form