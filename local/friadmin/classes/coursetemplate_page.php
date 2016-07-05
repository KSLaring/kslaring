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

//namespace local_friadmin;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the local_friadmin course_template page
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_page extends local_friadmin_widget implements renderable {

    /**
     * Construct the coursetemplate_page renderable.
     *
     * @param int $type The course type
     */
    public function __construct($type) {
        // Create the data object and set the first values
        parent::__construct();

        $this->data->url = new moodle_url('/local/friadmin/coursetemplate.php');

        $this->data->title = '';
        $this->data->subtitle = '';
        $this->data->errormissingcat = '';

        if ($type == TEMPLATE_TYPE_EVENT) {
            $this->data->title = get_string('eventtemplate_title', 'local_friadmin');
            $this->data->subtitle = get_string('eventtemplate_subtitle', 'local_friadmin');
        } else if ($type == TEMPLATE_TYPE_NETCOURSE) {
            $this->data->title = get_string('netcoursetemplate_title', 'local_friadmin');
            $this->data->subtitle = get_string('netcoursetemplate_subtitle', 'local_friadmin');
        }
    }
}
