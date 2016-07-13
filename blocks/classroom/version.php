<?php
/**
 * Classroom Course Format Block - Version
 *
 * @package         block
 * @subpackage      classroom
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/09/2015
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version    = 2016030700;           // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires   = 2014050800;           // Requires this Moodle version
$plugin->component  = 'block_classroom';    // Full name of the plugin (used for diagnostics)


/* Dependencies */
$plugin->dependencies = array('local_course_page'               => 2016033100,
                              'format_classroom_frikomport'    => 2016030700
                             );