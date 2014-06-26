<?php

/**
 * Autologin Plugin  - Library
 *
 * @package         local
 * @subpackage      autologin
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    17/12/2013
 * @author          eFaktor     (fbv)
 */

/**
 * @return              stdClass
 * @throws              Exception
 *
 * @creationDate        17/12/2013
 * @author              eFaktor     (fbv)
 *
 * Description
 * Create the user. Autologin User.
 */
function local_autologin_CreateUser() {
    global $DB;

    try {
        $user_new = local_autologin_define_user();
        $user_new->id = $DB->insert_record('user',$user_new);

        return $user_new;
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//local_autologin_CreateUser

/**
 * @return          stdClass
 * @throws          Exception
 *
 * @creationDate    04/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Define the user.
 */
function local_autologin_define_user() {
    global $CFG;

    try {
        $user = new stdClass();
        $user->firstname    = get_string('firstname', 'local_autologin');
        $user->lastname     = get_string('lastname', 'local_autologin');
        $user->username     = local_autologin_generatePassword(32,7);
        $user->email        = $user->username . '@nowhere.no';
        $user->mnethostid   = $CFG->mnet_localhost_id;
        $user->city         = get_string('city', 'local_autologin');
        $user->country      = get_string('countrycode', 'local_autologin');
        $user->generatedpw  = local_autologin_generatePassword(16,15);
        $user->password     = md5($user->generatedpw);
        $user->auth         = 'manual';
        $user->confirmed    = 1;
        $user->lang         = $CFG->lang;
        $user->timecreated  = time();
        $user->timemodified = time();

        return $user;
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//local_autologin_define_user


/**
 * @param       int $length
 * @param       int $strength
 * @return          string
 *
 * @creationDate    04/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Generate the Passowrd for the anonymous user.
 */
function local_autologin_generatePassword($length=9,$strength=0) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';

    if( $strength & 1 ) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if( $strength & 2 ) {
        $vowels .= "AEUY";
    }
    if( $strength & 4 ) {
        $consonants .= '23456789';
    }
    if( $strength & 8 ) {
        $consonants .= '@#$%!+';
    }

    $password = '';
    $alt = time() % 2;
    for( $i = 0; $i < $length; $i++ ) {
        if( $alt == 1 ) {
            $password .= $consonants[( rand() % strlen( $consonants ))];
            $alt = 0;
        }else {
            $password .= $vowels[( rand() % strlen( $vowels ))];
            $alt = 1;
        }//if_else
    }//for

    return $password;
}//local_autologin_generatePassword

/**
 * @param           $user
 * @param           $course_id
 * @return          string
 * @throws          Exception
 *
 * @creationDate    04/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Enrol the user into the course.
 */
function local_autologin_EnrolUserCourse($user,$course_id) {
    global $CFG, $DB;

    try {
        /* Variables */
        $error = '';

        require_once($CFG->libdir . '/enrollib.php');

        //retrieve the manual enrolment plugin
        $enrol = enrol_get_plugin('manual');
        if(empty($enrol)) {
            $exception  = new moodle_exception('manualpluginnotinstalled','enrol_manual');
            $error      = $exception->getMessage();
        }else {
            /* check manual enrolment plugin instance is enabled/exist */
            $enrol_manual = $DB->get_record('enrol',array('enrol' => 'manual','courseid' => $course_id),'*');
            if ($enrol_manual) {
                if ($enrol_manual->status) {
                    $errorparams = new stdClass();
                    $errorparams->roleid    = $enrol_manual->roleid;
                    $errorparams->courseid  = $enrol_manual->courseid;
                    $errorparams->userid    = $user->id;

                    $exception  = new moodle_exception( 'wscannotenrol', 'enrol_manual', '', $errorparams);
                    $error      = $exception->getMessage();
                }//if_status
                $enrol->enrol_user($enrol_manual,$user->id,5,time(),0,ENROL_USER_ACTIVE);
            }else {
                $errorparams = new stdClass();
                $errorparams->courseid = $course_id;

                $exception  =  new moodle_exception('wsnoinstance','enrol_manual',$errorparams);
                $error      = $exception->getMessage();
            }//if_else_enrol_manual
        }//if_else_empty_enrol

        return $error;
    }catch (Exception $ex){
        throw $ex;
    }//try_ccatch
}//local_autologin_EnrolUserCourse

/**
 * @param           $user
 * @param           $category
 * @param           $return_url
 * @throws          Exception
 *
 * @creationDate    17/12/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Redirect the user to the Category index page.
 */
function local_autologin_redirectToCategory($user,$category,$return_url) {
    try {
        $user = authenticate_user_login( $user->username, $user->generatedpw );

        /// Let's get them all set up.
        add_to_log($category, 'user', 'login', 'index.php?id=' . $user->id . '&category=' .  $category, $user->id, 0, $user->id);
        complete_user_login($user,true); // sets the username cookie

        $url = new moodle_url('/course/index.php?categoryid=' . $category . '&return=' . $return_url);
        redirect($url);
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_autologin_redirectToCategory

/**
 * @param           $user
 * @param           $course_id
 * @param           $return_url
 * @throws          Exception
 *
 * @creationDate    17/12/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Redirect the user to the course page
 */
function local_autologin_redirectToCourse($user,$course_id,$return_url) {
    try {
        $user = authenticate_user_login( $user->username, $user->generatedpw );

        /* Add Log  */
        add_to_log($course_id, 'user', 'login', 'view.php?id=' . $user->id . '&course=' .  $course_id, $user->id, 0, $user->id);
        complete_user_login($user); // sets the username cookie

        $url = new moodle_url('/course/view.php?id=' . $course_id . '&return=' . $return_url);
        redirect($url);
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//local_autologin_redirectToCourse
