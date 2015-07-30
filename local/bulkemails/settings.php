<?php

// This file is NOT part of Moodle - http://moodle.org/

//

// This is free software: you can redistribute it and/or modify

// it under the terms of the GNU General Public License as published by

// the Free Software Foundation, either version 3 of the License, or

// (at your option) any later version.

//

// This software is distributed in the hope that it will be useful,

// but WITHOUT ANY WARRANTY; without even the implied warranty of

// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

// GNU General Public License for more details.

//

// You should have received a copy of the GNU General Public License

// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.



/**

 * Add link to settings page from the settings block.

 *

 * @package    local_openid_idp

 * @copyright  2011 MuchLearning

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

 */



defined('MOODLE_INTERNAL') || die;

$capabilities = array('local/bulkemails:config');

$systemcontext = context_system::instance();

if (isset($capabilities)) {

//$ADMIN->add('local', new admin_category('bulkemails',get_string('bulkemails:manage', 'local_bulkemails')));

$ADMIN->add('reports', new admin_externalpage('view Bulk Emails', get_string('view report', 'local_bulkemails'), "$CFG->wwwroot/local/bulkemails/index.php",'local/bulkemails:config'));

  }
  
  if ($hassiteconfig) {
    global $CFG, $USER, $DB;

    $moderator = get_admin();
    $site = get_site();

    $settings = new admin_settingpage('local_bulkemails', get_string('pluginname', 'local_bulkemails'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_bulkemails/bulk_cron_time';
    $title =get_string('cron_intervel', 'local_bulkemails'); 
    $description = get_string('cron_intervel_desc', 'local_bulkemails');
    $setting = new admin_setting_configtext($name, $title, $description,1);
    $settings->add($setting);

    $name = 'local_bulkemails/bulk_no_emails_cron';
    $title = get_string('bulk_no_emails_cron', 'local_bulkemails'); 
    $description =get_string('bulk_no_emails_cron_desc', 'local_bulkemails'); 
    $setting = new admin_setting_configtext($name, $title, $description, 10);
    $settings->add($setting);

}







