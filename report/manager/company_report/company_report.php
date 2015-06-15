<?php
/**
 * Report Competence Manager - Company report.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_report/
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  08/04/2015
 * @author      eFaktor     (fbv)
 *
 * @updateDate  15/06/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Only the companies connected with my level
 *
 */

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('companyrptlib.php');
require_once($CFG->dirroot . '/report/manager/company_report/filter/lib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once('company_report_form.php');
require_once('user_selector_form.php');

/* Params */
$show_advanced  = optional_param('advanced',0,PARAM_INT);
$format         = optional_param('format','',PARAM_ALPHA);
$return_url     = new moodle_url('/report/manager/index.php');
$url            = new moodle_url('/report/manager/company_report/company_report.php',array('advanced' => $show_advanced));
$my_hierarchy   = null;
$myCompanies    = null;
$companyTracker = null;
$company        = null;
$users_lst      = null;

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}

require_login();

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
require_capability('report/manager:viewlevel4', $site_context,$USER->id);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->requires->js(new moodle_url('/report/manager/js/tracker.js'));
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->verify_https_required();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('company_report_link','report_manager'),$url);

/* My Hierarchy */
$my_hierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$site_context);

/* Create the user filter   */
$user_filter = new company_report_filtering(null,$url,null);
/* Set My Companies         */
if ($my_hierarchy->competence) {
    $myCompanies = CompanyReport::Get_MyCompanies($my_hierarchy->competence,$my_hierarchy->my_level);
    $user_filter->set_MyCompanies(implode(',',array_keys($myCompanies)));
}else {
    $user_filter->set_MyCompanies(null);
}
/* Selector User Form   */
$selector_users = new manager_company_user_selector_form(null,CompanyReport::GetSelection_Filter($user_filter));
if ($data = $selector_users->get_data()) {
    if (!empty($data->addall)) {
        CompanyReport::AddAll_SelectionFilter($user_filter);

    } else if (!empty($data->addsel)) {
        if (!empty($data->ausers)) {
            if (in_array(0, $data->ausers)) {
                CompanyReport::AddAll_SelectionFilter($user_filter);
            } else {
                foreach($data->ausers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    if (!isset($SESSION->bulk_users[$userid])) {
                        $SESSION->bulk_users[$userid] = $userid;
                    }
                }
            }
        }

    } else if (!empty($data->removeall)) {
        $SESSION->bulk_users= array();

    } else if (!empty($data->removesel)) {
        if (!empty($data->susers)) {
            if (in_array(0, $data->susers)) {
                $SESSION->bulk_users= array();
            } else {
                foreach($data->susers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    unset($SESSION->bulk_users[$userid]);
                }
            }
        }
    }

    // reset the form selections
    unset($_POST);
    $selector_users = new manager_company_user_selector_form(null, CompanyReport::GetSelection_Filter($user_filter));
}//if_selectorUsers_getData

/* Show Form */
$form = new manager_company_report_form(null,array($my_hierarchy,$show_advanced));
$out = '';

if ($form->is_cancelled()) {
    /* Clean Cookies    */
    setcookie('parentLevelZero',0);
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelThree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    /* Get the users that have been selected    */
    if (!$SESSION->bulk_users) {
        CompanyReport::AddAll_SelectionFilter($user_filter);
    }//if_sesion_users_bulk

    $users_lst = $SESSION->bulk_users;

    /* Get Company Tracker Info */
    $company = new stdClass();
    $company->levelZero     = $_COOKIE['parentLevelZero'];
    $company->levelOne      = $_COOKIE['parentLevelOne'];
    $company->levelTwo      = $_COOKIE['parentLevelTwo'];
    $company->levelThree    = $data_form[COMPANY_REPORT_STRUCTURE_LEVEL . '3'];

    $companyTracker = CompanyReport::Get_CompanyTracker($company,$users_lst);

    switch ($data_form[COMPANY_REPORT_FORMAT_LIST]) {
        case COMPANY_REPORT_FORMAT_SCREEN:
            $out = CompanyReport::PrintReport_CompanyTracker($companyTracker);
            break;
        case COMPANY_REPORT_FORMAT_SCREEN_EXCEL:
            CompanyReport::DownloadReport_CompanyTracker($companyTracker);
            break;
        default:
            break;
    }//switch
}//if_else

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    /* Print Title */
    echo $out;
}else {
    /* Print tabs at the top */
    $current_tab = 'company_report';
    $show_roles = 1;
    require('../tabs.php');

    if ($show_advanced) {
        $out  = html_writer::start_tag('div',array('class' => 'advance_set'));
        $out .= html_writer::link(new moodle_url('/report/manager/company_report/company_report.php',array('advanced' => '0')),get_string('hideadvancedsettings'));
        $out .= html_writer::end_tag('div'); //div_expiration
        echo $out;

        /* Add the filters  */
        $user_filter->display_add();
        $user_filter->display_active();
        flush();

        $selector_users->display();
    }else {
        $out  = html_writer::start_tag('div',array('class' => 'advance_set'));
        $out .= html_writer::link(new moodle_url('/report/manager/company_report/company_report.php',array('advanced' => '1')),get_string('showadvancedsettings'));
        $out .= html_writer::end_tag('div'); //div_expiration
        echo $out;
    }//if_show_advanced

    $form->display();
}//if_else


/* Print Footer */
echo $OUTPUT->footer();