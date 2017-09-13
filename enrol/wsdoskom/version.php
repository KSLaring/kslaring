<?php

/**
 * Single Sign On Enrolment Plugin - Version
 *
 * @package         enrol
 * @subpackage      wsdoskom
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2015
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2016030702;                    // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2012112900;                    // Requires this Moodle version
$plugin->component = 'enrol_wsdoskom';                 // Full name of the plugin (used for diagnostics)
$plugin->cron      = 60;

/* Dependencies */
$plugin->dependencies = array('local_doskom' => 2016030700);