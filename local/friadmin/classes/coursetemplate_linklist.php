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
 * Class containing data for the local_friadmin coursetemplate linklist area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_linklist extends local_friadmin_widget implements renderable {

    /**
     * Construct the coursetemplate linklist renderable.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Create the linklist
     */
    public function create_linklist($courseid) {
        $str_another = get_string('coursetemplate_another', 'local_friadmin');
        $str_settings = get_string('coursetemplate_settings', 'local_friadmin');
        $str_go = get_string('coursetemplate_go', 'local_friadmin');

        $url_another = new moodle_url('/local/friadmin/coursetemplate.php');
        $url_settings = new moodle_url('/course/edit.php?id=' . $courseid);
        $url_go = new moodle_url('/course/view.php?id=' . $courseid);

        $list1 = '<ul class="unlist buttons-linklist">
            <li><a class="btn" href="' . $url_another . '">' . $str_another . '</a></li>
            <li><a class="btn" href="' . $url_go . '">' . $str_go . '</a></li>
            <li><a class="btn" href="' . $url_settings . '">' . $str_settings . '</a></li>
        </ul>';

        $this->data->content = $list1;
    }
}
