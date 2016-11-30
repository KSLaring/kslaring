<?php
/**
 * Inconsistencies Course Completions  - Capabilities
 *
 * @package         local
 * @subpackage      icp/db
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/icp:manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
    )
);