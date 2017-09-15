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
 * Extra Profile Field Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 */

class profile_field_competence extends profile_field_base {

    public function edit_field_add($m_form) {
        /* Variables    */

        $m_form->addElement('static', 'comptence-description', '', get_string('profile_desc', 'profilefield_competence'));

        /* The field will be hidden */
        $m_form->addElement('hidden', $this->inputname, format_string($this->field->name));
        $m_form->setType($this->inputname,PARAM_TEXT);
    }//edit_field_add

    /**
     * @param       mixed       $data
     * @param       stdClass    $datarecord
     * @return      mixed
     *
     * @updateDate  11/11/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * The data from the form returns the key. This should be converted to the respective option string to be saved in database
     * Overwrites base class accessor method
     */
    public function edit_save_data_preprocess($data,$datarecord) {
        return $data;
    }

    /**
     * @param       $m_form
     * @throws      Exception
     *
     * @updateDate  23/12/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Set the default value for this field instance
     * Overwrites the base class method
     */
    public function edit_field_set_default($m_form) {
        /* Variables    */
        $myCompetence       = null;
        $out                = '';
        $url                = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $this->userid));

        try {
            /* INCLUDE  */
            require_once('competencelib.php');

            /* Get My Competence Data   */
            $myCompetence  = Competence::get_competence_data($this->userid);

            if ($myCompetence) {
                $out .= '<div><ul>';
                foreach ($myCompetence as $competence) {
                    $out .= '<li>' . '<strong>' .  $competence->path  . ':</strong>' . '<p>' . implode(', ',$competence->roles) . '</p>' . '</li>';
                }//for_companies
                $out .= '</ul></div>';
            }//if_my_competence

            $m_form->addElement('html', $out);

            $out = html_writer::start_tag('div',array('class' => 'buttons'));
            $out .= '<a href="' . $url . '">' . get_string('lnk_update','profilefield_competence') . '</a>';
            $out .= html_writer::end_tag('div'); //buttons

            /* Link to Update your companies and Job Roles  */
            $m_form->addElement('html', $out);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//edit_field_set_default


    /**
     * @return mixed|string
     *
     * @creationDate    13/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display the correct value
     */
    function display_data() {
        /* Variables    */
        $url                = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $this->userid));

        return '<a href="' . $url . '">' . get_string('lnk_view','profilefield_competence') . '</a>';
    }//display_data

    /**
     * @return      bool
     *
     * @updateDate  18/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Allways return false, so the link to edit it will be always visible
     */
    function is_empty() {
        return false;
    }
}//profile_field_competence