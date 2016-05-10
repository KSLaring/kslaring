<?php
/**
 * Fellesdata Integration - Mapping Forms
 *
 * @package         local/fellesdata
 * @subpackage      mapping
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/02/2016
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

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
        $titleLeft  = get_string('to_match','local_fellesdata');
        $titleRight = get_string('possible_matches','local_fellesdata');
        $level      = null;
        $pattern    = null;

        $form = $this->_form;
        list($level,$pattern,$toMatch) = $this->_customdata;

        $form->addElement('html','<div class="matching_process_title">');
            /* Title        */
            $form->addElement('html','<div class="area_left title_matching">');
                $form->addElement('html','<h6>' . $titleLeft . '</h6>');
            $form->addElement('html','</div>');//area_left
            /* Title Right  */
            $form->addElement('html','<div class="area_right title_matching">');
                $form->addElement('html','<h6>' . $titleRight . '</h6>');
            $form->addElement('html','</div>');//area_right
        $form->addElement('html','</div>');//matching_process

        /* Add data to Map  */
        $this->MatchOrganization($toMatch,$form);

        /* Level */
        $form->addElement('hidden','le');
        $form->setDefault('le',$level);
        $form->setType('le',PARAM_INT);

        /* Add Action Buttons   */
        $this->add_action_buttons(true,get_string('btn_match','local_fellesdata'));
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
    function MatchOrganization($fsToMap,&$form) {
        /* Variables    */
        $refFS      = null;
        $refKS      = null;
        $name       = null;
        $options    = null;

        try {
            /* FR ORganization  */
            foreach ($fsToMap as $fsCompany) {
                /* Reference    */
                $refFS = "FS_" . $fsCompany->fscompany;

                /* Display  */
                $form->addElement('html','<div class="matching_process ">');
                    /* To Match */
                    $form->addElement('html','<div class="area_left ">');
                        $form->addElement('html',$fsCompany->name);
                    $form->addElement('html','</div>');//area_left

                    /* Possible Matches */
                    $form->addElement('html','<div class="area_right">');
                        /* Not Sure Option  */
                        $options   = array();
                        $index  = 0;
                        $options[$index] = $form->createElement('radio', $refFS,'',get_string('no_match','local_fellesdata'),0);
                        $options[$index]->setValue(0);
                        $grp = $form->addElement('group', 'grp', null, $options,null , false);

                        /* Option new company */
                        $options   = array();
                        $index  = 'new';
                        $options[$index] = $form->createElement('radio', $refFS,'',get_string('new_comp','local_fellesdata'),$index);
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

/**
 * Class            jobroles_map_form
 *
 * @creationDate    08/02/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Form to map job roles
 */
class jobroles_map_form extends moodleform {
    function definition() {
        /* Variables    */
        $titleLeft  = get_string('to_match','local_fellesdata');
        $titleRight = get_string('possible_matches','local_fellesdata');
        $level      = null;
        $pattern    = null;

        $form = $this->_form;
        list($level,$pattern,$generic,$toMatch) = $this->_customdata;

        $form->addElement('html','<div class="matching_process_title">');
            /* Title        */
            $form->addElement('html','<div class="area_left title_matching">');
                $form->addElement('html','<h6>' . $titleLeft . '</h6>');
            $form->addElement('html','</div>');//area_left
            /* Title Right  */
            $form->addElement('html','<div class="area_right title_matching">');
                $form->addElement('html','<h6>' . $titleRight . '</h6>');
            $form->addElement('html','</div>');//area_right
        $form->addElement('html','</div>');//matching_process

        /* Add data to Map  */
        $this->MatchJobRoles($toMatch,$form);

        /* Level */
        $form->addElement('hidden','le');
        $form->setDefault('le',$level);
        $form->setType('le',PARAM_INT);

        /* Generic */
        $form->addElement('hidden','g');
        $form->setDefault('g',$generic);
        $form->setType('g',PARAM_INT);

        /* Add Action Buttons   */
        $this->add_action_buttons(true,get_string('btn_match','local_fellesdata'));
    }//definition

    /**
     * @param           $jrToMap
     * @param           $form
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Possible matches Job roles
     */
    function MatchJobRoles($jrToMap, &$form) {
        /* Variables    */
        $refFS      = null;
        $refKS      = null;
        $name       = null;
        $options    = null;
        $title      = null;

        try {
            /* FS job Role  */
            foreach ($jrToMap as $fsJR) {
                /* Reference    */
                $refFS = "FS_" . $fsJR->fsjobrole;

                /* Display  */
                $form->addElement('html','<div class="matching_process ">');
                    /* To Match */
                    $form->addElement('html','<div class="area_left ">');
                        $form->addElement('html',$fsJR->name);
                    $form->addElement('html','</div>');//area_left
                    /* Possible Matches*/
                    $form->addElement('html','<div class="area_right">');
                        /* Not Sure Option  */
                        $options   = array();
                        $index  = 0;
                        $options[$index] = $form->createElement('radio', $refFS,'',get_string('no_match','local_fellesdata'),0);
                        $options[$index]->setValue(0);
                        $grp = $form->addElement('group', 'grp', null, $options,null , false);

                        /* Option new company */
                        $options   = array();
                        $index  = 'new';
                        $options[$index] = $form->createElement('radio', $refFS,'',get_string('new_jr','local_fellesdata'),$index);
                        $options[$index]->setValue($index);
                        $grp = $form->addElement('group', 'grp', null, $options,null , false);

                        /* Match Options    */
                        foreach ($fsJR->matches as $match) {
                            /* Data to match    */
                            $options = array();
                            $refKS = $fsJR->fsjobrole . "#KS#" . $match->jobrole;

                            $title = $match->industry . " - " . $match->name;
                            $options[$refKS] = $form->createElement('radio', $refFS,'',$title,$refKS);
                            $options[$refKS]->setValue($refKS);

                            $grp = $form->addElement('group', 'grp', null, $options,null , false);
                        }//for_matches
                    $form->addElement('html','</div>');//area_right
                $form->addElement('html','</div>');//matching_process

                /* Line */
                $form->addElement('html','<hr class="line_rpt_matching">');
            }//for_to_map
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//MatchJobRoles
}//jobroles_map_form