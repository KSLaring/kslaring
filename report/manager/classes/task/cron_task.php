<?php
/**
 * Report Manager -  Task
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/classes
 * @copyright       2010 eFaktor
 *
 * @creationDate    23/05/2017
 * @author          eFaktor     (fbv)
 *
 */
namespace report_manager\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'report_manager');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/report/manager/lib.php');
        report_manager_cron();
    }

}