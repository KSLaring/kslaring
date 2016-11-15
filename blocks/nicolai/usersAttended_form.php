<?php
/**
 * Created by PhpStorm.
 * User: efaktor
 * Date: 15/11/16
 * Time: 09:29
 */

require_once("$CFG->libdir/formslib.php");

class usersAttended_form extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $form = $this->_form;

        $form->addElement('text', 'email', get_string('email')); // Add elements to your form
        $form->setType('email', PARAM_NOTAGS);                   //Set type of element
        $form->setDefault('email', 'Enter email here (test)');   //Default value

        /*
        //User variables
        $userID = null;         //person.personID
        $userFirstName = null;  //person.fornavn
        $userLastName = null;   //person.etternavn
        $userPhone = null;      //
        $userEmail = null;      //

        //Course variables
        $courseID = null;       //kurs.kursID
        $courseName = null;
        $courseLocation = null;
        $courseLeader = null;   //kursholder

        //Course instances
        $courseInstansID= null; //kursinstans.kursinstans

        //User information
        $infoUser = get_complete_user_data('id',$userID);

        //Course information
        // $infoCourse = get_complete_course_data('id','$courseID); <- i guess i need to create the function

        //Course instans information
        //$infoCourseInstans = get_complete_courseinstans_Data('id, $courseinstansID);

        //userFirstName / fornavn
        $form->addElement('text','firstname',get_string('firstname'),'maxlength="100" size="30"');
        //$this->addRule('firstname','required,'required', null, 'client);
        // ^ can you explain these fields to me?
        $form->setType('firstname', PARAM_TEXT);
        if ($infoUser->firstname) {
            $form->setDefault('firstname',$infoUser->firstname);
        }

        //userLastName / etternavn
        $form->addElement('text','lastname',get_string('lastname'),'maxlength="100" size="30"');
        //$this->addRule('lastname','required,'required', null, 'client);
        $form->setType('lastname', PARAM_TEXT);
        if ($infoUser->lastname) {
            $form->setDefault('lastname',$infoUser->lastname);
        }

        //userPhone / telefon
        $form->addElement('text','phone',get_string('phone'),'maxlength="20" size="30"');
        //$this->addRule('phone','required,'required', null, 'client);
        $form->setType('phone', PARAM_TEXT); // <--- should I use integer?
        if ($infoUser->phone) {
            $form->setDefault('phone',$infoUser->phone);
        }

        //courseName / kursnavn
        $form->addElement('text','courseName',get_string('courseName'),'maxlength="100" size="30"');
        //$this->addRule('courseName','required,'required', null, 'client);
        $form->setType('courseName', PARAM_TEXT);
        if ($infoUser->courseName) {
            $form->setDefault('courseName',$infoCourse->courseName);
        }

        //courseLocation / kurslokasjon
        $form->addElement('text','courseLocation',get_string('courseLocation'),'maxlength="100" size="30"');
        //$this->addRule('courseLocation','required,'required', null, 'client);
        $form->setType('courseLocation', PARAM_TEXT);
        if ($infoUser->courseLocation) {
            $form>setDefault('courseLocation',$infoCourse->courseLocation);
        }

        //courseLeader / kursholder <- kursholderinstans
        $form->addElement('text','courseName',get_string('courseName'),'maxlength="100" size="30"');
        $form->addRule('courseName','required,'required', null, 'client);
        $form->setType('courseName', PARAM_TEXT);
        if ($infoUser->courseName) {
            $form->setDefault('courseName',$infoCourse->courseName);
        }

        //Course instanses
        //$courses = get_string_manager()->get_list_of_courses();
        //$courses = array('' => get_string('selectacourse') . '...') + $courses
        //$form->addElement('select','courseInstans',get_string('selectacourse'),$courses);
        //if ($infoCourseInstans->courseInstansID) {
        //  $form->setDefault('courseInstans',$infoCourseInstans->courseInstansID);

    } */

    }//definition

    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}