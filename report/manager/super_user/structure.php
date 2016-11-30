<?php
/**
 * Report Competence Manager - Super User - Company Structure
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('spuserlib.php');
require_once('../managerlib.php');


/* PARAMS   */
$parent         = optional_param('parent',0,PARAM_INT);
$level          = required_param('level',PARAM_INT);

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/report/manager/super_user/structure.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('name' => SP_USER_COMPANY_STRUCTURE_LEVEL . $level, 'items' => array(),'clean' => array());
$toClean    = array();
switch ($level) {
    case 0:
        $toClean[0] = SP_USER_COMPANY_STRUCTURE_LEVEL . 1;
        $toClean[1] = SP_USER_COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[2] = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 1:
        $toClean[0] = SP_USER_COMPANY_STRUCTURE_LEVEL . 2;
        $toClean[1] = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;

        break;
    case 2:
        $toClean[0] = SP_USER_COMPANY_STRUCTURE_LEVEL . 3;

        break;
}
$data['clean'] = $toClean;

if ($parent) {
    $options = CompetenceManager::GetCompanies_LevelList($level,$parent);
}else {
    $options[0] = get_string('select_level_list','report_manager');
}//if_parent

foreach ($options as $companyId => $company) {
    /* Info Company */
    $infoCompany            = new stdClass;
    $infoCompany->id        = $companyId;
    $infoCompany->name      = $company;

    /* Add Company*/
    $data['items'][$infoCompany->name] = $infoCompany;
}



/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));