<?php
/**
 * Force Update Profile - Bulk Action (Library) / CLass
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/08/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      23/04/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the correct identifier for the profiles fields, instead of the language string
 *
 */

define('CITY','city');
define('COUNTRY','country');
define('LANGUAGE','lang');
define('DESCRIPTION','description');
define('PICTURE','picture');
define('ALT','imagealt');
define('FIRST_PHONETIC','firstnamephonetic');
define('LAST_PHONETIC','lastnamephonetic');
define('MIDDLE_NAME','middlename');
define('ALTERNATE_NAME','alternatename');
define('INTEREST','interests');
define('WEBPAGE','url');
define('ICQ_NUMBER','icq');
define('SKYPE','skype');
define('AIM','aim');
define('YAHOO','yahoo');
define('MSN','msn');
define('IDNUMBER','idnumber');
define('INSTITUTION','institution');
define('DEPARTMENT','department');
define('PHONE','phone1');
define('PHONE_TWO','phone2');
define('ADDRESS','address');


class ForceProfile {

    /**
     * @static
     * @param           $users
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    21/04/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the list of users have to update her/his profile
     */
    public static function ForceProfile_GetUsers($users) {
        /* Variables    */
        global $DB;

        try {
            /* Users List       */
            $lst_users = null;

            /* SQL Instruction */
            $sql = " SELECT		CONCAT(u.lastname, ', ', u.firstname) as 'user'
                     FROM		{user}	u
                     WHERE		u.id IN ($users)
                     ORDER BY	u.lastname, u.firstname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lst_users .=  $instance->user . '<br/>';
                }//for_rdo
            }//if_Rdo

            return $lst_users;
        }catch(Exception $ex){
            throw $ex;
        }//try_catch
    }//ForceProfile_GetUsers

    /**
     * @static
     * @return          array
     *
     * @creationDate    21/08/2014
     * @auhtor          eFaktor     (fbv)
     *
     * Description
     * Get the list of profile's fields.
     */
    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the list of profile's fields.
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a key for each field instead of the language string.
     */
    public static function ForceProfile_GetChoicesProfile() {
        /* Variables    */
        $profile    = array();
        $extra      = array();

        try {
            /* Standard */
            $profile[CITY]              = get_string('city');
            $profile[COUNTRY]           = get_string('country');
            $profile[LANGUAGE]          = get_string('preferredlanguage');
            $profile[DESCRIPTION]       = get_string('userdescription');
            $profile[PICTURE]           = get_string('currentpicture');
            $profile[ALT]               = get_string('imagealt');

            /* Alternative Names */
            $profile[FIRST_PHONETIC]    = get_string('firstnamephonetic');
            $profile[LAST_PHONETIC]     = get_string('lastnamephonetic');
            $profile[MIDDLE_NAME]       = get_string('middlename');
            $profile[ALTERNATE_NAME]    = get_string('alternatename');

            /* Interest */
            $profile[INTEREST]          = get_string('interestslist');

            /* Optional */
            $profile[WEBPAGE]           = get_string('webpage');
            $profile[ICQ_NUMBER]        = get_string('icqnumber');
            $profile[SKYPE]             = get_string('skypeid');
            $profile[AIM]               = get_string('aimid');
            $profile[YAHOO]             = get_string('yahooid');
            $profile[MSN]               = get_string('msnid');
            $profile[IDNUMBER]          = get_string('idnumber');
            $profile[INSTITUTION]       = get_string('institution');
            $profile[DEPARTMENT]        = get_string('department');
            $profile[PHONE]             = get_string('phone');
            $profile[PHONE_TWO]         = get_string('phone2');
            $profile[ADDRESS]           = get_string('address');

            /* EXTRA PROFILE    */
            $extra      = self::ForceProfile_GetExtraProfile();
            $profile    = $profile + $extra;

            return $profile;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch


    }//ForceProfile_GetChoicesProfile

    /**
     * @param               $msg
     * @throws              Exception
     *
     * @creationDate        21/08/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Send a notification eMail to the user
     *
     * @updateDate          24/04/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Add the content of the eMail
     */
    public static function ForceProfile_SendNotification($msg){
        /* Variables    */
        global $SESSION,$SITE,$CFG;

        try {
            /* Send a mail  */
            $a = new stdClass();
            $a->Site        = $SITE->shortname;
            foreach($SESSION->bulk_users as $user_id) {
                $a->url = $CFG->wwwroot . '/local/force_profile/confirm_profile.php?id=' . $user_id;
                $user   = get_complete_user_data('id',$user_id);
                /* Content eMail    */
                $subject    = (string)new lang_string('application_subject','local_force_profile',$a,$user->lang);
                $body       = $msg . '</br></br>';
                $body      .= (string)new lang_string('application_body','local_force_profile',$a,$user->lang);
                if (email_to_user($user, $SITE->shortname, $subject, $body,$body)) {
                    self::ForceProfile_InsertUserForceProfile($user_id);
                }//if_email
            }//for_user

        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_SendNotification

    /**
     * @static
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has to update his/her profile.
     */
    public static function ForceProfile_HasToUpdateProfile($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT id
                     FROM   {user_force_profile}
                     WHERE  (timeupdated is NULL
                             OR
                             timeupdated = 0)
                        AND userid = :user_id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_HasToUpdateProfile

    /**
     * @static
     * @param           $user_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the fields of the profile that the user should update.
     */
    public static function ForceProfile_GetFieldsToUpdate($user_id) {
        /* Variables    */
        $lst_fields = new stdClass();
        $lst_fields->normal     = null;
        $lst_fields->profile    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id'] = $user_id;

            /* First the fields are not extra profile   */
            $params['type']     = 'user';
            $lst_fields->normal = self::ForceProfile_getNormalFields($params);

            /* Finally the fields are extra profile     */
            $params['type']         = 'extra_profile';
            $lst_fields->profile    = self::ForceProfile_getExtraProfileFields($params);

            return $lst_fields;
        }catch(Exception $ex){
            throw $ex;
        }//try_catch
    }//ForceProfile_GetFieldsToUpdate

    /**
     * @static
     * @param           $form
     * @param           $field
     * @param           $user_id
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add an element to the form. Extra Profile Field
     */
    public static function ForceProfile_CreateExtraProfileElement(&$form,$field,$user_id) {
        /* Variables    */
        global $CFG,$SESSION;

        try {
            /* Get Info Field --> User */
            $extra = self::ForceProfile_GetInfoFieldUser($user_id,$field);

            /* Eadd Extra Field Profile */
            require_once($CFG->dirroot.'/user/profile/lib.php');
            require_once($CFG->dirroot . '/user/profile/field/'.$extra->datatype.'/field.class.php');

            $form->addElement('html','<h4>' . $field . '</h4>');

            $newfield = 'profile_field_'.$extra->datatype;
            $formfield = new $newfield($extra->id, $user_id);
            $formfield->edit_field($form);
            $form->setDefault('profile_field_'.$extra->shortname,$extra->data);

            $SESSION->elements[$field] = 'profile_field_'.$extra->shortname;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_CreateExtraProfileElement

    /**
     * @static
     * @param           $form
     * @param           $field
     * @param           $user
     * @param           $file_options
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add an element to the form. Profile Field
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Profile fields use the correct identifier instead of the language string
     */
    public static function ForceProfile_CreateUserElement(&$form,$field,$user,$file_options) {
        /* Variables        */
        global $OUTPUT;

        try {

            switch ($field) {
                case CITY:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('city') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_city', get_string('city'), 'maxlength="120" size="21" disabled');
                    $form->setType('old_city', PARAM_TEXT);
                    $form->setDefault('old_city',$user->city);

                    $form->addElement('text', 'city', null, 'maxlength="120" size="21" ');
                    $form->setType('city', PARAM_TEXT);
                    $form->addRule('city','','required', null, 'server');

                    break;

                case COUNTRY:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('country') . '</h4>');

                    /* Add Element  */
                    $choices = get_string_manager()->get_list_of_countries();
                    $choices= array(''=>get_string('selectacountry').'...') + $choices;
                    $form->addElement('select', 'country', get_string('selectacountry'), $choices);
                    if (!empty($user->country)) {
                        $form->setDefault('country', $user->country);
                    }
                    $form->addRule('country','','required', null, 'server');

                    break;

                case LANGUAGE:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('preferredlanguage') . '</h4>');

                    /* Add Element  */
                    $form->addElement('select', 'lang', get_string('preferredlanguage'), get_string_manager()->get_list_of_translations());
                    $form->setDefault('lang', $user->lang);

                    break;

                case DESCRIPTION:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('userdescription') . '</h4>');

                    /* Add Element  */
                    $form->addElement('editor', 'description', get_string('userdescription'),null);
                    $form->setType('description', PARAM_CLEANHTML);
                    $form->addHelpButton('description', 'userdescription');
                    $form->addRule('description','','required', null, 'server');

                    break;

                case PICTURE:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('currentpicture') . '</h4>');

                    if (!empty($user->picture)) {
                        $image_value = $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size'=>64));
                    }else {
                        $image_value = get_string('none');
                    }//if_else

                    /* Add Element  */
                    $form->addElement('static', 'currentpicture', get_string('currentpicture'));
                    $form->setDefault('currentpicture',$image_value);

                    $form->addElement('checkbox', 'deletepicture', get_string('delete'));
                    $form->setDefault('deletepicture', 0);
                    //$SESSION->elements[$field] = 'deletepicture';

                    $form->addElement('filemanager', 'imagefile', get_string('newpicture'), '',$file_options);
                    $form->addHelpButton('imagefile', 'newpicture');
                    //$SESSION->elements[$field] = 'imagefile';

                    break;

                case ALT:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('imagealt') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_imagealt', get_string('imagealt'), 'maxlength="100" size="30" disabled');
                    $form->setType('old_imagealt', PARAM_TEXT);
                    $form->setDefault('old_imagealt', $user->imagealt);

                    $form->addElement('text', 'imagealt', null, 'maxlength="100" size="30"');
                    $form->setType('imagealt', PARAM_TEXT);
                    $form->addRule('imagealt','','required', null, 'server');

                    break;

                case FIRST_PHONETIC:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('firstnamephonetic') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_firstnamephonetic', get_string('firstnamephonetic'), 'maxlength="100" size="30" disabled');
                    $form->setType('old_firstnamephonetic', PARAM_NOTAGS);
                    $form->setDefault('old_firstnamephonetic',$user->firstnamephonetic);

                    $form->addElement('text', 'firstnamephonetic', null, 'maxlength="100" size="30"');
                    $form->setType('firstnamephonetic', PARAM_NOTAGS);
                    $form->addRule('firstnamephonetic','','required', null, 'server');

                    break;

                case LAST_PHONETIC:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('lastnamephonetic') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_lastnamephonetic', get_string('lastnamephonetic'), 'maxlength="100" size="30" disabled');
                    $form->setType('old_lastnamephonetic', PARAM_NOTAGS);
                    $form->setDefault('old_lastnamephonetic',$user->lastnamephonetic);

                    $form->addElement('text', 'lastnamephonetic', null, 'maxlength="100" size="30"');
                    $form->setType('lastnamephonetic', PARAM_NOTAGS);
                    $form->addRule('lastnamephonetic','','required', null, 'server');

                    break;

                case MIDDLE_NAME:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('middlename') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_middlename', get_string('middlename'), 'maxlength="100" size="30" disabled');
                    $form->setType('old_middlename', PARAM_NOTAGS);
                    $form->setDefault('old_middlename',$user->middlename);

                    $form->addElement('text', 'middlename',null , 'maxlength="100" size="30"');
                    $form->setType('middlename', PARAM_NOTAGS);
                    $form->addRule('middlename','','required', null, 'server');

                    break;

                case ALTERNATE_NAME:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('alternatename') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_alternatename', get_string('alternatename'), 'maxlength="100" size="30" disabled');
                    $form->setType('old_alternatename', PARAM_NOTAGS);
                    $form->setDefault('old_alternatename',$user->alternatename);

                    $form->addElement('text', 'alternatename',null , 'maxlength="100" size="30"');
                    $form->setType('alternatename', PARAM_NOTAGS);
                    $form->addRule('alternatename','','required', null, 'server');

                    break;

                case INTEREST:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('interestslist') . '</h4>');

                    /* Add Element  */
                    $form->addElement('tags', 'interests', get_string('interestslist'), array('display' => 'noofficial'));
                    $form->setDefault('interests',tag_get_tags_array('user', $user->id));
                    $form->addHelpButton('interests', 'interestslist');
                    $form->addRule('interests','','required', null, 'server');

                    break;

                case WEBPAGE:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('webpage') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_url', get_string('webpage'), 'maxlength="255" size="50" disabled');
                    $form->setType('old_url', PARAM_URL);
                    $form->setDefault('old_url',$user->url);

                    $form->addElement('text', 'url', null, 'maxlength="255" size="50"');
                    $form->setType('url', PARAM_URL);
                    $form->addRule('url','','required', null, 'server');


                    break;

                case ICQ_NUMBER:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('icqnumber') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_icq', get_string('icqnumber'), 'maxlength="15" size="25" disabled');
                    $form->setType('old_icq', PARAM_NOTAGS);
                    $form->setDefault('old_icq',$user->icq);

                    $form->addElement('text', 'icq', null, 'maxlength="15" size="25"');
                    $form->setType('icq', PARAM_NOTAGS);
                    $form->addRule('icq','','required', null, 'server');

                    break;

                case SKYPE:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('skypeid') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_skype', get_string('skypeid'), 'maxlength="50" size="25" disabled');
                    $form->setType('old_skype', PARAM_NOTAGS);
                    $form->setDefault('old_skype',$user->skype);

                    $form->addElement('text', 'skype', null, 'maxlength="50" size="25"');
                    $form->setType('skype', PARAM_NOTAGS);
                    $form->addRule('skype','','required', null, 'server');

                    break;

                case AIM:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('aimid') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_aim', get_string('aimid'), 'maxlength="50" size="25" disabled');
                    $form->setType('old_aim', PARAM_NOTAGS);
                    $form->setDefault('old_aim',$user->aim);

                    $form->addElement('text', 'aim', null, 'maxlength="50" size="25"');
                    $form->setType('aim', PARAM_NOTAGS);
                    $form->addRule('aim','','required', null, 'server');

                    break;

                case YAHOO:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('yahooid') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_yahoo', get_string('yahooid'), 'maxlength="50" size="25" disabled');
                    $form->setType('old_yahoo', PARAM_NOTAGS);
                    $form->setDefault('old_yahoo',$user->yahoo);

                    $form->addElement('text', 'yahoo', null, 'maxlength="50" size="25"');
                    $form->setType('yahoo', PARAM_NOTAGS);
                    $form->addRule('yahoo','','required', null, 'server');

                    break;

                case MSN:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('msnid') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_msn', get_string('msnid'), 'maxlength="50" size="25" disabled');
                    $form->setType('old_msn', PARAM_NOTAGS);
                    $form->setDefault('old_msn',$user->msn);

                    $form->addElement('text', 'msn', null, 'maxlength="50" size="25"');
                    $form->setType('msn', PARAM_NOTAGS);
                    $form->addRule('msn','','required', null, 'server');

                    break;

                case IDNUMBER:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('idnumber') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_idnumber', get_string('idnumber'), 'maxlength="255" size="25" disabled');
                    $form->setType('old_idnumber', PARAM_NOTAGS);
                    $form->setDefault('old_idnumber',$user->idnumber);

                    $form->addElement('text', 'idnumber', null , 'maxlength="255" size="25"');
                    $form->setType('idnumber', PARAM_NOTAGS);
                    $form->addRule('idnumber','','required', null, 'server');

                    break;

                case INSTITUTION:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('institution') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_institution', get_string('institution'), 'maxlength="255" size="25" disabled');
                    $form->setType('old_institution', PARAM_TEXT);
                    $form->setDefault('old_institution',$user->institution);

                    $form->addElement('text', 'institution', null, 'maxlength="255" size="25" ');
                    $form->setType('institution', PARAM_TEXT);
                    $form->addRule('institution','','required', null, 'server');

                    break;

                case DEPARTMENT:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('department') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_department', get_string('department'), 'maxlength="255" size="25" disabled');
                    $form->setType('old_department', PARAM_TEXT);
                    $form->setDefault('old_department',$user->department);

                    $form->addElement('text', 'department', null, 'maxlength="255" size="25"');
                    $form->setType('department', PARAM_TEXT);
                    $form->addRule('department','','required', null, 'server');

                    break;

                case PHONE:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('phone') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_phone1', get_string('phone'), 'maxlength="20" size="25" disabled');
                    $form->setType('old_phone1', PARAM_NOTAGS);
                    $form->setDefault('old_phone1',$user->phone1);

                    $form->addElement('text', 'phone1', null, 'maxlength="20" size="25"');
                    $form->setType('phone1', PARAM_NOTAGS);
                    $form->addRule('phone1','','required', null, 'server');

                    break;

                case PHONE_TWO:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('phone2') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_phone2', get_string('phone2'), 'maxlength="20" size="25" disabled');
                    $form->setType('old_phone2', PARAM_NOTAGS);
                    $form->setDefault('old_phone2',$user->phone2);

                    $form->addElement('text', 'phone2', null, 'maxlength="20" size="25"');
                    $form->setType('phone2', PARAM_NOTAGS);
                    $form->addRule('phone2','','required', null, 'server');

                    break;

                case ADDRESS:
                    /* Header   */
                    $form->addElement('html','<h4>' . get_string('address') . '</h4>');

                    /* Add Element  */
                    $form->addElement('text', 'old_address', get_string('address'), 'maxlength="255" size="25" disabled');
                    $form->setType('old_address', PARAM_TEXT);
                    $form->setDefault('old_address',$user->address);

                    $form->addElement('text', 'address', null, 'maxlength="255" size="25"');
                    $form->setType('address', PARAM_TEXT);
                    $form->addRule('address','','required', null, 'server');

                    break;

                default:
                    break;
            }//switch

        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_CreateUserElement

    /**
     * @static
     * @param           $user_id
     * @param           $field
     * @param           $name
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the profile field connected with the user. 'user' table
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add params to tag_set
     */
    public static function ForceProfile_UpdateUserForceProfile($user_id,$field,$name){
        /* Variables    */
        global $DB;

        try {
            /* Begin Transaction */
            $trans = $DB->start_delegated_transaction();
            /* User */
            $instance_user = new stdClass();
            $instance_user->id              = $user_id;
            $instance_user->$name           = $field->value;
            $instance_user->timemodified    = time();

            if ($name == 'interests') {
                tag_set('user', $user_id, $field->value,'core',CONTEXT_USER::instance($user_id)->id);
            }

            $DB->update_record('user',$instance_user);

            /* User Force Profile Table */
            $DB->update_record('user_force_profile',$field);

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch(Exception $ex){
            /* Rollback */
            $trans->rollback($ex);
            throw $ex;
        }//try_catch
    }//ForceProfile_UpdateUserForceProfile

    /**
     * @static
     * @param           $user_id
     * @param           $field
     * @param           $data
     * @param           $name
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the extra profile field connected with the user.
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to the Competence Manager Level Five
     */
    public static function ForceProfile_UpdateExtraUserForceProfile($user_id,$field,$data,$name) {
        /* Variables    */
        global $DB,$CFG,$SESSION;

        try {
            /* Get Info Field --> User */
            $extra = self::ForceProfile_GetInfoFieldUser($user_id,$field->name);
            $instance_user = new stdClass();
            $instance_user->id       = $user_id;
            $instance_user->$name    = $data->$name;

            if ($field->datatype != 'competence') {
                require_once($CFG->dirroot.'/user/profile/lib.php');
                require_once($CFG->dirroot . '/user/profile/field/'.$extra->datatype.'/field.class.php');
                $newfield = 'profile_field_'.$extra->datatype;
                $formfield = new $newfield($extra->id, $user_id);
                $formfield->edit_save_data($instance_user);

                /* User Force Profile Table */
                $field->old_value   = $extra->data;
                $DB->update_record('user_force_profile',$field);
            }else {
                if ($rdo = $DB->get_records('user_info_competence_data',array('userid' => $user_id))) {
                    $DB->update_record('user_force_profile',$field);
                }
            }//if_datatype

        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_UpdateExtraUserForceProfile

    /* ********************* */
    /* PROTECTED   FUNCTIONS */
    /* ********************* */

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get a list of the profile fields - Extra.
     */
    protected static function ForceProfile_GetExtraProfile() {
        /* Variables    */
        global $DB;

        try {
            /* Extra Profile    */
            $extra = array();
            $fields = $DB->get_records('user_info_field',null,'name ASC','name');
            if ($fields) {
                foreach ($fields as $field) {
                    $extra[$field->name] = $field->name;
                }//for_each
            }//if_fields

            return $extra;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_GetExtraProfile

    /**
     * @static
     * @param           $user_id
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new record into 'user_force_profile'. Each record means the fields the user has to update.
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the correct identifier for the profile field, instead of the name.
     */
    protected static function ForceProfile_InsertUserForceProfile($user_id) {
        /* Variables    */
        global $DB,$SESSION;

        try {
            /* EXTRA PROFILE    */
            $extra = self::ForceProfile_GetExtraProfile();

            foreach($SESSION->fields as $key=>$value) {
                /* New Instance */
                $instance = new stdClass();
                $instance->userid       = $user_id;

                if (in_array($value,$extra)) {
                    $instance->type     = 'extra_profile';
                    $instance->field    = $value;
                }else {
                    $instance->type     = 'user';
                    $instance->field    = $key;
                }

                $instance->timecreated  = time();
                $instance->confirmed    = 0;

                /* Check if Exits   */
                if (!self::ForceProfile_CheckField($instance)) {
                    $instance->id = $DB->insert_record('user_force_profile',$instance);
                }//if_not_exists
            }//for_each

        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_InsertUserForceProfile

    /**
     * @static
     * @param           $instance
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if just exist the entry for the field.
     *
     * @updateDate      23/04/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * timeupdated can be null or 0
     */
    protected static function ForceProfile_CheckField($instance) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid']       = $instance->userid;
            $params['type']         = $instance->type;
            $params['field']        = $instance->field;
            $params['confirmed']    = 0;

            /* SQL Instruction  */
            $sql = " SELECT   id
                     FROM     {user_force_profile}
                     WHERE    userid    = :userid
                        AND   type      = :type
                        AND   field     = :field
                        AND   confirmed = :confirmed
                        AND   (timeupdated IS NULL
                               OR
                               timeupdated = 0) ";

            /* Execute      */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_CheckField

    /**
     * @static
     * @param           $user_id
     * @param           $field
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the detail of extra profile field.
     */
    protected static function ForceProfile_GetInfoFieldUser($user_id,$field) {
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['name']     = $field;
            $params['user_id']  = $user_id;

            /* SQL Instruction */
            $sql = " SELECT     uif.id,
                                uif.datatype,
                                uif.shortname,
                                uid.data
                     FROM            {user_info_field}   uif
                        LEFT JOIN    {user_info_data}    uid    ON  uid.fieldid = uif.id
                                                                AND uid.userid  = :user_id
                     WHERE      uif.name = :name ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_GetInfoFieldUser


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $params
     * @return          array
     * @throws          Exception
     *
     * @creationDate    04/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the fields to update that are not 'Extra Profile'
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Timeupdated can be zero or null
     */
    private static function ForceProfile_getNormalFields($params) {
        /* Variables    */
        global $DB;
        $lst_fields = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT 	id,
                                type,
                                field
                     FROM 	    {user_force_profile}
                     WHERE	    (timeupdated is NULL
                                 OR
                                 timeupdated = 0)
                        AND     userid = :user_id
                        AND     type   = :type
                     ORDER BY   field ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $field = new stdClass();
                    $field->id   = $instance->id;
                    $field->type = $instance->type;
                    $field->name = $instance->field;

                    $lst_fields[$instance->id] = $field;
                    }//for_rdo
                }//if_rdo

            return $lst_fields;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_getNormalFields

    /**
     * @param           $params
     * @return          array
     * @throws          Exception
     *
     * @creationDate    04/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the extra user profile to update
     *
     * @updateDate      23/04/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Timeupdated can be zero or null
     * Add datatype
     */
    private static function ForceProfile_getExtraProfileFields($params) {
        /* Variables    */
        global $DB;
        $lst_fields = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT     ufp.id,
                                ufp.type,
                                ufp.field,
                                uif.datatype
                     FROM 		{user_force_profile}		ufp
                         JOIN	{user_info_field}			uif		ON uif.name = ufp.field
                     WHERE		(ufp.timeupdated is NULL
                                 OR
                                 ufp.timeupdated = 0)
                         AND 	ufp.userid = :user_id
                         AND    ufp.type   = :type
                     ORDER BY	uif.categoryid, uif.sortorder ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $field              = new stdClass();
                    $field->id          = $instance->id;
                    $field->type        = $instance->type;
                    $field->name        = $instance->field;
                    $field->datatype    = $instance->datatype;

                    $lst_fields[$instance->id] = $field;
                }//for_rdo
            }//if_rdo

            return $lst_fields;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ForceProfile_getExtraProfileFields
}//ForceProfile