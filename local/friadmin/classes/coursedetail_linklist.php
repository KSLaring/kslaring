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
 * Class containing data for the local_friadmin coursedetail linklist area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursedetail_linklist extends local_friadmin_widget implements renderable {

    /**
     * Construct the coursedetail linklist renderable.
     */
    public function __construct($courseid) {
        parent::__construct();

        $this->create_linklist($courseid);
    }

    /**
     * Create the linklist
     */
    protected function create_linklist($courseid) {
        $str_back = get_string('coursedetail_back', 'local_friadmin');
        $str_users = get_string('coursedetail_users', 'local_friadmin');
        $str_waitlist = get_string('coursedetail_waitlist', 'local_friadmin');
        $str_settings = get_string('coursedetail_settings', 'local_friadmin');
        $str_go = get_string('coursedetail_go', 'local_friadmin');
        $str_duplicate = get_string('coursedetail_duplicate', 'local_friadmin');
        $str_email = get_string('coursedetail_email', 'local_friadmin');

        $url_back = new moodle_url('/local/friadmin/courselist.php');
        $url_users = new moodle_url('/enrol/users.php?id='.$courseid);
        $url_waitlist = '#';
        $url_settings = new moodle_url('/course/edit.php?id='.$courseid);
        $url_go = new moodle_url('/course/view.php?id='.$courseid);
        $url_duplicate = '#';
        $url_email = '#';

        $list = '<ul class="unlist coursedetail-linklist">
            <li><a class="btn" href="'.$url_back.'">'.$str_back.'</a></li>
            <li><a class="btn" href="'.$url_users.'">'.$str_users.'</a></li>
            <li><a class="btn disabled" href="'.$url_waitlist.'">'.$str_waitlist.'</a></li>
            <li><a class="btn" href="'.$url_settings.'">'.$str_settings.'</a></li>
            <li><a class="btn" href="'.$url_go.'">'.$str_go.'</a></li>
            <li><a class="btn disabled" href="'.$url_duplicate.'">'.$str_duplicate.'</a></li>
            <li><a class="btn disabled" href="'.$url_email.'">'.$str_email.'</a></li>
        </ul>';

        $this->data->content = $list;
    }
}
