<?php
/**
 * Gender Profile Field - Script to update all existing users
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/gender
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/10/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('../lib/genderlib.php');

require_login();

/* PARAMS */
$context    = context_system::instance();
$url        = new moodle_url('/user/profile/field/gender/script/gender.php');
$returnUrl  = new moodle_url($CFG->wwwroot . '/index.php');
$fieldId    = null;

/* Set page */
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

/* Header   */
echo $OUTPUT->header();

/* Require be an admin */
if (!is_siteadmin($USER->id)) {
    echo $OUTPUT->notification(get_string('no_permissions','profilefield_gender'), 'notifysuccess');
    echo $OUTPUT->continue_button($returnUrl);
}

/* Create gender profile if it does not exist       */
echo "Checking..." . "</br>";
if (!Gender::ExistGenderProfile()) {
    echo "Creating Gender Profile Instance" . "</br>";
    /* Create gender profile    */
    $fieldId = Gender::CreateGenderProfile();
    echo "Instance Created" . "</br>";
}

echo "Update USers " . "</br>";
Gender::AddGender_ToUsers(14,0,2000);
$remiander = 0;

/* Footer   */
echo $OUTPUT->footer();
