<?php

/**
 * Related Courses (local) - Capabilities
 *
 * @package         local
 * @subpackage      related_courses
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/04/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/related_courses:manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),
);
