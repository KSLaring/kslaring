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
 * Report Competence Manager - Company Report  Filter - Library Plugin Implementation
 *
 * @package         report
 * @subpackage      manager/company_report/filter
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    19/03/2014
 * @author          eFaktor     (fbv)
 */
require_once($CFG->dirroot.'/user/filters/text.php');
require_once($CFG->dirroot.'/user/filters/profilefield.php');

class company_report_filtering {
    var $_fields;
    var $_addform;
    var $_activeform;
    var $_my_companies;

    /**
     * Contructor
     * @param array array of visible user fields
     * @param string base url used for submission/return, null if the same of current page
     * @param array extra page parameters
     */
    function company_report_filtering($fieldnames=null, $baseurl=null, $extraparams=null) {
        global $SESSION;

        if (!isset($SESSION->user_filtering)) {
            $SESSION->user_filtering = array();
        }

        if (empty($fieldnames)) {
            $fieldnames = array('realname'=>0, 'lastname'=>1, 'firstname'=>1, 'email'=>1, 'city'=>1, 'country'=>1,
                'confirmed'=>1, 'suspended'=>1, 'profile'=>1, 'courserole'=>1, 'systemrole'=>1, 'cohort'=>1,
                'firstaccess'=>1, 'lastaccess'=>1, 'neveraccessed'=>1, 'timemodified'=>1,
                'nevermodified'=>1, 'username'=>1, 'auth'=>1, 'mnethostid'=>1);
        }

        $this->_fields  = array();

        foreach ($fieldnames as $fieldname=>$advanced) {
            if ($field = $this->get_field($fieldname, $advanced)) {
                $this->_fields[$fieldname] = $field;
            }
        }

        // fist the new filter form
        $this->_addform = new user_add_filter_form($baseurl, array('fields'=>$this->_fields, 'extraparams'=>$extraparams));
        if ($adddata = $this->_addform->get_data()) {
            foreach($this->_fields as $fname=>$field) {
                $data = $field->check_data($adddata);
                if ($data === false) {
                    continue; // nothing new
                }
                if (!array_key_exists($fname, $SESSION->user_filtering)) {
                    $SESSION->user_filtering[$fname] = array();
                }
                $SESSION->user_filtering[$fname][] = $data;
            }
            // clear the form
            $_POST = array();
            $this->_addform = new user_add_filter_form($baseurl, array('fields'=>$this->_fields, 'extraparams'=>$extraparams));
        }

        // now the active filters
        $this->_activeform = new user_active_filter_form($baseurl, array('fields'=>$this->_fields, 'extraparams'=>$extraparams));
        if ($adddata = $this->_activeform->get_data()) {
            if (!empty($adddata->removeall)) {
                $SESSION->user_filtering = array();

            } else if (!empty($adddata->removeselected) and !empty($adddata->filter)) {
                foreach($adddata->filter as $fname=>$instances) {
                    foreach ($instances as $i=>$val) {
                        if (empty($val)) {
                            continue;
                        }
                        unset($SESSION->user_filtering[$fname][$i]);
                    }
                    if (empty($SESSION->user_filtering[$fname])) {
                        unset($SESSION->user_filtering[$fname]);
                    }
                }
            }
            // clear+reload the form
            $_POST = array();
            $this->_activeform = new user_active_filter_form($baseurl, array('fields'=>$this->_fields, 'extraparams'=>$extraparams));
        }
        // now the active filters
    }//constructor_company_report_filtering

    function set_MyCompanies($my_companies) {
        if ($my_companies) {
            $this->_my_companies = $my_companies;
        }
    }//set_MyCompanies

    /**
     * Creates known user filter if present
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    function get_field($fieldname, $advanced) {
        global $USER, $CFG, $DB, $SITE;

        switch ($fieldname) {
            case 'username':    return new user_filter_text('username', get_string('username'), $advanced, 'username');
            case 'realname':    return new user_filter_text('realname', get_string('fullnameuser'), $advanced, $DB->sql_fullname());
            case 'email':       return new user_filter_text('email', get_string('email'), $advanced, 'email');
            case 'profile':     return new user_filter_profilefield('profile', get_string('profilefields', 'admin'), $advanced);
            default:            return null;
        }
    }//get_field

    /**
     * Returns sql where statement based on active user filters
     * @param string $extra sql
     * @param array named params (recommended prefix ex)
     * @return array sql string and $params
     */
    function get_sql_filter($extra='', array $params=null) {
        global $SESSION,$USER;
        $my_users    = null;
        $sql_allowed = '';

        $sqls = array();
        if ($extra != '') {
            $sqls[] = $extra;
        }
        $params = (array)$params;

        if (!empty($SESSION->user_filtering)) {
            foreach ($SESSION->user_filtering as $fname=>$datas) {
                if (!array_key_exists($fname, $this->_fields)) {
                    continue; // filter not used
                }
                $field = $this->_fields[$fname];
                foreach($datas as $i=>$data) {
                    list($s, $p) = $field->get_sql_filter($data);
                    $sqls[] = $s;
                    $params = $params + $p;
                }
            }
        }

        /* Remove  users fron not my company */
        /* Users  allowed to see          */
        if ($this->_my_companies) {
            $my_users = CompetenceManager::get_users_my_companies($this->_my_companies,$USER->id);
        }//if_my_companies

        if (empty($sqls)) {
            if ($my_users) {
                $sql_allowed .= " AND id IN ($my_users)";
            }

            return array($sql_allowed, array());
        } else {
            if ($my_users) {
                $sql_allowed .= " AND id IN ($my_users)";
            }

            $sqls = implode(' AND ', $sqls);
            $sqls .= $sql_allowed;

            return array($sqls, $params);
        }
    }

    /**
     * Print the add filter form.
     */
    function display_add() {
        $this->_addform->display();
    }

    /**
     * Print the active filter form.
     */
    function display_active() {
        $this->_activeform->display();
    }
}//company_report_filtering

class company_report_filter_type {
    /**
     * The name of this filter instance.
     */
    var $_name;

    /**
     * The label of this filter instance.
     */
    var $_label;

    /**
     * Advanced form element flag
     */
    var $_advanced;

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function company_report_filter_type($name, $label, $advanced) {
        $this->_name     = $name;
        $this->_label    = $label;
        $this->_advanced = $advanced;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return string the filtering condition or null if the filter is disabled
     */
    function get_sql_filter($data) {
        print_error('mustbeoveride', 'debug', '', 'get_sql_filter');
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        print_error('mustbeoveride', 'debug', '', 'check_data');
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        print_error('mustbeoveride', 'debug', '', 'setupForm');
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        print_error('mustbeoveride', 'debug', '', 'get_label');
    }
}//video_filter_type
