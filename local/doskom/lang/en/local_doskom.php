<?php
/**
 * Single Sign On WebService - Language Strings (English)
 *
 * @package         local
 * @subpackage      doskom
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 */

$string['pluginname']               = 'DOSKOM SSO';

$string['err_authenticate']         = 'Sorry, You are not authenticated user. Please, contact with the system administrator.';
$string['exists_username']          = 'Other user is using the username <b>{$a->username}</b>. Please, contact with the system administrator.';
$string['user_deleted']             = 'The user <b>{$a->username}</b> has been de-activated. Please, contact with the system administrator.';

$string['application_subject']      = 'Welcome to Moodle {$a->site}';
$string['application_body']         = '<p>Welcome <b>{$a->name}</b> to Moodle <b>{$a->site}</b></p><p>A new account has been created for you. The details of your new account are:</p><p><b>username:</b> {$a->username}</p><b>password:</b> {$a->pwd}<p></p><p>Please,<b>YOU MUST CHANGE YOUR PASSWORD</b>. You can do it from <b>{$a->change}</b></p>';

$string['sel_company']              = 'Choose a company...';
$string['company']                  = 'Company';

$string['cron_activate']            = 'Enabled';
$string['cron_deactivate']          = 'Disabled';
$string['cron_wsso']                = 'Single Sign on - Cron';

$string['end_point']                        = 'API Import Users';
$string['end_point_production']             = 'Production';
$string['end_point_production_desc']        = 'Production or Pilot site';

$string['crontask']             = 'Doskom Synchronization Cron Task';
