<?php
/**
 * Gender Profile Field - Version
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/gender
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/10/2014
 * @author          eFaktor     (fbv)
 *
 */

class profile_field_gender extends profile_field_base {
    public function edit_field_add($m_form) {

        /* Options  */
        $options = array();
        $options[0] = get_string('opt_select','profilefield_gender');
        $options[1] = get_string('opt_man','profilefield_gender');
        $options[2] = get_string('opt_woman','profilefield_gender');

        $m_form->addElement('select',$this->inputname,format_string($this->field->name),$options);
    }//edit_field_add

    /**
     * @param       mixed       $data
     * @param       stdClass    $datarecord
     * @return      mixed
     *
     *
     * Description
     * The data from the form returns the key. This should be converted to the respective option string to be saved in database
     * Overwrites base class accessor method
     */
    public function edit_save_data_preprocess($data,$datarecord) {
        return $data;
    }

    /**
     * @param           moodleform $mform
     *
     * @throws          Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the default value for this field instance
     * Overwrites the base class method
     */
    public function edit_field_set_default($mform) {
        /* Variables    */
        $gender = null;

        try {
            /* Library  */
            require_once('lib/genderlib.php');

            /* Get Gender   */
            $gender = Gender::GetGender_ByUser($this->userid,$this->fieldid);
            if ($gender) {
                $mform->setDefault($this->inputname,$gender->data);
            }else {
                $mform->setDefault($this->inputname,0);
            }

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//edit_field_set_default


    /**
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display the gender
     */
    function display_data() {
        /* Variables    */
        $options    = null;
        $gender     = null;

        try {
            /* Library  */
            require_once('lib/genderlib.php');

            /* Options  */
            $options = array();
            $options[0] = get_string('opt_select','profilefield_gender');
            $options[1] = get_string('opt_man','profilefield_gender');
            $options[2] = get_string('opt_woman','profilefield_gender');

            /* Get Gender */
            $gender = Gender::GetGender_ByUser($this->userid,$this->fieldid);
            if ($gender) {
               return $options[$gender->data];
            }else {
                return null;
            }

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_data
}//profile_field_gender