<?php

/**
 * Municipality Block - Version Settings
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @updateDate      20/08/2014
 * @author          efaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

//$plugin->version   = 2013082204;                // The current plugin version (Date: YYYYMMDDXX)
//$plugin->requires  = 2012112900;              // Requires this Moodle version

$plugin->version   = 2017041206;                // The current plugin version (Date: YYYYMMDDXX)
$plugin->component = 'block_municipality';      // Full name of the plugin (used for diagnostics)

/* Dependencies */
$plugin->dependencies = array('profilefield_municipality' => 2016030700);
