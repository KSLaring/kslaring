<?php
/**
 * Course Locations  - Capabilities
 *
 * @package         local
 * @subpackage      fridadmin
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/04/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      16/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Integrate into friadmin plugin
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/friadmin:course_locations_manage' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSE,CONTEXT_COURSECAT,
        'archetypes' => array(
            'coursecreator'  => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/friadmin:view' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,CONTEXT_COURSE,CONTEXT_COURSECAT,
        'archetypes' => array(
            'coursecreator'  => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    )
);