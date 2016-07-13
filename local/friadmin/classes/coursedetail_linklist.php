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
     * @param           $courseId
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
     */
    protected function create_linklist($courseId) {
        /* Variables    */
        $str_back       = null;
        $url_back       = null;
        $str_go         = null;
        $url_go         = null;
        $str_settings   = null;
        $url_settings   = null;
        $str_completion = null;
        $url_completion = null;
        $str_statistics = null;
        $url_statistics = null;
        $str_users      = null;
        $url_users      = null;
        $str_confirmed  = null;
        $url_confirmed  = null;
        $str_waitlist   = null;
        $url_waitlist   = null;
        $str_participantlist = null;
        $url_participantlist = null;
        $str_email = null;
        $url_email = null;
        $list1 = null;
        $list2 = null;
        $list3 = null;
        $list4 = null;

        try {
            /* Set up variables */
            $str_back               = get_string('coursedetail_back', 'local_friadmin');
            $str_go                 = get_string('coursedetail_go', 'local_friadmin');
            $str_settings           = get_string('coursedetail_settings', 'local_friadmin');
            $str_completion         = get_string('coursedetail_completion', 'local_friadmin');
            $str_statistics         = get_string('coursedetail_statistics', 'local_friadmin');
            $str_enrollment         = get_string('coursedetail_enrollment', 'local_friadmin');
            $str_users              = get_string('coursedetail_users', 'local_friadmin');
            $str_confirmed          = get_string('coursedetail_confirmed', 'local_friadmin');
            $str_waitlist           = get_string('coursedetail_waitlist', 'local_friadmin');
            $str_participantlist    = get_string('coursedetail_participantlist', 'local_friadmin');
            $str_email              = get_string('coursedetail_email', 'local_friadmin');

            /* Set up url       */
            $url_back               = new moodle_url('/local/friadmin/courselist.php');
            $url_go                 = new moodle_url('/course/view.php', array('id' => $courseId));
            $url_settings           = new moodle_url('/course/edit.php', array('id' => $courseId));
            $url_completion         = new moodle_url('/report/completion/index.php',array('course' => $courseId));
            $url_statistics         = new moodle_url('/report/overviewstats/index.php',array('course' => $courseId));
            $url_enrollment         = new moodle_url('/enrol/instances.php',array('id' => $courseId));
            $url_users              = new moodle_url('/local/participants/participants.php', array('id' => $courseId));
            $url_confirmed          = new moodle_url('/enrol/waitinglist/manageconfirmed.php', array('id' => $courseId));
            $url_waitlist           = new moodle_url('/enrol/waitinglist/managequeue.php', array('id' => $courseId));
            $url_participantlist    = new moodle_url('/grade/export/xls/index.php?',array('id' => $courseId));
            $url_email = '#';

            // Check if the course has completion criteria set
            list ($disabled_completion, $url_completion) = $this->check_completioncriteria($courseId, $url_completion);

            // Check if there are confirmed users
            list ($disabled_confirmed, $url_confirmed) = $this->check_confirmedusers($courseId, $url_confirmed);

            // Check if there are users in the course waitlist
            list ($disabled_waitlist, $url_waitlist) = $this->check_usersinwaitlist($courseId, $url_waitlist);

            // Check if there is a forum with forcesubscribe activated
            list ($disabled_email, $url_email) = $this->check_forcesubscribeforum($courseId, $url_email);

            /* Set up row 1 */
            $list1 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn" href="' . $url_back . '">' . $str_back . '</a></li>
                      </ul>';

            /* Set up row 2 */
            $list2 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn" href="' . $url_go . '">' . $str_go . '</a></li>
                        <li><a class="btn" href="' . $url_settings . '">' . $str_settings . '</a></li>
                        <li><a class="btn' . $disabled_completion . '" href="' . $url_completion .
                        '">' . $str_completion . '</a></li>
                        <li><a class="btn" href="' . $url_statistics . '">' . $str_statistics .
                        '</a></li>
                        <li><a class="btn" href="' . $url_enrollment . '">' . $str_enrollment .
                        '</a></li>
                      </ul>';

            /* Set up row 3 */
            $list3 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn" href="' . $url_users . '">' . $str_users . '</a></li>
                        <li><a class="btn' . $disabled_confirmed . '" href="' . $url_confirmed .
                        '">' . $str_confirmed . '</a></li>
                        <li><a class="btn' . $disabled_waitlist . '" href="' . $url_waitlist . '">' .
                        $str_waitlist . '</a></li>
                        <li><a class="btn" href="' . $url_participantlist . '">' . $str_participantlist .
                        '</a></li>
                      </ul>';

            /* Set up row 4 */
            $list4 = '<ul class="unlist buttons-linklist">
                        <li><a class="btn' . $disabled_email . '" href="' . $url_email . '">' .
                        $str_email . '</a></li>
                     </ul>';

            $this->data->content = $list1 . $list2 . $list3 . $list4;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//create_linklist

    /**
     * @param  int          $courseId   The course id
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
    protected function check_completioncriteria($courseId, $url) {
        /* Variables    */
        global $CFG;
        $disabled   = '';
        $course     = null;
        $completion = null;

        try {
            /* Include references   */
            require_once("{$CFG->libdir}/completionlib.php");
            require_once("{$CFG->libdir}/datalib.php");

            // Get criteria for course
            $course     = get_course($courseId);
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
     * @param           $courseId
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
    protected function check_usersinwaitlist($courseId, $url) {
        /* Variables    */
        $disabled = '';
        $queueman = null;

        try {
            $queueman = \enrol_waitinglist\queuemanager::get_by_course($courseId);

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
     * @param           $courseId
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
    protected function check_forcesubscribeforum($courseId, $url) {
        /* Variables    */
        global $DB;
        $disabled = ' disabled';
        $sql = null;
        $forums = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseid'] = $courseId;
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
                // Continue with the only/first forum found
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
