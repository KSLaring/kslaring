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
 * Class containing data for the mod_registerattendance bulkregister page
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_bulkregister_page extends mod_registerattendance_widget implements renderable {
    /**
     * Construct the bulkregister renderable.
     *
     * @param object $cm The course module
     */
    public function __construct($cm) {
        // Create the data object and set the first values
        parent::__construct();

        $this->data->url = new moodle_url('/mod/registerattendance/bulkregister.php', array('id' => $cm->id));
        $this->data->title = get_string('bulkregister_title', 'mod_registerattendance');
    }
}
