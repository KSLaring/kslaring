<?php
/**
 * Participants List  - Capabilities
 *
 * @package         local
 * @subpackage      participants/db
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/participants:manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSECAT,CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        ),
    ),
    
    'local/participants:view' => array(
        'riskbitmask' => RISK_XSS,
    
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSECAT,CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        ),
    )
);