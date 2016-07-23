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

//namespace mod_registerattendance;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the mod_registerattendance bulkregister selector page
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_bulkregister_selector extends mod_registerattendance_widget implements renderable {
    /** @var object The reference to the main object. */
    protected $registerattendance = null;

    /** @var object The course module object. */
    protected $cm = null;

    /** @var object The related filter data returned from the form. */
    protected $filterdata = null;

    /**
     * Construct the bulkregister page selector.
     *
     * @param string $baseurl
     * @param object $filterdata
     * @param object $cm
     * @param object $registerattendance
     *
     * @throws Exception
     */
    public function __construct($baseurl, $filterdata = null, $cm = null, $registerattendance = null) {
        parent::__construct();

        $this->registerattendance = $registerattendance;
        $this->cm = $cm;
        $this->data->baseurl = $baseurl;
        $this->filterdata = $filterdata;
    }

    /**
     * Create the Moodle user selectors with the saved data
     *
     * @return string The rendered Moodle selectors
     * @throws Exception
     */
    public function render() {
        $out = '';

        $out .= 'Selectors here ...';

        $this->data->content = $out;
    }

    /**
     * Add the user's fullname, remove firstname and lastname
     *
     * @param array $data Array with user data objects
     *
     * @return array The extended user data
     */
    protected function add_fullname($data) {
        $result = $data;

        if ($data) {
            $result = array_map(array($this, 'callback_fullname'), $data);
        }

        return $result;
    }

    /**
     * Callback for the user's fullname
     *
     * @param object $row The user object from the database
     *
     * @return object The extended user data row
     */
    protected function callback_fullname($row) {
        $row->fullname = fullname($row);

        return $row;
    }
}
