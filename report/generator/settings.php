<?php

/**
 * Report Generator - Settings
 *
 * Description
 *
 * @package         report
 * @subpackage      generator
 * @copyright       2010 eFaktor
 * @updateDate      06/09/2012
 * @author          eFaktor     (fbv)
 *
 * Add link to site administration.
 *
 */

defined('MOODLE_INTERNAL') || die();

$url = new moodle_url('/report/generator/index.php');
//$CFG->wwwroot.'/report/generator/index.php'
$ADMIN->add('reports',
        new admin_externalpage('generator', get_string('pluginname','report_generator'),
        $url));

//Indicates That we only want to display link
$settings->add(new admin_setting_heading('report_generator_report', '', get_string('report_generator', 'report_generator')));

