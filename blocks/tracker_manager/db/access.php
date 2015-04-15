<?php

/**
 * Tracker Manager Block - Capabilities
 *
 * @package         block
 * @subpackage      tracker_manager
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/024/2015
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/tracker_manager:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

    'block/tracker_manager:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),
);