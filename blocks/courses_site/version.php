<?php
/**
 * Courses Site Block - Version Settings
 *
 * @package         block
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/05/2014
 * @author          efaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2016030700;            // The current plugin version (Date: YYYYMMDDXX)
$plugin->component = 'block_courses_site';    // Full name of the plugin (used for diagnostics)

/* Dependencies */
$plugin->dependencies = array('local_courses_site' => 2016030700);