<?php
/**
 * Web Services KS - Capabilities
 *
 * @package         local
 * @subpackage      wsks/db
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    08/11/2016
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/wsks:manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    )
);