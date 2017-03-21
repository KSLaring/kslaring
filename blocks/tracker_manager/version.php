<?php

/**
 * Tracker Manager Block - Version Settings
 *
 * @package         block
 * @subpackage      tracker_manager
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/04/2014
 * @author          efaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017032100;                    // The current plugin version (Date: YYYYMMDDXX)
//$plugin->requires  = 2012112900;                  // Requires this Moodle version
$plugin->component = 'block_tracker_manager';    // Full name of the plugin (used for diagnostics)

/* Dependencies */
$plugin->dependencies = array('report_manager'  => 2017032100);