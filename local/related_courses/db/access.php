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
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        )
    ),
);
