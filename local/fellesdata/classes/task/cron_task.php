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
        return get_string('crontask', 'local_test');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        $pluginInfo     = null;
        $now            = time();
        $fstExecution   = null;

        /* Library */
        require_once('../../cron/fellesdatacron.php');
        require_once('../../lib/fellesdatalib.php');


        /* First execution or no */
        $lastexecution = get_config('local_fellesdata','lastexecution');
        if ($lastexecution) {
            $fstExecution = false;
        }else {
            $fstExecution = true;
        }

        \FELLESDATA_CRON::cron($fstExecution);
        set_config('lastexecution', $now, 'local_fellesdata');
    }

}