<?php
/**
 * Micro Learning Plugin - Schedule Task cron
 *
 * @package         local
 * @subpackage      microlearning/cron_tasks
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    13/01/2017
 * @author          eFaktor     (fbv)
 *
 */

namespace local_microlearning\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_microlearning');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/microlearning/lib.php');
        microlearning_cron();
    }

}