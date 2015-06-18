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
        global $CFG, $DB;

        $str_back = get_string('coursedetail_back', 'local_friadmin');
        $str_go = get_string('coursedetail_go', 'local_friadmin');
        $str_settings = get_string('coursedetail_settings', 'local_friadmin');
        $str_completion = get_string('coursedetail_completion', 'local_friadmin');
        $str_statistics = get_string('coursedetail_statistics', 'local_friadmin');
        $str_users = get_string('coursedetail_users', 'local_friadmin');
        $str_confirmed = get_string('coursedetail_confirmed', 'local_friadmin');
        $str_waitlist = get_string('coursedetail_waitlist', 'local_friadmin');
        $str_participantlist = get_string('coursedetail_participantlist', 'local_friadmin');
        $str_duplicate = get_string('coursedetail_duplicate', 'local_friadmin');
        $str_email = get_string('coursedetail_email', 'local_friadmin');

        $url_back = new moodle_url('/local/friadmin/courselist.php');
        $url_go = new moodle_url('/course/view.php?id=' . $courseid);
        $url_settings = new moodle_url('/course/edit.php?id=' . $courseid);
        $url_completion = new moodle_url('/report/completion/index.php?course=' . $courseid);
        $url_statistics = new moodle_url('/report/overviewstats/index.php?course=' . $courseid);
        $url_users = new moodle_url('/enrol/users.php?id=' . $courseid);
        $url_confirmed = new moodle_url('/enrol/waitinglist/manageconfirmed.php?id=' . $courseid);
        $url_waitlist = new moodle_url('/enrol/waitinglist/managequeue.php?id=' . $courseid);
        $url_participantlist = new moodle_url('/grade/export/xls/index.php?id=' . $courseid);
        $url_duplicate = '#';
        $url_email = '#';

        // Check if the course has completion criteria set
        // to avoid errors on the report page
        $disabled_completion = '';
        require_once("{$CFG->libdir}/completionlib.php");
        require_once("{$CFG->libdir}/datalib.php");

        // Get criteria for course
        $course = get_course($courseid);
        $completion = new completion_info($course);

        if (!$completion->has_criteria()) {
            $disabled_completion = ' disabled';
            $url_completion = '#';
        }

        // Check if there are confirmed users,
        // set the confirmed button to disabled if not.
        $disabled_confirmed = '';
        $confirmedman = \enrol_waitinglist\entrymanager::get_by_course($courseid);
        /**
         * @updateDate  17/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Check if the result is null or not
         */
        if ($confirmedman) {
            if ($confirmedman->get_confirmed_listtotal() == 0) {
                $disabled_confirmed = ' disabled';
                $url_confirmed = '#';
            }
        } else {
            $disabled_confirmed = ' disabled';
            $url_confirmed = '#';
        }//if_confirmedman


        // Check if there are users in the course waitlist,
        // set the waitlist button to disabled if not.
        $disabled_waitlist = '';
        $queueman = \enrol_waitinglist\queuemanager::get_by_course($courseid);
        /**
         * @updateDate  17/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Check if the result is null or not
         */
        if ($queueman) {
            if ($queueman->get_listtotal() == 0) {
                $disabled_waitlist = ' disabled';
                $url_waitlist = '#';
            }
        } else {
            $disabled_waitlist = ' disabled';
            $url_waitlist = '#';
        }//if_queueman

        $list1 = '<ul class="unlist buttons-linklist">
            <li><a class="btn" href="' . $url_back . '">' . $str_back . '</a></li>
            <li><a class="btn" href="' . $url_go . '">' . $str_go . '</a></li>
            <li><a class="btn" href="' . $url_settings . '">' . $str_settings . '</a></li>
            <li><a class="btn' . $disabled_completion . '" href="' . $url_completion . '">' .
                $str_completion . '</a></li>
            <li><a class="btn" href="' . $url_statistics . '">' . $str_statistics . '</a></li>
        </ul>';

        $list2 = '<ul class="unlist buttons-linklist">
            <li><a class="btn" href="' . $url_users . '">' . $str_users . '</a></li>
            <li><a class="btn' . $disabled_confirmed . '" href="' . $url_confirmed . '">' .
                $str_confirmed . '</a></li>
            <li><a class="btn' . $disabled_waitlist . '" href="' . $url_waitlist . '">' .
                $str_waitlist . '</a></li>
            <li><a class="btn" href="' . $url_participantlist . '">' . $str_participantlist . '</a></li>
        </ul>';

        $list3 = '<ul class="unlist buttons-linklist">
            <li><a class="btn disabled" href="' . $url_email . '">' . $str_email . '</a></li>
        </ul>';

        $this->data->content = $list1 . $list2 . $list3;
    }
}
