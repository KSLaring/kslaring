<?php

/**
 * Report Generator - Capabilities
 *
 * Description
 *
 * @package         report
 * @subpackage      generator
 * @copyright       2010 eFaktor
 * @updateDate      06/09/2012
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array (
    // People who can can see the report generator link
    'report/generator:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    // People with level 1 rights to view the reports
    'report/generator:viewlevel1' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    // People with level 2 rights to view the reports
    'report/generator:viewlevel2' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    // People with level 3 rights to view the reports
    'report/generator:viewlevel3' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    // People with level 4 rights to view the reports
    'report/generator:viewlevel4' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        )
    ),

    // People who can edit.
    'report/generator:edit' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'report/generator:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
);
//'riskbitmask' => RISK_PERSONAL | RISK_DATALOSS | RISK_CONFIG,