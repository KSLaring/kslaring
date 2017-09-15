<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * WSDOSKOM - Cron Settings
 *
 * @package         local
 * @subpackage      doskom/cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate      27/02/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_doskom', get_string('pluginname','local_doskom'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_doskom'),
                     '1' => get_string('cron_activate','local_doskom'));
    $settings->add(new admin_setting_configselect('local_doskom/wsdoskom_cron_active', new lang_string('active'),  '', 1, $options));
    
    // Web Service
    /**
    $settings->add(new admin_setting_configtext('local_doskom/wsdoskom_end_point',get_string('end_point','local_doskom'),'','',PARAM_TEXT,50));

    // Production or Pilot Site 
    $settings->add(new admin_setting_configcheckbox('local_doskom/wsdoskom_end_point_production',
                                                    get_string('end_point_production','local_doskom'),
                                                    get_string('end_point_production_desc','local_doskom'),1));
    **/

    // Notifications
    $settings->add(new admin_setting_configtext('local_doskom/mail_notification',
                                                      get_string('basic_notify', 'local_doskom'), '', ''));

    // Add seting links doskom
    require_once('settingslib.php');
    require_once('lib/actionslib.php');

    // Link view sources
    $urlview = new moodle_url('/local/doskom/actions/view.php',array('t' => SOURCE));
    $lnkview = '<a href="' . $urlview. '">' . get_string('viewsources','local_doskom'). '</a>';
    // Link add sources
    $urladd = new moodle_url('/local/doskom/actions/sources.php',array('a' => ADD_SOURCE));
    $lnkadd = '<a href="' . $urladd. '">' . get_string('addsource','local_doskom'). '</a>';

    // Link Sources
    $settings->add(new admin_setting_link_doskom('local_doskom/source',
        get_string('sources','local_doskom'),get_string('sources_desc','local_doskom'),
        null,$lnkview,$lnkadd));

    // Link view companies
    $urlview = new moodle_url('/local/doskom/actions/view.php',array('t' => COMPANIES));
    $lnkview = '<a href="' . $urlview. '">' . get_string('viewcomp','local_doskom'). '</a>';
    // Link add companies
    $urladd = new moodle_url('/local/doskom/actions/companies.php',array('a' => ADD_COMPANY));
    $lnkadd = '<a href="' . $urladd. '">' . get_string('addcomp','local_doskom'). '</a>';

    // Link Sources
    $settings->add(new admin_setting_link_doskom('local_doskom/companies',
        get_string('companies','local_doskom'),get_string('companies_desc','local_doskom'),
        null,$lnkview,$lnkadd));
}//if