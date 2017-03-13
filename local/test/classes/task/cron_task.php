<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 13/03/17
 * Time: 10:57
 */
namespace local_test\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_test');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/test/lib.php');
        test_cron();
    }

}