<?php
/**
 * Express Login  - Index (Form)
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/11/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      11/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Remove Security Phrase. It will be something internal
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class express_login_form extends moodleform {
    function definition() {
        global $SITE,$USER;
        $form       = $this->_form;

        $plugin_info  = $this->_customdata;

        /* Header   */
        $form->addElement('header', 'express_header',get_string('pluginname','local_express_login'));
        // visible elements
        $form->addElement('static', 'username', get_string('username'), $USER->username);

        /* PIN Code */
        $minimum    = array('4','6','8');
        $digits     = $minimum[$plugin_info->minimum_digits];
        $form->addElement('static', 'pin_description', '', get_string('pin_code_min','local_express_login',$digits));
        $form->addElement('passwordunmask', 'pin_code', get_string('pin_code','local_express_login'), ' size="10" maxlength="' . $digits . '"');
        $form->addHelpButton('pin_code', 'pin_code','local_express_login');
        $form->setType('pin_code', PARAM_RAW);
        $form->addRule('pin_code',get_string('required'), 'required', null, 'server');

        $form->addElement('static', 'express_description', '', get_string('title_info', 'local_express_login',$SITE->fullname));

        $this->add_action_buttons(true, get_string('btn_generate_link','local_express_login'));

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$USER->id);
    }//definition

    function validation($data, $files) {
        /* Variables    */
        $pin_not_valid  = null;
        $err_pin        = null;

        $errors = parent::validation($data, $files);

        $plugin_info  = $this->_customdata;

        /* Check the Password   */
        /* First the correct number of digits   */
        $minimum    = array('4','6','8');
        $digits     = $minimum[$plugin_info->minimum_digits];
        if (strlen($data['pin_code']) != $digits) {
            $errors['pin_code'] = get_string('pin_code_min','local_express_login',$digits);
            return $errors;
        }elseif (!is_numeric($data['pin_code'])) {
            $errors['pin_code'] = get_string('pin_numeric_err','local_express_login');
            return $errors;
        }else {
            /* Check if the PIN code is valid   */
            list($pin_not_valid,$err_pin) = Express_Login::CheckPinCode($data['pin_code'],$plugin_info);
            if ($pin_not_valid) {
                $errors['pin_code'] = $err_pin;
                return $errors;
            }//if_pin_not_valid
        }//if_length_pin_code

        return $errors;
    }//validation
}//express_login_form

class express_login_link_form extends moodleform {
    function definition() {
        global $USER,$SITE;
        $clipBoard_Html = null;
        $form       = $this->_form;

        /* Header   */
        $form->addElement('header', 'express_header',get_string('pluginname','local_express_login'));
        // visible elements
        $form->addElement('static', 'express-link-description', '',get_string('title_link','local_express_login',$SITE->fullname));

        /* Express Link */
        $express_link = Express_Login::Get_ExpressLink($USER->id);
        $clipBoard_Html  = '<div id="clipboardDiv" style="display: none;">' . get_string('clipboardDiv','local_express_login') . '</div>';
        $clipBoard_Html .= '<div></div>';
        /* Add to Bookmark      */
        $bookmarkURL  = '<a href="#">' . $SITE->shortname . '</a>';
        $clipBoard_Html .= '<div id="bookmarkDiv" style="display: none;">' . get_string('bookmarkDiv','local_express_login',$bookmarkURL) . '</div>';
        $form->addElement('html',$clipBoard_Html);

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('button','btn_copy_link',get_string('btn_copy_link','local_express_login'),'data-clipboard-text="' . $express_link . '"');
        $buttons[] = $form->createElement('cancel');

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');

        /* Add Script   */
        $clipBoard_Html  = '<script type="text/javascript" src="zeroclipboard/ZeroClipboard.js"></script>';
        $clipBoard_Html .= '<script type="text/javascript">';
        $clipBoard_Html .= 'var client  = new ZeroClipboard( document.getElementById("id_btn_copy_link") );';
        $clipBoard_Html .= 'var divClip     = document.getElementById("clipboardDiv");';
        $clipBoard_Html .= 'var bookmarkDiv = document.getElementById("bookmarkDiv");';
        $clipBoard_Html .= 'client.on( "ready", function( readyEvent ) {';
        $clipBoard_Html .= 'var urlBook     = Y.one("#id_btn_copy_link").getAttribute("data-clipboard-text");';
        $clipBoard_Html .= 'var newContent  = bookmarkDiv.innerHTML.valueOf();';
        $clipBoard_Html .= 'client.on( "aftercopy", function( event ) {';
        $clipBoard_Html .= 'divClip.style.display = "block";';
        $clipBoard_Html .= 'newContent = bookmarkDiv.innerHTML.replace("#",urlBook);';
        $clipBoard_Html .= 'bookmarkDiv.innerHTML = newContent;';
        $clipBoard_Html .= 'bookmarkDiv.style.display = "block";';
        $clipBoard_Html .= '}); });';
        $clipBoard_Html .= '</script>';
        $form->addElement('html',$clipBoard_Html);

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$USER->id);
    }//definition
}//express_login_link_form

class express_login_change_pin_code extends moodleform {
    function definition() {
        global $SITE,$USER;
        $form       = $this->_form;

        list($plugin_info,$exists_express)  = $this->_customdata;

        /* Header   */
        $form->addElement('header', 'express_header',get_string('header_new_code','local_express_login'));
        // visible elements
        $form->addElement('static', 'username', get_string('username'), $USER->username);

        if ($exists_express) {
            /* First the correct number of digits   */
            $minimum    = array('4','6','8');
            $digits     = $minimum[$plugin_info->minimum_digits];
            /* New PIN Code         */
            $form->addElement('static', 'pin_description', '', get_string('pin_code_min','local_express_login',$digits));
            $form->addElement('passwordunmask', 'pin_code', get_string('pin_new_code','local_express_login'), ' size="10" maxlength="' . $digits . '"');
            $form->addHelpButton('pin_code', 'pin_code','local_express_login');
            $form->setType('pin_code', PARAM_RAW);
            $form->addRule('pin_code',get_string('required'), 'required', null, 'server');

            /* NEW PIN Code (Again) */
            $form->addElement('passwordunmask', 'pin_code_again', get_string('pin_new_code_again','local_express_login'), ' size="10" maxlength="' . $digits . '"');
            $form->addHelpButton('pin_code_again', 'pin_code','local_express_login');
            $form->setType('pin_code_again', PARAM_RAW);
            $form->addRule('pin_code_again',get_string('required'), 'required', null, 'server');

            $form->addElement('static', 'express_description', '', get_string('title_change', 'local_express_login',$SITE->fullname));

            $this->add_action_buttons(true, get_string('btn_generate_link','local_express_login'));
        }else {
            $a = new stdClass();
            $a->site    = $SITE->fullname;
            $url= new moodle_url('/local/express_login/index.php',array('id' => $USER->id));
            $a->url = html_writer::link($url,get_string('pluginname','local_express_login'));
            $form->addElement('static', 'express_description', '', get_string('warning_regenerate', 'local_express_login',$a));
        }


        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$USER->id);
    }//definiton

    function validation($data, $files) {
        /* Variables    */
        $pin_not_valid  = null;
        $err_pin        = null;

        $errors = parent::validation($data, $files);

        list($plugin_info,$exists_express)  = $this->_customdata;

        /* Check the Password   */
        /* First the correct number of digits   */
        if (Express_Login::ValidateExpressLogin_User($data['id'],$data['pin_code'])) {
            $errors['pin_code'] = get_string('pin_new_not_diff_current','local_express_login');
            return $errors;
        }else {
            if ($data['pin_code'] != $data['pin_code_again']) {
                $errors['pin_code'] = get_string('pin_new_diff_err','local_express_login');
                return $errors;
            }else {
                $minimum    = array('4','6','8');
                $digits     = $minimum[$plugin_info->minimum_digits];
                if (strlen($data['pin_code']) != $digits) {
                    $errors['pin_code'] = get_string('pin_code_min','local_express_login',$digits);
                    return $errors;
                }elseif (!is_numeric($data['pin_code'])) {
                    $errors['pin_code'] = get_string('pin_numeric_err','local_express_login');
                    return $errors;
                }else {
                    /* Check if the PIN code is valid   */
                    list($pin_not_valid,$err_pin) = Express_Login::CheckPinCode($data['pin_code'],$plugin_info);
                    if ($pin_not_valid) {
                        $errors['pin_code'] = $err_pin;
                        return $errors;
                    }//if_pin_not_valid
                }//if_length_pin_code
            }//new_code_diff
        }//if_is_valid_current_code

        return $errors;
    }//validation
}//express_login_change_pin_code

class express_login_regenerate_link extends moodleform {
    function definition() {
        /* Variables    */
        global $SITE,$USER;
        $form       = $this->_form;

        $exists_express  = $this->_customdata;

        /* Header   */
        $form->addElement('header', 'express_header',get_string('pluginname','local_express_login'));
        // visible elements
        $form->addElement('static', 'username', get_string('username'), $USER->username);

        if ($exists_express) {
            /* Security Question    */
            $form->addElement('static', 'express_remind', '', get_string('regenerate_link', 'local_express_login'));

            $form->addElement('static', 'express_description', '', get_string('title_regenerate_link', 'local_express_login'));

            $this->add_action_buttons(true, get_string('btn_generate_link','local_express_login'));
        }else {
            $a = new stdClass();
            $a->site    = $SITE->fullname;
            $url= new moodle_url('/local/express_login/index.php',array('id' => $USER->id));
            $a->url = html_writer::link($url,get_string('pluginname','local_express_login'));
            $form->addElement('static', 'express_description', '', get_string('warning_regenerate', 'local_express_login',$a));
        }//if_else

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$USER->id);
    }//definition
}//express_login_regenerate_link

