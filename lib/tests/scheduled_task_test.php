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
 * This file contains the unittests for the css optimiser in csslib.php
 *
 * @package   core
 * @category  phpunit
 * @copyright 2013 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Test class for scheduled task.
 *
 * @package core
 * @category task
 * @copyright 2013 Damyon Wiese
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scheduled_task_testcase extends advanced_testcase {

    /**
     * Test the cron scheduling method
     */
    public function test_eval_cron_field() {
        $testclass = new testable_scheduled_task();

        $this->assertEquals(20, count($testclass->eval_cron_field('*/3', 0, 59)));
        $this->assertEquals(31, count($testclass->eval_cron_field('1,*/2', 0, 59)));
        $this->assertEquals(15, count($testclass->eval_cron_field('1-10,5-15', 0, 59)));
        $this->assertEquals(13, count($testclass->eval_cron_field('1-10,5-15/2', 0, 59)));
        $this->assertEquals(3, count($testclass->eval_cron_field('1,2,3,1,2,3', 0, 59)));
        $this->assertEquals(1, count($testclass->eval_cron_field('-1,10,80', 0, 59)));
    }

    public function test_get_next_scheduled_time() {
        // Test job run at 1 am.
        $testclass = new testable_scheduled_task();

        // All fields default to '*'.
        $testclass->set_hour('1');
        $testclass->set_minute('0');
        // Next valid time should be 1am of the next day.
        $nexttime = $testclass->get_next_scheduled_time();

        $oneam = mktime(1, 0, 0);
        // Make it 1 am tomorrow if the time is after 1am.
        if ($oneam < time()) {
            $oneam += 86400;
        }

        $this->assertEquals($oneam, $nexttime, 'Next scheduled time is 1am.');

        // Now test for job run every 10 minutes.
        $testclass = new testable_scheduled_task();

        // All fields default to '*'.
        $testclass->set_minute('*/10');
        // Next valid time should be next 10 minute boundary.
        $nexttime = $testclass->get_next_scheduled_time();

        $minutes = ((intval(date('i') / 10))+1) * 10;
        $nexttenminutes = mktime(date('H'), $minutes, 0);

        $this->assertEquals($nexttenminutes, $nexttime, 'Next scheduled time is in 10 minutes.');
    }

    public function test_get_next_scheduled_task() {
        global $DB;

        $this->resetAfterTest(true);
        // Delete all existing scheduled tasks.
        $DB->delete_records('task_scheduled');
        // Add a scheduled task.

        // A task that runs once per hour.
        $record = new stdClass();
        $record->blocking = true;
        $record->minute = '0';
        $record->hour = '0';
        $record->dayofweek = '*';
        $record->day = '*';
        $record->month = '*';
        $record->component = 'test_scheduled_task';
        $record->classname = '\\testable_scheduled_task';

        $DB->insert_record('task_scheduled', $record);
        // And another one to test failures.
        $record->classname = '\\testable_scheduled_task2';
        $DB->insert_record('task_scheduled', $record);
        $now = time();

        // Should get handed the first task.
        $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNotNull($task);
        $task->execute();

        \core\task\manager::scheduled_task_complete($task);
        // Should get handed the second task.
        $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNotNull($task);
        $task->execute();

        \core\task\manager::scheduled_task_failed($task);
        // Should not get any task.
        $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNull($task);

        // Should get the second task (retry after delay).
        $task = \core\task\manager::get_next_scheduled_task($now + 120);
        $this->assertNotNull($task);
        $task->execute();

        \core\task\manager::scheduled_task_complete($task);

        // Should not get any task.
        $task = \core\task\manager::get_next_scheduled_task($now);
        $this->assertNull($task);
    }
}

class testable_scheduled_task extends \core\task\scheduled_task {
    public function get_name() {
        return "Test task";
    }

    public function execute() {
    }
}

class testable_scheduled_task2 extends \core\task\scheduled_task {
    public function get_name() {
        return "Test task 2";
    }

    public function execute() {
    }
}

