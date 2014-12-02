<?php

/**
 * Municipality Block - Main Page
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @updateDate      20/08/2014
 * @author          efaktor     (fbv)
 */

require('municipalitylib.php');

class block_municipality extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_municipality');
    }//init

    function get_content() {
        global  $USER,$OUTPUT,$CFG;

        $this->content = new stdClass;

        if (isloggedin()) {
            /* Get the municipality connected with the user */
            $municipality = Municipality::municipality_ExitsMuni_User($USER->id);

            if ($municipality) {
                /* Show the Municipality Logo */
                $this->content->text = html_writer::start_tag('div',array('class'=>'municipality-content'));
                $this->content->footer = '';

                /* Get the municipality logo */
                $this->content->text .= '<img class="logo" alt="' . $municipality->name . '"src="' . $municipality->logo . '"/>';
                $this->content->text .= html_writer::end_tag('div');
                $this->content->text .= '<h2 style="text-align: center;">' . $municipality->name . '</h2>';
            }else {
                $url_muni = new moodle_url('/blocks/municipality/edit_muni.php');
                $this->content->footer = $OUTPUT->action_link($url_muni, get_string('edit_muni', 'block_municipality'),null);
            }//if_else
        }//if_loggin

        return $this->content;
    }//get_content
}//block_municipality