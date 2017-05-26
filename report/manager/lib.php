<?php
/**
 * Report Manager - Library
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 *
 * @creationDate    23/05/2017
 * @author          eFaktor     (fbv)
 *
 */

/**
 * To run the cron
 */
function report_manager_cron() {
    try {
        require_once('cron/manager_cron.php');

        Manager_Cron::cron();
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_cron