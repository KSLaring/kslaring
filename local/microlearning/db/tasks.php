<?php
/**
 * Micro Learning Plugin - Schedule Task cron
 *
 * @package         local
 * @subpackage      microlearning/db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    13/01/2017
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_microlearning\task\cron_task',
        'blocking'  => 0,
        'minute'    => '*',
        'hour'      => '*',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*'
    )
);