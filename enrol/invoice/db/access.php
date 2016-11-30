<?php
/**
 * Invoice Enrolment Method - Capabilities
 *
 * @package         enrol/invoice
 * @subpackage      db
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/09/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    /* Add or edit enrol-self instance in course. */
    'enrol/invoice:config' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    /* Manage user self-enrolments. */
    'enrol/invoice:manage' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    /* Voluntarily unenrol self from course - watch out for data loss. */
    'enrol/invoice:unenrolself' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
        )
    ),

    /* Unenrol anybody from course (including self) -  watch out for data loss. */
    'enrol/invoice:unenrol' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

);
