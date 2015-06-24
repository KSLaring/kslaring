<?php
/**
 * Friadmin Plugin - New Course Form
 *
 * @package             local
 * @subpackage          friadmin/classes
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        24/06/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

class local_friadmin_newcourse_form extends moodleform {
    function definition() {
        /* Variables    */
        $myCategories       = array();

        /* Form         */
        $form               = $this->_form;

        /* Get My Categories    */
        $myCategories[0]    = get_string('sel_category','local_friadmin');
        $myCategories       = $myCategories + local_friadmin_helper::getMyCategories();

        /* Static Element - Extra info for the user */
        $form->addElement('static','extra_info',null,get_string('info_new_course','local_friadmin'));

        /* Add Categories Selection */
        $form->addElement('select','category',get_string('my_categories','local_friadmin'),$myCategories);
        $form->addHelpButton('category', 'my_categories','local_friadmin');
        $form->addRule('category', 'required', 'required', 'nonzero', 'client');
        $form->addRule('category', 'required', 'nonzero', null, 'client');

        $this->add_action_buttons(true, get_string('continue'));
    }
}//local_friadmin_new_course_form