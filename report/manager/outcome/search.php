<?php
/**
 * Report Competence Manager - Search Process - Job Role Selector
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/10/2015
 * @author          eFaktor     (fbv)
 *
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('outcomelib.php');

/* PARAMS   */
$search             = required_param('search',PARAM_RAW);
$selectorId         = required_param('selectorid',PARAM_ALPHANUM);
$outcomeId          = required_param('outcome',PARAM_INT);

$optSelector    = null;
$class          = null;
$json           = array();
$selected       = array();
$jobRoles       = array();
$info           = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/outcome/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Validate if exits the selector   */
if (!isset($USER->jrselectors[$selectorId])) {
   print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->jrselectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

/* Find Outcome   */
if ($optSelector['name'] == 'addselect') {
    $selected = outcome::FindJobRoles_Selector($outcomeId,$search);
    $selected = implode(',',array_keys($selected));
    $results  = outcome::FindPotentialJobRole_Selector($selected,$search);
}else {
    $results  = outcome::FindJobRoles_Selector($outcomeId,$search);
}
$data       = array('jr' => array());
foreach ($results as $id => $jobRole) {
    /* Info Job Role    */
    $info = new stdClass();
    $info->id   = $id;
    $info->name = $jobRole;

    $jobRoles[$info->name] = $info;
}
$data['jr'] = $jobRoles;
$json[] = $data;
echo json_encode(array('results' => $json));
