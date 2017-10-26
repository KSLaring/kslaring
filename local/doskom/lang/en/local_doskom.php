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
$string['doskom:manage']            = 'Manage DOSKOM';

$string['err_authenticate']         = 'Sorry, You are not authenticated user. Please, contact with the system administrator.';
$string['exists_username']          = 'Other user is using the username <b>{$a->username}</b>. Please, contact with the system administrator.';
$string['user_deleted']             = 'The user <b>{$a->username}</b> has been de-activated. Please, contact with the system administrator.';

$string['application_subject']      = 'Welcome to Moodle {$a->site}';
$string['application_body']         = '<p>Welcome <b>{$a->name}</b> to Moodle <b>{$a->site}</b></p><p>A new account has been created for you. The details of your new account are:</p><p><b>username:</b> {$a->username}</p><b>password:</b> {$a->pwd}<p></p><p>Please,<b>YOU MUST CHANGE YOUR PASSWORD</b>. You can do it from <b>{$a->change}</b></p>';

$string['sel_company']              = 'Choose a company...';
$string['company']                  = 'Company';

$string['cron_activate']            = 'Enabled';
$string['cron_deactivate']          = 'Disabled';
$string['cron_wsso']                = 'DOSKOM SSO';

$string['end_point']                        = 'API Import Users';
$string['end_point_production']             = 'Production';
$string['end_point_production_desc']        = 'Production or Pilot site';

$string['crontask']             = 'Doskom Synchronization Cron Task';

$string['sources']      = 'Sources';
$string['sources_desc'] = 'Sources information (APIs End points)';
$string['viewsources']  = 'View sources';
$string['addsource']    = 'Add source';

$string['companies']        = 'Companies';
$string['companies_desc']   = 'Companies information';
$string['viewcomp']         = 'View companies';
$string['addcomp']          = 'Add company';

$string['strsource']    = 'Source';
$string['strlabel']     = 'Label';
$string['stractions']   = 'Actions';
$string['strcompany']   = 'Company';
$string['stractive']    = 'Active';

$string['headersource']     = 'DOSKOM Sources';
$string['headercompany']    = 'DOSKOM Companies';

$string['delete_endpoint_are_you_sure']   = 'Are you sure you want to delete the <strong>{$a}</strong> end point?';
$string['delete_endpoint_companies_are_you_sure']   = 'There are companies connected with .Are you sure you want to delete the <strong>{$a}</strong> end point?';
$string['delete_company_are_you_sure']    = 'Are you sure you want to delete the <strong>{$a}</strong> company?';

$string['deletedendpoint'] = 'The <strong>{$a}</strong> end point has been deleted';
$string['deletedcompany'] = 'The <strong>{$a}</strong> company has been deleted';
$string['error_deleted'] = 'Sorry, there has been a problem during the process. Please, try again or contact with administrator ';

$string['strcoid']      = 'Company ID';
$string['strconame']    = 'Company name';
$string['srtcouser']    = 'User';
$string['strcotoken']   = 'Token';
$string['strselone']    = 'Select one...';

$string['errexits'] = 'Already exists';

$string['basic_notify'] = 'Notify by email to';

$string['errorws_subject'] = ' {$a}: Integration DOSKOM  - ERROR RESPONSE';
$string['errorws_body']    = ' <p>We would like to inform you, that there has been a problem during the connection/communication with <strong>DOSKOM {$a->company}</strong> services.</p> 
                               <p>So, the process has got an invalid response at <strong>{$a->time}</strong>.</p> ';

$string['errorprocess_subject'] = ' {$a}: Integration DOSKOM  - ERROR';
$string['errorprocess_body']    = ' <p>We would like to inform you, that there has been a problem during the process so the <strong>DOSKOM {$a->company}</strong> could no bet carried out at <strong>{$a->time}</strong>.</p>';

