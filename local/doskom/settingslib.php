<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * WSDOSKOM -  Settings Lib
 *
 * @package         local
 * @subpackage      doskom/cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    04/09/2017
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

class admin_setting_link_doskom extends admin_setting {
    private $lnkview = null;
    private $lnkadd  = null;

    public function __construct($name, $visiblename, $description, $defaultsetting,$lnkview,$lnkadd) {
        parent::__construct($name, $visiblename, $description, $defaultsetting);

        // Link to view source/company
        $this->lnkview  = $lnkview;
        // Link to add course/company
        $this->lnkadd   = $lnkadd;
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_setting() {
        return false;
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_defaultsetting() {
        return false;
    }

    /**
     * Never write settings
     * @return string Always returns an empty string
     */
    public function write_setting($data) {
        // do not write any setting
        return '';
    }

    /**
     * Returns an HTML string
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $OUTPUT;
        $return = '';

        $html = html_writer::start_div();
            // div_doskom
            $html .= html_writer::start_div('div_doskom dk_left');
                $html .= $this->lnkview;
            $html .= html_writer::end_div();//div_doskom

            // div_doskom
            $html .= html_writer::start_div('div_doskom dk_right');
                $html .= $this->lnkadd;
            $html .= html_writer::end_div();//div_doskom
        $html .= html_writer::end_div();

        return format_admin_setting($this, $this->visiblename, $html,
            $this->description, true, '', '', $query);
    }
}//admin_setting_link_doskom