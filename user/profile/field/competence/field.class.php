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
    //function profile_field_competence($fieldid=0, $userid=0) {
        //first call parent constructor
    //    $this->profile_field_base($fieldid, $userid);

        /// Set the data key
    //    if ($this->data !== NULL) {
    //        $this->datakey = $this->data;
    //    }
    //}

    public function edit_field_add($m_form) {
        /* Variables    */
        global $PAGE;
        $out = ' ';


        $m_form->addElement('static', 'comptence-description', '', get_string('profile_desc', 'profilefield_competence'));
        /* Companies    */
        //$m_form->addElement('textarea','companies',get_string('my_companies','profilefield_competence'),'rows="5" style="width:98%; overflow-y:scroll;" disabled');
        /* Job Role     */
        //$m_form->addElement('textarea','jobroles',get_string('my_job_roles','profilefield_competence'),'rows="5" style="width:98%; overflow-y:scroll;" disabled');


        $m_form->addElement('hidden', $this->inputname, format_string($this->field->name));
        $m_form->setType($this->inputname,PARAM_TEXT);
    }//edit_field_add

    /**
     * @param           stdClass $usernew
     * @return          mixed|void
     *
     * @creationDate    11/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save the company/ies selected
     */
    public function edit_save_data($usernew) {
        /* Variables    */
        global $DB;

        //if (!$usernew->{$this->inputname}) {
            // Field not present in form, probably locked and invisible - skip it.
            //$url = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $this->userid));

            //redirect($url);
        //}else {
            /* Save Data    */
        //}//if_null


    }//edit_save_data

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
        global $USER;
        $out                = '';
        $url                = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $this->userid));

        /* INCLUDE  */
        require_once('competencelib.php');

        /* Get My Competence Data   */
        $my_competence  = Competence::Get_CompetenceData($this->userid);

        if ($my_competence) {
            $out .= '<div><ul>';
                foreach ($my_competence as $competence) {
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

        return '<a href="' . $url . '">' . get_string('lnk_view','profilefield_competence') . '</a>';;
    }//display_data
}//profile_field_competence