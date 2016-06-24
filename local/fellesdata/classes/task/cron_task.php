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
        $pluginInfo     = null;
        $now            = time();
        $fstExecution   = null;

        /* Library */
        require_once('../../cron/fellesdatacron.php');
        require_once('../../lib/fellesdatalib.php');


        /* First execution or no */
        $activate = get_config('local_fellesdata','cron_active');
        //if ($activate) {
            $lastexecution = get_config('local_fellesdata','lastexecution');
            if ($lastexecution) {
                $fstExecution = false;
            }else {
                $fstExecution = true;
            }

            \FELLESDATA_CRON::cron($fstExecution);

            $lastexecution = get_config('local_fellesdata','lastexecution');
            $dbLog  = "LAST EXECUTION WS: " . userdate($lastexecution,'%d.%m.%Y', 99, false) . "\n";
            $dbLog  .= "NEW EXECUTION WS: " . userdate($now,'%d.%m.%Y', 99, false) . "\n\n";

            set_config('lastexecution', $now, 'local_fellesdata');            
        //}
    }

}