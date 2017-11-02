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

namespace local_course_search\task;

use core_tag_tag;

/**
 * A scheduled task for the course search.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_course_search');
    }

    /**
     * Run local course search cron.
     */
    public function execute_o() {
        global $CFG;
        require_once($CFG->dirroot . '/local/course_search/lib.php');
        local_course_search_cron();
    }

    /**
     * Run local course search cron.
     */
    public function execute() {
        $this->course_search_cron();
    }

    /**
     * Function to be run periodically according to the scheduled task.
     *
     * Save a lowercase text of the course infromation for a fulltext search.
     */
    protected function course_search_cron() {
        global $DB;

        mtrace('Indexing courses ...');

        mtrace('Purging course data cache ...');
        $cache = \cache::make('local_course_search', 'courses');
        $cache->purge(); // Purge the cache - delete all entries.

        $indexrecord = null;
        $sql = 'SELECT
        	id,
        	fullname,
        	shortname,
        	format,
        	summary,
        	startdate,
        	summaryformat
        FROM
        	{course}
        WHERE
        	category != 0
        ORDER BY 
            fullname ASC';

        mtrace('Starting to index courses ...');

        if (!$result = $DB->get_records_sql($sql)) {
            mtrace('No courses to index.');
            return null;
        };

        $courseids = array_keys($result);

        $sortcounter = 0;
        $courses = new \local_course_search\output\courses();
        foreach ($result as $row) {
            $alltext = $this->course_search_alltext($row);
            $indexrecord = (object)array(
                'course' => $row->id,
                'fullname' => $row->fullname,
                'alltext' => $alltext,
                'json' => $courses->get_course_json_for_cron($row, $alltext, $sortcounter),
                'timemodified' => time(),
            );

            $sortcounter++;

            if ($id = $DB->get_field('local_course_search', 'id', array('course' => $row->id))) {
                $indexrecord->id = $id;
                $DB->update_record('local_course_search', $indexrecord);
            } else {
                $DB->insert_record('local_course_search', $indexrecord);
            }
        }

        // Delete old entries from not exisitng courses.
        list($insql, $inparams) = $DB->get_in_or_equal($courseids);
        $sql =  "Delete from {local_course_search}
                 WHERE course NOT " . $insql . "
                    OR json IS NULL";

        $DB->execute($sql, $inparams);
    }

    /**
     * Create a lowercase text string with the course name, summary an tags as the source for a fulltext search.
     *
     * @param object $row The course data from the database
     *
     * @return string
     */
    protected function course_search_alltext($row) {
        $text = \core_text::strtolower(content_to_text($row->summary, $row->summaryformat));

        $name = \core_text::strtolower(content_to_text($row->fullname, false));
        if (strpos($text, $name) === false) {
            $text .= ' ' . $name;
        }

        $shortname = \core_text::strtolower(content_to_text($row->shortname, false));
        if (strpos($text, $shortname) === false) {
            $text .= ' ' . $shortname;
        }

        $tags = \core_text::strtolower(
            join(',', \core_tag_tag::get_item_tags_array('core', 'course', $row->id)));

        if ($tags !== '') {
            $text .= ',' . $tags;
        }

        // Remove all carriage returns.
        $text = str_replace(array("\n", "\r"), ' ', $text);

        return $text;
    }
}
