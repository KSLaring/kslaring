<?php
/**
 * Coteacher Block -  Settings
 *
 * @package         block
 * @subpackage      block_coinstructor
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/04/2017
 * @author          efaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_coinstructor/max',
        get_string('block_max_courses','block_coinstructor'),
        get_string('block_max_courses','block_coinstructor'), 20, PARAM_INT,5));
}//if