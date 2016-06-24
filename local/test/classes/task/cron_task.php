<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 24/06/16
 * Time: 09:53
 */

namespace local_test\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'mod_forum');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once('../../lib.php');
        test_cron();
    }

}