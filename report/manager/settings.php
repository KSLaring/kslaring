<?php

/**
 * Report Competence Manager - Settings
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @updateDate      06/09/2012
 * @author          eFaktor     (fbv)
 *
 * Add link to site administration.
 *
 */

defined('MOODLE_INTERNAL') || die();

$url = new moodle_url('/report/manager/index.php');
//$CFG->wwwroot.'/report/manager/index.php'
$ADMIN->add('reports',
        new admin_externalpage('manager', get_string('pluginname','report_manager'),
        $url));

//Indicates That we only want to display link
$settings->add(new admin_setting_heading('report_manager_report', '', get_string('report_manager', 'report_manager')));

