<?php

/**
 * Related Courses (local) - Version
 *
 * @package         local
 * @subpackage      lightbox
 * @copyright       2014 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version    = 2016030700; /* The current plugin version (Date: YYYYMMDDXX)  */
$plugin->requires   = 2012061700; /* Requires this Moodle version                   */
$plugin->component  = 'local_lightbox'; /* Full name of the plugin (used for diagnostics) */

/* Dependencies */
$plugin->dependencies = array('local_scorm_lightbox' => 2016030700);