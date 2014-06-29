<?php

/**
 * Municipality Block - Main Page
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @author          efaktor     (fbv)
 */

require($CFG->dirroot . '/local/muni_block/locallib.php');

class block_municipality extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_municipality');
    }//init

    function get_content() {
        global  $USER,$OUTPUT;

        $this->content = new stdClass;

        if (isloggedin()) {
            /* Get the municipality connected with the user */
            $muni = local_muni_exists_municipality_user($USER->id);

            if ($muni) {
                /* Show the Municipality Logo */
                $this->content->text = html_writer::start_tag('div',array('class'=>'municipality-content'));
                $this->content->footer = '';

                /* Get the municipality logo */
                $logo = local_muni_get_municipality_logo($muni);
                $this->content->text .= $logo;
                $this->content->text .= html_writer::end_tag('div');
                $this->content->text .= '<h2 style="text-align: center;">' . $muni . '</h2>';
            }else {
                $url_muni = new moodle_url('/local/muni_block/edit_muni.php');
                $this->content->footer = $OUTPUT->action_link($url_muni, get_string('edit_muni', 'block_municipality'),null);
                redirect($url_muni);
            }//if_else
        }//if_loggin

        return $this->content;
    }//get_content
}//block_municipality