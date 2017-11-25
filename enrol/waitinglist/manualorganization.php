<?php
/**
 * Waiting List - Manual submethod load organization
 *
 * @package         enrol/waitinglist
 * @subpackage      yui
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */
define('AJAX_SCRIPT', true);

require('../../config.php');
require_once($CFG->dirroot . '/report/manager/managerlib.php');

/* PARAMS   */
$parent         = optional_param('parent',0,PARAM_INT);
$manual         = required_param('manual',PARAM_INT);
$level          = required_param('level',PARAM_INT);

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = context_system::instance();
$url            = new moodle_url('/enrol/waitinglist/manualorganization.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Data */
$data       = array('name' => 'level_' . $level, 'items' => array(),'clean' => array());
$toClean    = array();
switch ($level) {
    case 0:
        $toClean[0] = 'level_1';
        $toClean[1] = 'level_2';
        $toClean[2] = 'level_3';

        break;
    case 1:
        $toClean[0] = 'level_2';
        $toClean[1] = 'level_3';

        break;
    case 2:
        $toClean[0] = 'level_3';
        break;
}
$data['clean'] = $toClean;

/**
 * Admin or normal user
 */
$myCompetence = null;
if (!$manual) {
    $myCompetence = enrol_waitinglist\method\manual\enrolmethodmanual::GetCompetenceData($USER->id);
}
$options = array();
if ($parent) {
    if ($myCompetence) {
        switch ($level) {
            case 1:
                $options    = CompetenceManager::get_companies_level_list($level,$parent,$myCompetence->levelone);

                break;
            case 2:
                $options    = CompetenceManager::get_companies_level_list($level,$parent,$myCompetence->leveltwo);

                break;
            case 3:
                $options    = CompetenceManager::get_companies_level_list($level,$parent,$myCompetence->levelthree);

                break;
        }
    }else {
        $options = CompetenceManager::get_companies_level_list($level,$parent);
    }
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