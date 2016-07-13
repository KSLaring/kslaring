<?php
/**
 * Competence Profile - Job Role
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 *
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../../../config.php');
require_once('../competencelib.php');

/* PARAMS   */
$levelZero      = required_param('levelZero',PARAM_INT);
$levelOne       = optional_param('levelOne',0,PARAM_INT);
$levelTwo       = optional_param('levelTwo',0,PARAM_INT);
$levelThree     = optional_param('levelThree',0,PARAM_INT);

$json           = array();
$data           = array();
$options        = array();
$jobRoles       = array();
$infoJR         = null;
$managers       = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/user/profile/field/competence/actions/jobrole.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('jr' => array(),'toApprove' => 0);

/* Get Job Roles    */
$options[0] = get_string('select_level_list','report_manager');

/* Level Three  */
if ($levelThree) {
    /* Add Generics --> Only Public Job Roles   */
    if (Competence::IsPublic($levelThree)) {
        Competence::GetJobRoles_Generics($options);
    }//if_isPublic

    Competence::GetJobRoles_Hierarchy($options,$levelZero,$levelOne,$levelTwo,$levelThree);

    $managers = Competence::ManagersConnected($levelZero,$levelOne,$levelTwo,$levelThree);
    if ($managers) {
        $data['toApprove']  = 1;
    }
}//if_level_three

foreach ($options as $id => $jr) {
    /* Info Company */
    $infoJR            = new stdClass;
    $infoJR->id        = $id;
    $infoJR->name      = $jr;

    /* Add Company*/
    $jobRoles[$infoJR->name] = $infoJR;
}

$data['jr'] = $jobRoles;
$json[]     = $data;
echo json_encode(array('results' => $json));
