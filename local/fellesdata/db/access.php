<?php
/**
 * Fellesdata Integration - Capabilities
 *
 * @package         local/fellesdata
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/fellesdata:manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    )
);