<?php
/**
 * Fellesdata Integration - Cron Task
 *
 * @package         local/fellesdata
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    24/06/2016
 * @author          eFaktor     (fbv)
 *
 */

namespace local_fellesdata\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_fellesdata');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/fellesdata/lib.php');
        fellesdata_cron();
    }

}