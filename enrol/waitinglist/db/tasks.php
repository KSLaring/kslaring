<?php // $Id: events.php

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
	'classname' => 'enrol_waitinglist\task\waitinglisttask',                                                            
    'blocking' => 0,                                                                                             
    'minute' => '*/5',
	'hour' => '*',
	'day' => '*',
	'dayofweek' => '*',
	'month' => '*'
	)
);
