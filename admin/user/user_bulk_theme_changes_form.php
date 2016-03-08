<?php

/**
 * Theme Changes - Bulk Action
 *
 * @package         admin
 * @subpackage      user
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    18/03/2013
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class user_bulk_theme_changes_form extends moodleform {
    function definition() {
        $form = $this->_form;

        $themes = get_list_of_themes();
        //$themes =  get_plugin_list('theme');
        //$themes = array_keys($themes);
        $options = array(''=>'');
        foreach ($themes as $key=>$theme) {
            if (empty($theme->hidefromselector)) {
                $options[$key] = get_string('pluginname', 'theme_'.$theme->name);
            }
        }
        $form->addElement('select','theme',new lang_string('themes'),$options);
        $this->add_action_buttons(true, get_string('bulk_theme', 'local_theme_changes'));
    }//definition
}//user_bulk_theme_changes_form