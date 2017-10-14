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
 * Fellesdata Integration - Mapping Forms
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    04/02/2016
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class map_org_form extends moodleform {
    function definition() {
        global $SESSION;
        /* Variables    */
        $form       = null;
        $form               = $this->_form;
        $level = $this->_customdata;

        /* Options Mapping */
        $form->addElement('header','header_map',get_string('map_opt','local_fellesdata'));
        /* Level to Map */
        /* Change Selector */
        $options = FS_MAPPING::getLevelsMapping();
        $form->addElement('select','level',get_string('level_map','local_fellesdata'),$options);
        $form->addRule('level','required','required', null, 'client');
        $form->setDefault('level',$level);

        /* Parents */
        list($options,$fsparents) = FS_MAPPING::get_parents_synchronized($level);
        $SESSION->fsparents       = $fsparents;
        $form->addElement('select','ksparent',get_string('parent','local_fellesdata'),$options);
        $form->setDefault('ksparent',0);

        /* Pattern  */
        $form->addElement('text','pattern',get_string('pattern','local_fellesdata'));
        $form->addHelpButton('pattern','pattern','local_fellesdata');
        $form->setType('pattern',PARAM_TEXT);

        /* Add Action Buttons   */
        $this->add_action_buttons(true,get_string('continue'));

        // Hide level selected
        $form->addElement('text','hlevel',null,'style=visibility:hidden;height:0px;');
        $form->setType('hlevel',PARAM_INT);
        $form->setDefault('hlevel',0);

        // Hide parent selected
        $form->addElement('text','hparent',null,'style=visibility:hidden;height:0px;');
        $form->setType('hparent',PARAM_INT);
        $form->setDefault('hparent',0);

        // Hide fsparents
        $form->addElement('text','hfsparents',null,'style=visibility:hidden;height:0px;');
        $form->setType('hfsparents',PARAM_RAW);
        $form->setDefault('hfsparents',0);
    }//definition
}//map_org_form

class selector_form extends moodleform {
    function definition() {
        /* Variables    */
        $type       = null;
        $source     = null;
        $form       = null;

        $form               = $this->_form;
        list($type,$source) = $this->_customdata;

        /* Header   */
        /* Type of mapping  */
        $form->addElement('header','header_mapping',get_string('type_map','local_fellesdata'));
        /* Company Mapping      */
        $form->addElement('radio','mapping_co','',get_string('opt_org','local_fellesdata'),MAPPING_CO);
        /* Job Roles Mapping    */
        $form->addElement('radio','mapping_jr','',get_string('opt_jr','local_fellesdata'),MAPPING_JR);
        /* Job Roles Options    */
        $form->addElement('checkbox','jr_no_generic','',get_string('opt_no_generics','local_fellesdata'),'disabled');
        $form->addElement('checkbox','jr_generic','',get_string('opt_generics','local_fellesdata'),'disabled');

        /* Options Mapping */
        $form->addElement('header','header_map',get_string('map_opt','local_fellesdata'));
        /* Level to Map */
        /* Change Selector */
        $form->addElement('text','level',get_string('level_map','local_fellesdata'),'size="10"');
        $form->setType('level',PARAM_TEXT);
        $form->addRule('level','required','required', null, 'client');
        /* Pattern  */
        $form->addElement('text','pattern',get_string('pattern','local_fellesdata'));
        $form->addHelpButton('pattern','pattern','local_fellesdata');
        $form->setType('pattern',PARAM_TEXT);

        $form->addElement('text','type','','style="visibility:hidden"');
        $form->setDefault('type',$type);
        $form->setType('type',PARAM_TEXT);

        /* Hidden Elements   */
        /* Source */
        $form->addElement('hidden','src');
        $form->setDefault('src',$source);
        $form->setType('src',PARAM_INT);
        /* Type Mapping */
        $form->addElement('hidden','m');
        $form->setDefault('m',$type);
        $form->setType('m',PARAM_INT);

        /* Add Action Buttons   */
        $this->add_action_buttons(true,get_string('continue'));
    }//definition
}//selector_form

/**
 * Class organization_map_form
 *
 * @creationDate    08/02/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Form to map companies
 */
class organization_map_form extends moodleform {
    function definition() {
        /* Variables    */
        $titleLeft      = get_string('to_match','local_fellesdata');
        $titleRemain    = null;
        $titleRight     = get_string('possible_matches','local_fellesdata');
        $level          = null;
        $pattern        = null;
        $remain         = 0;

        $form = $this->_form;
        list($level,$parent,$pattern,$toMatch,$total) = $this->_customdata;

        // Get how many remains to map
        $tomap = new stdClass();
        $tomap->of      = count($toMatch);
        $tomap->total   = $total;

        $form->addElement('text','level',get_string('level_map','local_fellesdata'),'size=5 readonly');
        $form->setDefault('level',$level);
        $form->setType('level',PARAM_TEXT);
        $form->addElement('text','ksparent',get_string('parentlevel','local_fellesdata'),'readonly');
        if ($parent) {
            $form->setDefault('ksparent',$parent->name);
        }

        $form->setType('ksparent',PARAM_TEXT);

        $form->addElement('html','<div class="matching_process_title">');
            $titleRemain = get_string('remain_match','local_fellesdata',$tomap);
            $titleLeft .= '. ' . $titleRemain;

            // Left
            $form->addElement('html','<div class="area_left title_matching ">');
                $form->addElement('html','<h6>' . $titleLeft . '</h6>');
            $form->addElement('html','</div>');//area_left

            // Right
            $form->addElement('html','<div class="area_right title_matching ">');
                $form->addElement('html','<h6>' . $titleRight . '</h6>');
            $form->addElement('html','</div>');//area_right
        $form->addElement('html','</div>');//matching_process

        // Data to map
        $this->MatchOrganization($toMatch,$form,$level);

        // Level
        $form->addElement('hidden','le');
        $form->setDefault('le',$level);
        $form->setType('le',PARAM_INT);

        // Parent
        $form->addElement('hidden','ks');
        if ($parent) {
            $form->setDefault('ks',$parent->companyid);
        }else {
            $form->setDefault('ks',0);
        }
        $form->setType('ks',PARAM_INT);

        // Add action buttons
        $strcancel = null;
        if ($toMatch) {
            $strcancel = get_string('cancel');
        }else {
            $strcancel = get_string('strback','local_fellesdata');
        }
        $buttons = array();
        $buttons[] = $form->createElement('submit','submitbutton',get_string('btn_match', 'local_fellesdata'));
        $buttons[] = $form->createElement('cancel',null,$strcancel);

       $form->addGroup($buttons, 'buttonar', '', array(' '), false);
       $form->setType('buttonar', PARAM_RAW);
       $form->closeHeaderBefore('buttonar');
    }//definition

    /**
     * @param           $fsToMap
     * @param           $form
     *
     * @throws          Exception
     *
     * @creationDate    08/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * To map companies
     */
    function MatchOrganization($fsToMap,&$form,$level) {
        /* Variables    */
        $refFS      = null;
        $refKS      = null;
        $name       = null;
        $options    = null;

        try {
            // FR Organization
            foreach ($fsToMap as $fsCompany) {
                // Reference
                $refFS = "FS_" . $fsCompany->fscompany;

                /* Display  */
                $form->addElement('html','<div class="matching_process ">');
                /* To Match */
                $form->addElement('html','<div class="area_left ">');
                $form->addElement('html',$fsCompany->fscompany . ' - ' . $fsCompany->name);
                $form->addElement('html','</div>');//area_left

                /* Possible Matches */
                $form->addElement('html','<div class="area_right">');
                if ($level != 1) {
                    /* Option new company */
                    $options   = array();
                    $index  = 'new';
                    $options[$index] = $form->createElement('radio', $refFS,'',get_string('new_comp','local_fellesdata'),$index);
                    $options[$index]->setValue(0);
                    $grp = $form->addElement('group', 'grp', null, $options,null , false);
                }

                /* Not Sure Option  */
                $options    = array();
                $index      = 'no_sure';
                $options[$index] = $form->createElement('radio', $refFS,'',get_string('no_match','local_fellesdata'),$index);
                $options[$index]->setValue($index);
                $grp = $form->addElement('group', 'grp', null, $options,null , false);

                /* Match Options  */
                foreach ($fsCompany->matches as $match) {
                    /* Data to match    */
                    $options = array();
                    $refKS = $fsCompany->fscompany . "#KS#" . $match->kscompany;

                    $options[$refKS] = $form->createElement('radio', $refFS,'',$match->name,$refKS);
                    $options[$refKS]->setValue($refKS);

                    $grp = $form->addElement('group', 'grp', null, $options,null , false);
                }//for_matches
                $form->addElement('html','</div>');//area_right
                $form->addElement('html','</div>');//matching_process

                /* Line */
                $form->addElement('html','<hr class="line_rpt_matching">');
            }//fsCompany
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MatchOrganization
}//organization_map_form

class organization_new_map_form extends moodleform {
    function definition() {
        /* Variables    */
        global $OUTPUT;
        $level      = null;
        $parent     = optional_param('ks_parent',0,PARAM_INT);

        $form = $this->_form;

        /* Get Extra Info   */
        list($level,$addSearch,$removeSearch,$org) = $this->_customdata;

        $form->addElement('static','header_map',null,get_string('header_parent','local_fellesdata'));

        /* Parents */
        $options = FS_MAPPING::GetParents($level);
        $form->addElement('select','ks_parent',get_string('parent','local_fellesdata'),$options);
        $form->setDefault('ks_parent',$parent);

        /* Companies */
        $form->addElement('header','head_companies',get_string('to_connect','local_fellesdata'));

        $form->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Companies with parents */
            $schoices = FS_MAPPING::FindFSCompanies_WithParent($level,$removeSearch,$parent);
            $form->addElement('html','<div class="sel_comp_left">');
                $form->addElement('select','scompanies','',$schoices,'multiple size="15"');
                $form->addElement('text','scompanies_searchtext',get_string('search'),'id="scompanies_searchtext"');
                $form->setType('scompanies_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_comp_left

            $form->addElement('html','<div class="sel_comp_buttons">');
                /* Add Company     */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $form->addElement('submit','add_sel',$add_btn);

                $form->addElement('html','</br>');
                $form->addElement('html','</br>');

                /* Remove Company  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $form->addElement('submit','remove_sel',$remove_btn);
            $form->addElement('html','</div>');//sel_users_buttons

            /* Companies No Parents */
            $achoices = FS_MAPPING::FindFSCompanies_WithoutParent($level,$addSearch,$parent);
            asort($achoices);
            $form->addElement('html','<div class="sel_comp_right">');
                $form->addElement('select','acompanies','',$achoices,'multiple size="15"');
                $form->addElement('text','acompanies_searchtext',get_string('search'),'id="acompanies_searchtext"');
                $form->setType('acompanies_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');
        $form->addElement('html','</div>');//mapping_selectors

        /* Level */
        $form->addElement('hidden','le');
        $form->setDefault('le',$level);
        $form->setType('le',PARAM_INT);

        /* Back main form or not */
        $form->addElement('hidden','o');
        $form->setDefault('o',$org);
        $form->setType('o',PARAM_INT);

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('cancel','btn_back',get_string('back'));

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');
    }//definition
}//organization_new_map_form

class jobrole_map_form extends moodleform {
    function definition() {
        /* Variables    */
        global $OUTPUT;
        $level      = null;
        $jobrole    = optional_param('ks_jobrole',0,PARAM_INT);

        $form = $this->_form;

        /* Get Extra Info   */
        list($addSearch,$removeSearch) = $this->_customdata;

        $form->addElement('static','header_map',null,get_string('header_jobroles','local_fellesdata'));

        /* Job Roles */
        $options = FS_MAPPING::GetKSJobroles();
        $form->addElement('select','ks_jobrole',get_string('opt_jr','local_fellesdata'),$options);
        $form->setDefault('ks_jobrole',$jobrole);

        /* Job roles fellesdata */
        $form->addElement('header','head_fellesdata',get_string('jr_to_connect','local_fellesdata'));


        $form->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Job roles mapped */
            $schoices = FS_MAPPING::FindFSJobroles_Mapped($jobrole,$removeSearch);
            $form->addElement('html','<div class="sel_comp_left">');
                $form->addElement('select','sjobroles','',$schoices,'multiple size="15"');
                    $form->addElement('text','sjobroles_searchtext',get_string('search'),'id="sjobroles_searchtext"');
                $form->setType('sjobroles_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_comp_left

            $form->addElement('html','<div class="sel_comp_buttons">');
                /* Add Job roles     */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $form->addElement('submit','add_sel',$add_btn);

                $form->addElement('html','</br>');
                $form->addElement('html','</br>');

                /* Remove Job Role  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $form->addElement('submit','remove_sel',$remove_btn);
            $form->addElement('html','</div>');//sel_users_buttons

            /* Job roles no mapped */
            $achoices = FS_MAPPING::FindFSJobroles_NO_Mapped($jobrole,$addSearch);
            $form->addElement('html','<div class="sel_comp_right">');
                $form->addElement('select','ajobroles','',$achoices,'multiple size="15"');
                    $form->addElement('text','ajobroles_searchtext',get_string('search'),'id="ajobroles_searchtext"');
                $form->setType('ajobroles_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');
        $form->addElement('html','</div>');//mapping_selectors

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('cancel','btn_back',get_string('back'));

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');
    }//definition
}//jobrole_map_form