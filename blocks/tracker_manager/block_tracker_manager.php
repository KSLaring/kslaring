<?php

/**
 * Tracker Manager Block - Main Page
 *
 * @package         block
 * @subpackage      tracker_manager
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/04/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

class block_tracker_manager extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_tracker_manager');
    }//init

    function has_config() {
        return false;
    }//has_config

    public function get_aria_role() {
        return 'navigation';
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $USER, $CFG;

        if (isloggedin()) {
            /* External References  */
            $this->page->requires->js(new moodle_url('/blocks/tracker_manager/js/block_tracker_manager.js'));
            require_once($CFG->dirroot . '/report/manager/tracker/trackerlib.php');

            /* Add title        */
            $this->title = get_string('pluginname', 'block_tracker_manager');

            if ($this->content !== NULL) {
                return $this->content;
            }

            /* Add the content to the block */
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';

            /* Get Tracker User */
            $trackerUser = TrackerManager::get_user_tracker($USER->id);

            /* Outcomes Courses     */
            $this->content->text .= TrackerManager::print_outcome_tracker($trackerUser->competence);

            /* Individual Courses   */
            $this->content->text .= TrackerManager::print_individual_tracker($trackerUser->completed,$trackerUser->not_completed,$trackerUser->inWaitList);
        }


        return $this->content;
    }//get_content
}//class block_tracker