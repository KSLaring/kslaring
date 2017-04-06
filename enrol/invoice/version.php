<?php
/**
 * Invoice Enrolment - Version
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017032400;                    // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2012112900;                    // Requires this Moodle version
$plugin->component = 'enrol_invoice';               // Full name of the plugin (used for diagnostics)
$plugin->cron      = 60;