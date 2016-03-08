<?php
/**
 * Classroom Course Format Block - Capabilities
 *
 * @package         block
 * @subpackage      classroom/db
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    15/09/2015
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'block/classroom:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),

    'block/classroom:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);