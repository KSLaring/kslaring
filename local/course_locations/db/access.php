<?php
/**
 * Course Locations  - Capabilities
 *
 * @package         local
 * @subpackage      course_locations
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/04/2015
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/course_locations:manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSE,
        'archetypes' => array(
            'coursecreator'  => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    )
);