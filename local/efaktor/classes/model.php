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

//namespace local_efaktor;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Model class
 *
 * @package         local
 * @subpackage      efaktor
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_efaktor_model {

    // The datalist object
    protected $datalist = null;

    // The data
    protected $data = null;

    // The related filter data returned from the form
    protected $filterdata = null;

    // The related sort data returned from the table
    protected $sort = null;

    // The related sort data returned from the table
    protected $fields = null;

    // The query SQL
    protected $sql = '';

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Set filter and sort and get the data from the DB
     */
    public function set_db_data($sql = '', $filterdata = null, $sort = null) {
        $this->sql = $sql;

        if (!is_null($filterdata)) {
            $this->filterdata = $filterdata;
        }
        if (!is_null($sort)) {
            $this->sort = $sort;
        }

        if (!empty($sql)) {
            $this->get_data_from_db();
        }
    }

    /**
     * Set filter and sort and get the data from a JSON fixture
     */
    public function set_fixture_data($fname, $objectname, $fields = array(),
            $filterdata = null, $sort = null) {

        if (!is_null($filterdata)) {
            $this->filterdata = $filterdata;
        }
        if (!is_null($sort)) {
            $this->sort = $sort;
        }

        $this->get_data_from_fixture($fname, $objectname, $fields);
    }

    /**
     * Get the data from the DB and save it in the $data property
     */
    protected function get_data_from_db() {
        global $DB;

        list($sql, $params) = $this->add_sql_filters($this->sql, $this->filterdata);

        if ($this->sort) {
            $sql .= ' ORDER BY ' . $this->sort;
        }

        $this->data = $DB->get_records_sql($sql, $params);
    }

    /**
     * Add SQL filters
     *
     * @param String $sql The SQL query
     *
     * @return Array An array with the extended SQL and the parameters
     */
    protected function add_sql_filters($sql, $fromform = null) {
        global $DB;

        $params = array();

        if (is_null($fromform)) {
            return array($sql, $params);
        }

//        $sql .= " WHERE id != 0";
//
//        if (!empty($fromform->formitem)) {
//            $sql .= ' AND fieldname = :formitem';
//            $params['formitem'] = $fromform->formitem;
//        }

        return array($sql, $params);
    }

    /**
     * Laod the data from a fixture.
     *
     * The fixture must be saved in the same subpackage and must a JSON data
     *
     * @param String $fname    The fixture name
     * @param String $itemname The name of the item within the JSON data to save
     * @param Array  $fields   The fields to use
     */
    protected function get_data_from_fixture($fname, $itemname = null, $fields = array()) {
        $data = $this->get_fixture($fname);

        if (!is_null($itemname)) {
            $data = $data[$itemname];
        }

        // Get only the listed fields
        if (!empty($fields)) {
            $d = array();
            foreach ($data as $row) {
                $r = array();
                foreach ($fields as $field) {
                    if (isset($row[$field])) {
                        $r[$field] = $row[$field];
                    }
                }
                $d[] = $r;
            }
            $data = $d;
            unset($r);
            unset($d);
        }

        // Set the datalist object
        $this->datalist = new local_efaktor_datalist($data);

        // Add the datalist filters and filter the datalist
        $this->add_datalist_filters();
        $this->datalist->find();

        // Add the datalist sort
        $datalistsort = array();
        if ($this->datalist->length()) {
            $datalistsort = $this->get_datalist_sort();
        }

        if (!empty($datalistsort)) {
            list($fields, $order) = $datalistsort;
            $this->data = $this->datalist->sort($fields, $order);
        } else {
            $this->data = $this->datalist->found();
        }
    }

    /**
     * Add datalist filters
     *
     * @param Object $fromform The form result
     *
     * @return Bool
     */
//    protected abstract function add_datalist_filters();
    protected function add_datalist_filters() {

        if (is_null($this->filterdata)) {
            return false;
        }

//        if (!empty($this->filterdata->formitem)) {
//            $this->datalist->where('fieldname', '== ' . $this->filterdata->formitem);
//        }

        return true;
    }

    /**
     * Get the datalist sort string
     *
     * The sort information returned by the Moodle table class is
     * the sql for the ORDER BY clause 'fieldname sortorder[, fieldname sortorder]'.
     * The datalist sort parameters: sort(array(fieldname[, fieldname]), sortorder).
     *
     * @return Array The sort field and order
     */
    protected function get_datalist_sort() {
        $sort = array();
        $fields = array();
        $order = '';

        if (is_null($this->sort)) {
            return $sort;
        }

        $_sort = explode(', ', $this->sort);

        if (!empty($_sort) && !empty($_sort[0])) {
            $sort = explode(' ', $_sort[0]);
            $order = $sort[1];

            foreach ($_sort as $field) {
                $fields[] = explode(' ', $field)[0];
            }

            $sort = array($fields, $order);
        }

        return $sort;
    }

    /**
     * Datalist getter
     */
    protected function get_datalist() {
        return $this->datalist;
    }

    /**
     * Data getter
     */
    protected function get_data() {
        return $this->data;
    }

    /**
     * Data setter
     */
    protected function set_data($data) {
        $this->$data = $data;
    }

    /**
     * Data getter
     */
    protected function get_sql() {
        return $this->sql;
    }

    /**
     * Data setter
     */
    protected function set_sql($sql) {
        $this->sql = $sql;
    }

    /**
     * Data getter
     */
    protected function get_filterdata() {
        return $this->filterdata;
    }

    /**
     * Data setter
     */
    protected function set_filterdata($filterdata) {
        $this->filterdata = $filterdata;
    }

    /**
     * Magic property method
     *
     * Attempts to call a set_$key method if one exists otherwise falls back
     * to simply set the property
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value) {
        if (method_exists($this, 'set_' . $key)) {
            $this->{'set_' . $key}($value);
        }
        $this->properties->{$key} = $value;
    }

    /**
     * Magic get method
     *
     * Attempts to call a get_$key method to return the property and ralls over
     * to return the raw property
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key) {
        if (method_exists($this, 'get_' . $key)) {
            return $this->{'get_' . $key}();
        }

        return $this->properties->{$key};
    }

    /**
     * Stupid PHP needs an isset magic method if you use the get magic method and
     * still want empty calls to work.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key) {
        if (method_exists($this, 'get_' . $key)) {
            $val = $this->{'get_' . $key}();

            return !empty($val);
        }

        return !empty($this->properties->{$key});
    }

    /**
     * Read the JSON encoded fixture and create a PHP object
     *
     * @param string $fname The file name as name or filepath
     *
     * @return array $result The parsed JSON object as an associated array
     */
    protected function get_fixture($fname) {
        $result = array();

        // If the fname contains '/' we expect a full path
        if (strpos($fname, '/') === false) {
            $f = file_get_contents(dirname(dirname(__FILE__)) . '/fixtures/' . $fname . '.json');
        } else {
            $f = file_get_contents($fname);
        }
        $result = json_decode($f, true);

        return $result;
    }
}
