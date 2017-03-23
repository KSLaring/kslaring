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

defined('MOODLE_INTERNAL') || die();

/**
 * Openlast class
 *
 * Redirect to the last opend activity/resource
 *
 * @package    format_netcourse
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_netcourse_openlast {

    /**
     * @var The actual page object
     */
    protected $page;

    /**
     * @var The actual course object
     */
    protected $course;

    /**
     * @var The actual user object
     */
    protected $user;

    /**
     * @var The full page url
     */
    protected $fullme;

    /**
     * @var The course modules collection
     */
    protected $modinfo;

    /**
     * @var An array with the module cmids in section 0
     */
    protected $section0modids = null;

    public function __construct($page, $course, $user, $fullme) {
        $this->course = $course;
        $this->page = $page;
        $this->user = $user;
        $this->fullme = $fullme;

        $this->modinfo = get_fast_modinfo($this->course->id);

        if (isset($this->modinfo->sections[0])) {
            $this->section0modids = $this->modinfo->sections[0];
        }
    }

    /**
     * Get the redirect url.
     *
     * @param string $request The page request
     *
     * @return mixed false (continue) | -1 (return) | Moodle redirect url
     */
    public function redirect($request = '') {
        // Check if the request is an AJAX call, don't redirect if true
        if ($this->is_ajax_call() ||
            strpos($request, 'course/completion.php') !== false
        ) {
            return -1;
        } else if (strpos($request, 'scorm') !== false &&
            strpos($request, 'view.php') === false &&
            strpos($request, 'player.php') === false
        ) {
            return -1;
        }

        // If the user never visited the course the last opened page URL is null.
        // In this case show the first activity/resource in the section 0
        if (!$this->is_editing() && strpos($request, 'course/view.php') !== false) {
            list($text, $module, $openedcmid, $url) = $this->
                get_last_opened(1);

            // First time, redirect to the course description
            if (is_null($url)) {

//                if (!isset($this->modinfo->sections[0])) {
                if (!isset($this->modinfo->sections[0]) ||
                    !isset($this->modinfo->sections[0][0])
                ) {
                    return false;
                }

                $cmid = $this->modinfo->sections[0][0];

                $url = $this->modinfo->cms[$cmid]->url;
                if (!empty($url)) {
                    $url->param('description', 1);
                    $url->param('nonav', 1);
                } else {
                    $url = new moodle_url('#');
                }
            } else if (!$this->modinfo->cms[$openedcmid]->uservisible) {
                // If the module is not visible for the user
                // then get the first module in section 1.
                if (!isset($this->modinfo->sections[1]) ||
                    !isset($this->modinfo->sections[1][0])
                ) {
                    return false;
                }

                $altcmid = $this->modinfo->sections[1][0];

                $cmsurl = $this->modinfo->cms[$altcmid]->url;
                if (empty($cmsurl)) {
                    $cmsurl = new moodle_url('#');
                }

                $url = $cmsurl;
            }

            return $url;
        }

        return false;
    }

    /*
     * If the request is an AJAX request then return
     * We can't use $page-url here because the pageurl has not been set
     *
     * @return bool
     */
    protected function is_ajax_call() {
        return (strpos($this->fullme, 'course/rest.php') !== false ||
            strpos($this->fullme, 'ajax/service') !== false);
    }

    /*
     * Check if the user is editing the page
     *
     * @return bool
     */
    public function is_editing() {
        $edit = optional_param('edit', false, PARAM_BOOL);

        return $this->page->user_is_editing() || $edit;
    }

    /*
     * Get the saved modinfo
     *
     * @return object
     */
    public function get_modinfo() {
        return $this->modinfo;
    }

    /*
     * Get the section 0 module ids
     *
     * @return object
     */
    public function get_section0modids() {
        return $this->section0modids;
    }

    /**
     * Get the last opened course activity or section
     * for the current user in the actual course.
     *
     * Respect the exclude options like module types, editing states or similar.
     *
     * If the log is checked from the course format script the last log entry
     * is the course view, the log entry before the last holds the information
     * about the last opened page. $limitno can be set to 2 to retrieve
     * the last two log entries.
     *
     * @param int $limitno The number of records to fetch
     *
     * @return mixed null | array
     */
    public function get_last_opened($limitno = 1) {
        global $DB;

        $text = null;
        $module = null;
        $cmid = null;
        $url = null;

        // Define the query with the userid and courseid
        // and module not 'course' to get the last viewed activity/resource
        $sql = "
        SELECT *
        FROM   {logstore_standard_log}
        WHERE  userid = :userid
           AND courseid = :courseid
           AND target = 'course_module'
           AND action = 'viewed'
        ";

        // Exclude all activities/resources in section 0
        // "<> IN(id1,id2)" with the module ids from section0modids
        list($insql, $inparams) = $DB->get_in_or_equal($this->section0modids,
            SQL_PARAMS_NAMED, 'param', false);
        $insql = "\nAND contextinstanceid " . $insql;

        // Set the ORDER BY SQL.
        $ordersql = "\nORDER BY id DESC";

        // Set the limit SQL.
        // Using "LIMIT :limitno" and passing $limitno in params throws an SQL error???
        $limitsql = "\nLIMIT " . $limitno;

        $sql = $sql . $insql . $ordersql . $limitsql;

        // Set the SQL parameters
        $params = array_merge($inparams,
            array('userid' => $this->user->id, 'courseid' => $this->course->id));

        // Get the records from the database
        if ($result = $DB->get_records_sql($sql, $params)) {
            $text = '';
            $rowno = $limitno - 1;

            // Get the correct key for the item defined in limit
            $akeys = array_keys($result);
            $akey = 0;
            if (!empty($akeys[$rowno])) {
                $akey = $akeys[$rowno];
            }

            // Get the item if the item with the calculated key is not empty
            // else get the first item
            if (!empty($result[$akey])) {
                $row = $result[$akey];
            } else {
                $row = reset($result);
            }

            // Create a text string from the row for debugging
            foreach ((array)$row as $key => $value) {
                if ($key === 'url') {
                    $value = str_replace('&', '&amp;', $value);
                }
                $text .= $key . ': ' . $value . "\n";
            }

            // Set the module type and create the url for the last opened page
            // Redirect to SCORM lightbox when a course is entered does not work here.
//            if (strpos($row->component, 'scorm') === false) {
//                $module = str_replace('mod_', 'mod/', $row->component);
//            } else {
//                $module = 'local/scorm_lightbox';
//            }
            $module = str_replace('mod_', 'mod/', $row->component);
            $cmid = $row->contextinstanceid;

            // Check if the course module exists
            if ($DB->record_exists('course_modules', array('id' => $cmid))) {
                $url = new moodle_url('/' . $module . '/view.php?id=' . $cmid);
            }

            // Exclude resource and folder modules to avoid the repeated automatic download trap
            if (strpos($row->component, 'mod_resource') !== false ||
                strpos($row->component, 'mod_folder') !== false
            ) {
                $url = null;
            }
        }

        // return the values as an array
        return array($text, $module, $cmid, $url);
    }
}
