<?php
/**
 * DOSKOM Integration - Shecduled Task for the cron synchronization
 *
 * @package         local/doskom
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_doskom\task\cron_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);