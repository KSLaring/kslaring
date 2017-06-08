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
 * @creationDate    28/08/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Match Form
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class match_wk_form
 *
 * @creationDate    28/08/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * To match workplaces && job roles
 */
class match_form extends moodleform {
    function definition() {
        /* Variables    */
        $titleLeft  = get_string('to_match','report_manager');
        $titleRight = get_string('po_match','report_manager');

        $form = $this->_form;

        list($nonExisting,$start,$step,$processing) = $this->_customdata;

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

        /* Data */
        switch ($processing) {
            case 'wk':
                $this->matchWorkplaces($nonExisting,$form);
                break;
            case 'jr':
                $this->matchJobRoles($nonExisting,$form);
                break;
        }//for_toMatch

        $form->addElement('hidden','start');
        $form->setDefault('start',$start+$step);
        $form->setType('start',PARAM_INT);

        $this->add_action_buttons(true,get_string('btn_match','report_manager'));
    }//definition

    /**
     * @param           $nonExisting
     * @param           $form
     * @throws          Exception
     *
     * @creationDate    28/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * To match workplaces
     */
    function matchWorkplaces($nonExisting,&$form) {
        /* Variables    */
        $name   = null;
        $objs   = null;
        $index  = null;
        $title  = null;

        try {
            foreach ($nonExisting as $toMatch) {
                /* Reference */
                $name = 'CI_' . $toMatch->id;

                /* Display  */
                $form->addElement('html','<div class="matching_process ">');
                    /* To Match             */
                    $form->addElement('html','<div class="area_left ">');
                        $form->addElement('html',$toMatch->industry . ' - ' . $toMatch->workplace);
                    $form->addElement('html','</div>');//area_left

                    /* Possible Matches Right  */
                    $form->addElement('html','<div class="area_right">');
                        foreach ($toMatch->matches as $match) {
                            /* Date To Send */
                            $objs = array();
                            $index = $toMatch->id . '#MT#' . $match->id . '#SE#' . $match->sectorId;
                            $title = $match->industry . ' - ' . $match->name . ' (' . $match->sector . ')';

                            $objs[$index] = $form->createElement('radio', $name,'',$title,$index);
                            $objs[$index]->setValue($index);

                            $grp = $form->addElement('group', 'grp', null, $objs,null , false);
                        }//for_matched

                        /* Not Sure Option  */
                        $objs   = array();
                        $index  = 0;
                        $title  = get_string('not_sure','report_manager');
                        $objs[$index] = $form->createElement('radio', $name,'',$title,0);
                        $objs[$index]->setValue(0);
                        $grp = $form->addElement('group', 'grp', null, $objs,null , false);

                    $form->addElement('html','</div>');//area_right
                $form->addElement('html','</div>');//matching_process
                $form->addElement('html','<hr class="line_rpt_matching">');
            }//for_nonExisting


        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//matchWorkplaces

    /**
     * @param           $nonExisting
     * @param           $form
     * @throws          Exception
     *
     * @creationDate    28/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * To match job roles
     */
    function matchJobRoles($nonExisting,&$form) {
        /* Variables    */
        $name   = null;
        $objs   = null;
        $index  = null;
        $title  = null;
        $strJR  = null;

        try {
            foreach ($nonExisting as $toMatch) {
                /* Reference    */
                $name = 'CI_' . $toMatch->id;

                /* Display  */
                $form->addElement('html','<div class="matching_process ">');
                    /* To Match             */
                    $form->addElement('html','<div class="area_left ">');
                        $strJR = $toMatch->industry . ' - ' . $toMatch->jobrole . '</br>(' . $toMatch->ref . ')';
                        $form->addElement('html',$strJR);
                    $form->addElement('html','</div>');//area_left

                    /* Possible Matches Right  */
                    $form->addElement('html','<div class="area_right">');
                        foreach ($toMatch->matches as $match) {
                            /* Data */
                            $objs = array();
                            $index = $toMatch->id . '#MT#' . $match->id;
                            $title = $match->industry . ' - ' . $match->name;

                            $objs[$index] = $form->createElement('radio', $name,'',$title,$index);
                            $objs[$index]->setValue($index);

                            $grp = $form->addElement('group', 'grp', null, $objs,null , false);
                        }//for_matches

                        /* Not Sure Option  */
                        $objs   = array();
                        $index  = 0;
                        $title  = get_string('not_sure','report_manager');
                        $objs[$index] = $form->createElement('radio', $name,'',$title,0);
                        $objs[$index]->setValue(0);
                        $grp = $form->addElement('group', 'grp', null, $objs,null , false);
                    $form->addElement('html','</div>');//area_right
                $form->addElement('html','</div>');//matching_process
                $form->addElement('html','<hr class="line_rpt_matching">');
            }//for
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//matchJobRoles
}//match_form
