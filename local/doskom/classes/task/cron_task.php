<?php
/**
 * Doskom Integration - Cron Task
 *
 * @package         local/doskom
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2016
 * @author          eFaktor     (fbv)
 *
 */

namespace local_doskom\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_doskom');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;

        try {
            require_once($CFG->dirroot . '/local/doskom/lib.php');
            doskom_cron();
        }catch (\Exception $ex) {
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' ERROR EXECUTE CALL CRON ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/doskom.log");
            throw $ex;
        }
    }

}