<?php
/**
 * Express Login  - Schedule Cron Task
 *
 * @package         local
 * @subpackage      express_login/classes
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    13/01/2017
 * @author          eFaktor     (fbv)
 */
namespace local_express_login\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_express_login');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/express_login/lib.php');
        express_login_cron();
    }

}