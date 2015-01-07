<?php
/**
 * Microlearning Users Selector - Filter - Library Plugin Implementation
 *
 * @package         local
 * @subpackage      microlearning/users/filter
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    13/09/2014
 * @author          eFaktor     (fbv)
 */
require_once($CFG->dirroot.'/user/filters/text.php');
require_once($CFG->dirroot.'/user/filters/profilefield.php');
require_once($CFG->dirroot.'/user/filters/cohort.php');
require_once($CFG->dirroot.'/user/filters/courserole.php');
require_once($CFG->dirroot.'/user/filters/globalrole.php');

class microlearning_users_filtering {
    var $_fields;
    var $_addform;
    var $_activeform;
    public $course_id = 0;

    /**
     * Contructor
     * @param array array of visible user fields
     * @param string base url used for submission/return, null if the same of current page
     * @param array extra page parameters
     */
    function microlearning_users_filtering($fieldnames=null, $baseurl=null, $extraparams=null) {
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
            case 'city':        return new user_filter_text('city', get_string('city'), $advanced, 'city');
            case 'country':     return new user_filter_select('country', get_string('country'), $advanced, 'country', get_string_manager()->get_list_of_countries(), $USER->country);
            case 'profile':     return new user_filter_profilefield('profile', get_string('profilefields', 'admin'), $advanced);
            case 'courserole':  return new user_filter_courserole('courserole', get_string('courserole', 'filters'), $advanced);
            case 'systemrole':  return new user_filter_globalrole('systemrole', get_string('globalrole', 'role'), $advanced);
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
        /* Variables    */
        global $SESSION;

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

        if (empty($sqls)) {
            return array('', array());
        } else {
            $sqls = implode(' AND ', $sqls);
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
}//microlearning_users_filtering

class microlearning_users_filter_type {
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
    function microlearning_users_filter_type($name, $label, $advanced) {
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
}//microlearning_users_filter_type