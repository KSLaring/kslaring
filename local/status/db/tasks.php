<?php
/**
 * Fellesdata Status Integration - Shecduled Task for the cron synchronization
 *
 * @package         local/status
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_status\task\cron_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);