<?php
/**
 * Fellesdata Status Integration - Cron Task
 *
 * @package         local/fellesdata_satus
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

namespace local_fellesdata_status\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_fellesdata_status');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/fellesdata_status/lib.php');
        fellesdata_status_cron();
    }

}