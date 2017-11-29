<?php
/**
 * Express Login  - Language Settings (English)
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/11/2014
 * @author          eFaktor     (fbv)
 */

$string['pluginname']           = 'Express Login';

$string['title_info']               = 'Here you can administer your own express login to {$a}. </br> Here you can generate your unique personal link';
$string['title_link']               = 'Here you can copy your unique personal Express Login link to {$a} to the clipboard and/or add it to the bookmark ';
$string['title_regenerate_link']    = 'Here you can regenerate your unique personal link.';
$string['regenerate_link']          = 'To regenerate a new unique personal link, you must enter a new security phrase';
$string['warning_regenerate']       = 'You have never generated your own express login to {$a->site} before. If it is your first time, click on <strong>{$a->url} </strong>.';
$string['title_change']             = 'Here you can change your express login to {$a}.';
$string['header_new_code']          = 'Change Express Login';

$string['pin_code']             = 'My PIN code';
$string['pin_old_code']         = 'Current PIN code';
$string['pin_new_code']         = 'New PIN code';
$string['pin_new_code_again']   = 'New PIN code (again)';
$string['pin_code_help']        = 'Remind that you must enter a pin code of 4 - 6 - 8 digits.';
$string['pin_code_min']         = 'The PIN code has to be a number of {$a} digits';

$string['pin_question']         = 'Security phrase';
$string['pin_new_question']     = 'New Security phrase';
$string['pin_question_help']    = 'Security phrase. (25 characters)';
$string['pin_security_err']     = 'The security phrase has to be a string of 25 characters';
$string['pin_identical_err']    = 'The PIN code is not valid. The digits are identical';
$string['pin_consecutive_err']  = 'The PIN code contains consecutive digits';
$string['pin_code_err']         = 'The PIN code is not secure enough';
$string['pin_percentage_err']   = 'The PIN code is not valid. The digit {$a} is repetitive';
$string['pin_numeric_err']      = 'The PIN code has to be a numeric string';
$string['pin_code_expired']     = 'PIN code expired.';

$string['err_generic']          = 'There has been an error during the process. Please, try again or contact with administrator';
$string['err_micro_lnk']        = 'Micro Learning Link not valid. Please, contact with your instructor';

$string['pin_new_diff_err']         = 'The PIN code are different';
$string['pin_new_not_diff_current'] = 'The new PIN code and the current are the same';
$string['pin_current_diff_err']     = 'The current PIN code not valid';

$string['err_remind']               = 'Not valid. It is the same that the old one, you must fill a new one';

$string['btn_copy_link']        = 'Get Express Login Link';
$string['btn_save_link']        = 'Save as bookmark';
$string['btn_generate_link']    = 'Generate Link';
$string['btn_regenerate_link']  = 'Regenerate Express Link';
$string['btn_change_pin_code']  = 'Change PIN CODE';

$string['settings_desc']        = 'Quick Access Module is a feature that gives users direct login by just entering a pin code of 4-8 digits. Quick access module generates a unique personal link to each user as they can store in the browser Favorites / Bookmarks.
                                   When a user clicks on this link opens a dialog box where personal pin code must be entered. One has only three attempts before being redirected to the default login method with username and password.
                                   The PIN code is created in your own user profile and you can anytime replace it with a new one.';

$string['set_activate']         = 'Activate express login';
$string['set_activate_desc']    = 'Activate express login';

$string['set_deny']             = 'Deny identical digits';
$string['set_deny_desc']        = 'Deny identical digits';

$string['set_expire']           = 'Expire after';
$string['set_expire_desc']      = 'Expire after';

$string['set_force']            = 'Force a new express login token';
$string['set_force_desc']       = 'Force a new express login token';

$string['set_minimum']          = 'Minimum number of digits';
$string['set_minimum_dec']      = 'Minimum number of digits';

$string['set_encryption']       = 'Encryption phrase';
$string['set_encryption_desc']  = 'Encryption phrase (25 characters)';

$string['ERROR_EXPRESS_LINK_NOT_VALID']             = 'The Express Login Link not valid.Please,regenerate it or contact to administrator.';
$string['ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED']    = 'Number of attempts exceeded';
$string['ERROR_EXPRESS_LINK_USER_NOT_VALID']        = 'The user not valid';
$string['ERROR_EXPRESS_PIN_NOT_VALID']              = 'PIN code not valid. You have {$a} attempts more';

$string['clipboardDiv'] = 'Your Express Login Link has been copied to the clipboard. You only have to do CTR+V to copy where you want.';
$string['bookmarkDiv']  = 'To add your personal Express Login Link to the bookmark, please drop and drag <strong>{$a}</strong> to the bookmark.';

$string['err_express_access'] = 'You not have permissions to generate Express Login.';

$string['cron_settings']            = 'Cron settings';
$string['cron_activate']            = 'Enabled';
$string['cron_deactivate']          = 'Disabled';

$string['express_subject']        = '{$a}: Express Login Auto Generated';
$string['express_body']           = '<p>Dear {$a->name},</p><p>We would like to inform you that your <strong>Express Login</strong> has been generated automatically. Your <strong>pin code</strong> is: <strong>{$a->express}</strong></p>
                                    <p>You can get your <strong>Express Login Link</strong> in <strong>My profile settings->Express Login-><u>Express Login</u></strong>.</p>';

$string['micro_message']          = '<p>We would like to inform you, that you have changed your <strong>Express Login</strong>.So, you get your <strong>Microlearning</strong> with the new <strong>Express Login</strong>.</p>';

$string['bulk_action']      = 'Generated Express Login';
$string['bulk_succesful']   = 'The Express Login will be generated during the next 30 minutes.';

$string['crontask']             = 'Express login cron task';

$string['express_disable'] = 'Express login is disable';