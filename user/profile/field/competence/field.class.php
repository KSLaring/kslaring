<?php
/**
 * Extra Profile Field Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
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