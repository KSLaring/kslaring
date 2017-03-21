<?php
/**
 * Extra Profile Field Competence - Version
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * A new user profile which includes information about the companies and job roles connected with user
 *
 */

defined('MOODLE_INTERNAL') || die();


//$plugin->version   = 2015100900;                    // The current plugin version (Date: YYYYMMDDXX)
$plugin->version   = 2017032100;                    // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2014050800;                    // Requires this Moodle version
$plugin->component = 'profilefield_competence';     // Full name of the plugin (used for diagnostics)


/* Dependencies */
$plugin->dependencies = array('report_manager' => 2017032100);