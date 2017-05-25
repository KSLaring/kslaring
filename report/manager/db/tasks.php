<?php
/**
 * Report Manager - Cron Task
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/db
 * @copyright       2010 eFaktor
 *
 * @creationDate    22/05/2017
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'report_manager\task\cron_task',
        'blocking'  => 0,
        'minute'    => '*',
        'hour'      => '*',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*'
    )
);