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

defined('MOODLE_INTERNAL') || die;

/**
 * Class containing data for the local_friadmin base page class
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class local_friadmin_widget implements renderable {

    // The page data.
    protected $data;

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct() {
        // Create the data object and set the first values.
        $data = new stdClass();

        $this->data = $data;
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
    protected function set_data($key, $value) {
        $this->data->$key = $value;
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
        } else {
            throwException('Property does not exisit.');
        }
        //return $this->properties->{$key};
        return null;
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
        } else {
            throwException('Property does not exisit.');
        }
        //return !empty($this->properties->{$key});
        return null;
    }

    /**
     * Read the JSON encoded fixture and create a PHP object
     *
     * @param string $fname The file name
     *
     * @return array $result The parsed JSON object
     */
    public function get_fixture($fname) {
        $result = array();

        $f = file_get_contents(dirname(dirname(__FILE__)) . '/fixtures/' . $fname . '.json');
        $result = json_decode($f, true);

        return $result;
    }
}
