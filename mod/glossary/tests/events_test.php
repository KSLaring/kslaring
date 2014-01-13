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

/**
 * Unit tests for lib.php
 *
 * @package    mod_glossary
 * @category   test
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for glossary events.
 *
 * @package   mod_glossary
 * @category  test
 * @copyright 2013 Rajesh Taneja <rajesh@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class glossary_event_testcase extends advanced_testcase {

    /**
     * Test comment_created event.
     */
    public function test_comment_created() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/comment/lib.php');

        $this->resetAfterTest();

        // Create a record for adding comment.
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $glossary = $this->getDataGenerator()->create_module('glossary', array('course' => $course));
        $glossarygenerator = $this->getDataGenerator()->get_plugin_generator('mod_glossary');

        $entry = $glossarygenerator->create_content($glossary);

        $context = context_module::instance($glossary->id);
        $cm = get_coursemodule_from_instance('data', $glossary->id, $course->id);
        $cmt = new stdClass();
        $cmt->component = 'mod_glossary';
        $cmt->context = $context;
        $cmt->course = $course;
        $cmt->cm = $cm;
        $cmt->area = 'glossary_entry';
        $cmt->itemid = $entry->id;
        $cmt->showcount = true;
        $comment = new comment($cmt);

        // Triggering and capturing the event.
        $sink = $this->redirectEvents();
        $comment->add('New comment');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_glossary\event\comment_created', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/mod/glossary/view.php', array('id' => $glossary->id));
        $this->assertEquals($url, $event->get_url());
    }

    /**
     * Test comment_deleted event.
     */
    public function test_comment_deleted() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/comment/lib.php');

        $this->resetAfterTest();

        // Create a record for deleting comment.
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $glossary = $this->getDataGenerator()->create_module('glossary', array('course' => $course));
        $glossarygenerator = $this->getDataGenerator()->get_plugin_generator('mod_glossary');

        $entry = $glossarygenerator->create_content($glossary);

        $context = context_module::instance($glossary->id);
        $cm = get_coursemodule_from_instance('data', $glossary->id, $course->id);
        $cmt = new stdClass();
        $cmt->component = 'mod_glossary';
        $cmt->context = $context;
        $cmt->course = $course;
        $cmt->cm = $cm;
        $cmt->area = 'glossary_entry';
        $cmt->itemid = $entry->id;
        $cmt->showcount = true;
        $comment = new comment($cmt);
        $newcomment = $comment->add('New comment 1');

        // Triggering and capturing the event.
        $sink = $this->redirectEvents();
        $comment->delete($newcomment->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_glossary\event\comment_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/mod/glossary/view.php', array('id' => $glossary->id));
        $this->assertEquals($url, $event->get_url());
    }
}
