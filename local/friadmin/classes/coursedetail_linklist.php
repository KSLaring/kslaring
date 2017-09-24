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
     * @param           $courseid
     *
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Create the linklist
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments, exception, clean code...
     *
     * @updateDate      19/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add button for 'Manual enrolments'
     */
    protected function create_linklist($courseid) {
        global $DB;

        /* Variables    */
        $strback       = null;
        $urlback       = null;
        $strgo         = null;
        $urlgo         = null;
        $strsettings   = null;
        $urlsettings   = null;
        $strcompletion = null;
        $urlcompletion = null;
        $strstatistics = null;
        $urlstatistics = null;
        $strusers      = null;
        $urlusers      = null;
        $strconfirmed  = null;
        $urlconfirmed  = null;
        $strwaitlist   = null;
        $urlwaitlist   = null;
        $strparticipantlist = null;
        $urlparticipantlist = null;
        $stremail      = null;
        $urlemail      = null;
        $strmanual     = null;
        $urlmanual     = null;
        $strduplicate  = null;
        $urlduplicate  = null;
        $list1         = null;
        $list2         = null;
        $list3         = null;
        $list4         = null;

        try {
            // Get the waitinglist instance id.
            $waitlistinstid = $DB->get_field('enrol', 'id', array('courseid' => $courseid, 'enrol' => 'waitinglist'));
            /* Set up variables */
            $strback               = get_string('coursedetail_back', 'local_friadmin');
            $strgo                 = get_string('coursedetail_go', 'local_friadmin');
            $strsettings           = get_string('coursedetail_settings', 'local_friadmin');
            $strcompletion         = get_string('coursedetail_completion', 'local_friadmin');
            $strstatistics         = get_string('coursedetail_statistics', 'local_friadmin');
            $strenrollment         = get_string('coursedetail_enrollment', 'local_friadmin');
            $strconfirmed          = get_string('coursedetail_confirmed', 'local_friadmin');
            $strwaitlist           = get_string('coursedetail_waitlist', 'local_friadmin');
            $strparticipantlist    = get_string('coursedetail_participantlist', 'local_friadmin');
            $stremail              = get_string('coursedetail_email', 'local_friadmin');
            $strmanual             = get_string('coursedetail_manual', 'local_friadmin');
            $strduplicate          = get_string('coursedetail_duplicatecourse', 'local_friadmin');

            /* Set up url       */
            $urlback               = new moodle_url('/local/friadmin/courselist.php');
            $urlgo                 = new moodle_url('/course/view.php', array('id' => $courseid));
            $urlsettings           = new moodle_url('/course/edit.php', array('id' => $courseid));
            $urlcompletion         = new moodle_url('/report/completion/index.php', array('course' => $courseid));
            $urlstatistics         = new moodle_url('/report/overviewstats/index.php', array('course' => $courseid));
            $urlenrollment         = new moodle_url('/enrol/instances.php', array('id' => $courseid));
            $urlparticipantlist    = new moodle_url('/local/participants/participants.php', array('id' => $courseid));
            $urlconfirmed          = new moodle_url('/enrol/waitinglist/manageconfirmed.php', array('id' => $courseid));
            $urlwaitlist           = new moodle_url('/enrol/waitinglist/managequeue.php', array('id' => $courseid));
            $urlemail              = '#';
            $urlmanual             = "#";
            if ($waitlistinstid) {
                $urlmanual = new moodle_url('/enrol/waitinglist/managemanual.php', array('id' => $waitlistinstid,
                    'co' => $courseid));
            } else {
                $urlmanual = new moodle_url('/enrol/users.php', array('id' => $courseid));
            }
            $urlduplicate          = new moodle_url('/local/friadmin/duplicatecourse.php', array('id' => $courseid));

            // Check if the course has completion criteria set.
            list ($disabledcompletion, $urlcompletion) = $this->check_completioncriteria($courseid, $urlcompletion);

            // Check if there are confirmed users.
            list ($disabledconfirmed, $urlconfirmed) = $this->check_confirmedusers($courseid, $urlconfirmed);

            // Check if there are users in the course waitlist.
            list ($disabledwaitlist, $urlwaitlist) = $this->check_usersinwaitlist($courseid, $urlwaitlist);

            // Check if there is a forum with forcesubscribe activated.
            list ($disabledemail, $urlemail) = $this->check_forcesubscribeforum($courseid, $urlemail);

            /* Set up row 1 */
            $list1 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn" href="' . $urlback . '">' . $strback . '</a></li>
                      </ul>';

            /* Set up row 2 */
            $list2 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn" href="' . $urlgo . '">' . $strgo . '</a></li>
                        <li><a class="btn" href="' . $urlsettings . '">' . $strsettings . '</a></li>
                        <li><a class="btn' . $disabledcompletion . '" href="' . $urlcompletion .
                        '">' . $strcompletion . '</a></li>
                        <li><a class="btn" href="' . $urlstatistics . '">' . $strstatistics .
                        '</a></li>
                        <li><a class="btn" href="' . $urlenrollment . '">' . $strenrollment .
                        '</a></li>
                        <li><a class="btn" href="' . $urlmanual . '">' . $strmanual . '</a></li>
                        <li><a class="btn" href="' . $urlduplicate . '">' . $strduplicate . '</a></li>
                      </ul>';

            /* Set up row 3 */
            $list3 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn" href="' . $urlparticipantlist . '">' . $strparticipantlist . '</a></li>
                        <li><a class="btn' . $disabledconfirmed . '" href="' . $urlconfirmed .
                        '">' . $strconfirmed . '</a></li>
                        <li><a class="btn' . $disabledwaitlist . '" href="' . $urlwaitlist . '">' .
                        $strwaitlist . '</a></li>
                      </ul>';

            /* Set up row 4 */
            $list4 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn' . $disabledemail . '" href="' . $urlemail . '">' .
                        $stremail . '</a></li>
                     </ul>';

            $this->data->content = $list1 . $list2 . $list3 . $list4;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//create_linklist

    /**
     * @param  int          $courseid   The course id
     * @param  moodle_url   $url        The url
     *
     * @return              array
     * @throws              Exception
     *
     * @creationDate
     * @author              Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Check if the course has completion criteria set
     * to avoid errors on the report page
     *
     * @updateDate          24/06/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Add exception, clean code ...
     */
    protected function check_completioncriteria($courseid, $url) {
        /* Variables    */
        global $CFG;
        $disabled   = '';
        $course     = null;
        $completion = null;

        try {
            /* Include references   */
            require_once("{$CFG->libdir}/completionlib.php");
            require_once("{$CFG->libdir}/datalib.php");

            // Get criteria for course.
            $course     = get_course($courseid);
            $completion = new completion_info($course);

            if (!$completion->is_enabled() || !$completion->has_criteria()) {
                $disabled = ' disabled';
                $url = '#'; // Remove url
            }//if_completion

            return array($disabled, $url);
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_completioncriteria

    /**
     * Check if there are confirmed users,
     * set the confirmed button to disabled if not.
     *
     * @param Int    $courseid
     * @param String $url
     *
     * @return array
     * @throws Exception
     */
    protected function check_confirmedusers($courseid, $url) {
        $disabled = '';
        $confirmedman = \enrol_waitinglist\entrymanager::get_by_course($courseid);

        /**
         * @updateDate  17/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Check if the result is null or not
         */
        if (!is_null($confirmedman)) {
            if ($confirmedman->get_confirmed_listtotal() == 0) {
                $disabled = ' disabled';
                $url = '#';
            }
        } else {
            $disabled = ' disabled';
            $url = '#';
        }//if_confirmedman

        return array($disabled, $url);
    }

    /**
     * @param           $courseid
     * @param           $url
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Check if there are users in the course waitlist,
     * set the waitlist button to disabled if not.
     */
    protected function check_usersinwaitlist($courseid, $url) {
        /* Variables    */
        $disabled = '';
        $queueman = null;

        try {
            $queueman = \enrol_waitinglist\queuemanager::get_by_course($courseid);

            /**
             * @updateDate  17/06/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Check if the result is null or not
             */
            if (!is_null($queueman)) {
                if ($queueman->get_listtotal() == 0) {
                    $disabled = ' disabled';
                    $url = '#';
                }
            } else {
                $disabled = ' disabled';
                $url = '#';
            }//if_queueman

            return array($disabled, $url);
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_usersinwaitlist

    /**
     * @param           $courseid
     * @param           $url
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Check if there is a forum with forcesubscribe activated,
     * set the link url if there is a forum or
     * set the waitlist button to disabled if not.
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbV)
     *
     * Description
     * Add exception,clean code...
     */
    protected function check_forcesubscribeforum($courseid, $url) {
        /* Variables    */
        global $DB;
        $disabled = ' disabled';
        $sql = null;
        $forums = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseid'] = $courseid;
            $params['forum'] = 'forum';
            $params['force'] = 1;

            // Select all forums in the course with the given id
            // that have the forcesubscribe option activated
            $sql = " SELECT   f.id,
                              f.name,
                              f.forcesubscribe
                     FROM     {forum}           f
                        JOIN  {course_modules}  cm  ON f.id = cm.instance
                        JOIN  {modules}         m   ON m.id = cm.module
                     WHERE    m.name            = :forum
                        AND   f.forcesubscribe  = :force
                        AND   cm.course         = :courseid
                   ";

            /* Execute  */
            if ($forums = $DB->get_records_sql($sql, $params)) {
                // Continue with the only/first forum found.
                $forum = array_shift($forums);
                if (!empty($forum)) {
                    $disabled = '';
                    $url = new moodle_url('/mod/forum/post.php?forum=' . $forum->id);
                }//if_not_empty
            }//if_forums

            return array($disabled, $url);
        } catch (Exception $ex) {
            throw $ex;
        }//trY_catch
    }//check_forcesubscribeforum
}
