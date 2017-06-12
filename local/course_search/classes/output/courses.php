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

namespace local_course_search\output;

use stdClass;
use context_course;
use stored_file;
use moodle_url;
use local_friadmin_helper;

/**
 * Handle the course collection.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Hold the course data.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @property boolean $viewfullnames Whether to override fullname()
 */
class courses implements \renderable, \templatable {

    /* @var array $coursecollection All courses available for the course search */
    protected $coursecollection = array();

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \local_course_search_renderer $renderer The renderer to be used for the search page
     *
     * @return object Data ready for use in a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $USER;

        $searchtags = null;

        if (isloggedin()) {
            $searchtags = \local_course_search\util::get_searchtags($USER->id);
        } else {
            $searchtags = \local_course_search\util::get_searchtags(0);
        }

        $data = (object)array(
            'userid' => is_object($USER) ? $USER->id : 0,
            'pagetitle' => get_string('pagetitle', 'local_course_search'),
            'searchtitle' => get_string('searchtitle', 'local_course_search'),
            'searchbtntext' => get_string('searchbtntext', 'local_course_search'),
            'toggleselection' => get_string('toggleselection', 'local_course_search'),
            'searchtags' => $searchtags,
            //'courses' => $this->get_coursecollection(),
            'courses' => null,
        );

        return $data;
    }

    public function export_course_data() {
        return $this->get_coursecollection();
    }

    /**
     * Get the courses to show in the result area.
     *
     * @return array The cours collection
     */
    protected function get_coursecollection() {
        // Load the fixture for development.
        if (false) {
            $json = file_get_contents(__DIR__ . '/../../fixtures/courses.json');
            $context = json_decode($json);

            $this->coursecollection = $context->courses;
        } else {
            $courses = get_courses('all', 'c.fullname ASC');
            $preselectedcourseids = \local_course_search\util::get_user_tagged_courseids();
            $templateids = $this->get_template_courses_ids();
            $sortcounter = 0;
            foreach ($courses as $course) {
                // Exclude the site and the template courses.
                if ($course->id == 1 || in_array($course->id, $templateids)) {
                    continue;
                }

                // Exclude the courses that have none of the user's preselected tags.
                if (!in_array($course->id, $preselectedcourseids)) {
                    continue;
                }
                $this->coursecollection[] = $this->get_one_course($course, $sortcounter);
                $sortcounter++;
            }
        }

        return $this->coursecollection;
    }

    /*
     * Get the data for one course.
     */
    protected function get_one_course($course, $sortcounter) {
        global $CFG, $DB;
        $context = context_course::instance($course->id);
        $date = '';
        $timestamp = 0;
        $availseats = '';
        $availnumber = '';
        $deadline = '';
        $location = '';
        $municipality = '';

        require_once($CFG->dirroot . '/local/course_page/locallib.php');
        $formatoptions = \course_page::get_format_fields($course->id);
        $formatoptions = \course_page::get_available_seats_format_option($course->id, $formatoptions);

        if (!empty($formatoptions['homepage'])) {
            $url = new \moodle_url('/local/course_page/home_page.php', array('id' => $course->id));
            $availseats = $formatoptions['enrolledusers']->value;
            if ($availseats === 'hide') {
                $availseats = '';
            } else {
                if (\core_text::strpos($availseats, ' ') !== false) {
                    $availnumber = \core_text::substr($availseats, 0, \core_text::strpos($availseats, ' '));
                }
            }
            $deadline = \course_page::deadline_course($course->id);
            if (!empty($formatoptions['course_location'])) {
                $locationid = (int)$formatoptions['course_location']->value;
                $location = \course_page::get_location_name($locationid);

                // Get the location municipality.
                $params = array();
                $params['location'] = $locationid;

                // SQL Instruction.
                $sql = " SELECT
                                      cl.id,
                                      levelone.name as 'muni'
                         FROM		  {course_locations}		cl
                            JOIN	  {report_gen_companydata}	levelone  ON  levelone.id 	= cl.levelone
                         WHERE		  cl.id = :location ";

                // Execute.
                if ($rdo = $DB->get_record_sql($sql, $params)) {
                    $municipality = $rdo->muni;
                }
            }
        } else {
            $url = new \moodle_url('/course/view.php', array('id' => $course->id));
        }

        $courseextended = $course;
        if ($courseextended instanceof stdClass) {
            require_once($CFG->libdir . '/coursecatlib.php');
            $courseextended = new \course_in_list($course);
        }

        $summary = '';
        if ($courseextended->has_summary()) {
            require_once($CFG->dirroot . '/course/renderer.php');
            $chelper = new \coursecat_helper();

            $options = (object)array(
                "noclean" => true,
                "para" => false,
                "overflowdiv" => true
            );
            $summary = $chelper->get_course_formatted_summary($courseextended, $options);
        }

        $img = '';

        $timestamp = $course->startdate;
        $date = userdate($timestamp, '%d.%m.%Y');
        $sortdate = userdate($timestamp, '%Y%m%d');

        if (strlen($date) < 10) {
            $date = '0' . $date;
        }
        if (strlen($sortdate) < 8) {
            $sortdate = substr($sortdate, 0, -1) . '0' . substr($sortdate, -1);
        }

        // Use the first image saved in the course settings.
        /* @var stored_file $file A Moodle stored file object */
        foreach ($courseextended->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if ($isimage) {
                $path = '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename();
                $imgurl = moodle_url::make_file_url('/pluginfile.php', $path);
                $img = $imgurl->out();

                break;
            }
        }

        $coursetags = \core_tag_tag::get_item_tags_array('core', 'course', $course->id);
        $tags = array();
        $tagcollection = array();

        foreach ($coursetags as $key => $value) {
            $tagcollid = \core_tag_area::get_collection('core', 'course');
            $metagroupname = '';

            if ($relatedgrouptags = \local_tag\collection::get_tag_related_group_tags($tagcollid, $key,
                \local_tag\tag::get_meta_group_prefix())
            ) {
                $grouptag = array_shift($relatedgrouptags);
                $metagroupname = \local_tag\tag::get_meta_tag_stripped_name($grouptag->name,
                    \local_tag\tag::get_meta_group_prefix());
            }

            if (!empty($metagroupname)) {
                $tags[] = (object)array(
                    'id' => $key,
                    'name' => $value,
                    'group' => $metagroupname
                );
                $tagcollection[] = strtolower($metagroupname) . '-' . $value;
            }
        }

        $coursename = format_string($course->fullname, true, array('context' => $context));
        $coursename = str_replace('"', '&quot;', $coursename);

        $alltext = $DB->get_field('local_course_search', 'alltext', array('course' => $course->id));

        $result = (object)array(
            "id" => $course->id,
            "sortorder" => $sortcounter,
            "name" => $coursename,
            "img" => $img,
            "summary" => $summary,
            "link" => $url->out(),
            "date" => $date,
            "sortdate" => $sortdate,
            "availseats" => $availseats,
            "availnumber" => $availnumber,
            "deadline" => $deadline,
            "municipality" => $municipality,
            "location" => $location,
            "tags" => $tags,
            "tagcollection" => $tagcollection,
            "alltext" => $alltext
        );

        return $result;
    }

    /**
     * Get the ids of the template courses.
     *
     * @return array The ids of the template courses
     */
    protected function get_template_courses_ids() {
        $result = array();

        $usercategories = local_friadmin_helper::get_usercategories_data();

        $eventids = array_keys($usercategories['eventtemplates']);
        $netcourseids = array_keys($usercategories['netcoursetemplates']);

        $result = array_merge($eventids, $netcourseids);

        return $result;
    }
}
