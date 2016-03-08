<?php
/**
 * Courses Site Block -  Settings
 *
 * @package         block
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/05/2014
 * @author          efaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die;

$options = array('None'             => get_string('select_orientation','block_courses_site'),
                 'opt_landscape'    => get_string('opt_landscape','block_courses_site'),
                 'opt_portrait'     => get_string('opt_portrait','block_courses_site'));

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configselect('block_courses_site/orientation',
                                                  get_string('block_orientation','block_courses_site'),
                                                  get_string('block_orientation','block_courses_site'),
                                                  'opt_landscape',$options));

    $settings->add(new admin_setting_configtext('block_courses_site/title',
                                                get_string('block_title','block_courses_site'),
                                                get_string('block_title','block_courses_site'),'',PARAM_TEXT,60));

    $settings->add(new admin_setting_configtext('block_courses_site/max',
                                                get_string('block_max_courses','block_courses_site'),
                                                get_string('block_max_courses','block_courses_site'),6,PARAM_INT,2));
}//if