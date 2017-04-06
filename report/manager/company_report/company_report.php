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
$site_context = context_system::instance();
$IsReporter = CompetenceManager::IsReporter($USER->id);
if (!$IsReporter) {
    require_capability('report/manager:viewlevel4', $site_context,$USER->id);
}

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
$my_hierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$site_context,$IsReporter,0);

/* Create the user filter   */
$user_filter = new company_report_filtering(null,$url,null);
/* Set My Companies         */
if ($my_hierarchy->competence) {
    if ($IsReporter) {
        $myLevelThree = null;
        foreach($my_hierarchy->competence as $key => $competence) {
            if ($myLevelThree) {
                $myLevelThree .= ',' . implode(',',$competence->levelThree);
            }else {
                $myLevelThree = implode(',',$competence->levelThree);
            }//if_lvelThree
        }//for_competence

        $user_filter->set_MyCompanies($myLevelThree);
    }else {
        $myCompanies = CompanyReport::Get_MyCompanies($my_hierarchy->competence,$my_hierarchy->my_level);
        $user_filter->set_MyCompanies(implode(',',array_keys($myCompanies)));
    }//if_IsReporter
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
$form = new manager_company_report_form(null,array($my_hierarchy,$show_advanced,$IsReporter));
$out = '';

if ($form->is_cancelled()) {
    unset($SESSION->selection);

    $_POST = array();
    $SESSION->bulk_users= array();

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
    $company->levelZero     = $data_form[COMPANY_STRUCTURE_LEVEL . '0'];
    $company->levelOne      = $data_form[COMPANY_STRUCTURE_LEVEL . '1'];
    $company->levelTwo      = $data_form[COMPANY_STRUCTURE_LEVEL . '2'];
    $company->levelThree    = $data_form[COMPANY_STRUCTURE_LEVEL . '3'];

    $companyTracker = CompanyReport::Get_CompanyTracker($company,$users_lst);

    /* Keep selection data --> when it returns to the main page */
    $SESSION->selection = array();
    $SESSION->selection[COMPANY_STRUCTURE_LEVEL . '0']   = (isset($data_form[COMPANY_STRUCTURE_LEVEL . '0']) ? $data_form[COMPANY_STRUCTURE_LEVEL . '0'] : 0);
    $SESSION->selection[COMPANY_STRUCTURE_LEVEL . '1']   = (isset($data_form[COMPANY_STRUCTURE_LEVEL . '1']) ? $data_form[COMPANY_STRUCTURE_LEVEL . '1'] : 0);
    $SESSION->selection[COMPANY_STRUCTURE_LEVEL . '2']   = (isset($data_form[COMPANY_STRUCTURE_LEVEL . '2']) ? $data_form[COMPANY_STRUCTURE_LEVEL . '2'] : 0);
    $SESSION->selection[COMPANY_STRUCTURE_LEVEL . '3']   = (isset($data_form[COMPANY_STRUCTURE_LEVEL . '3']) ? $data_form[COMPANY_STRUCTURE_LEVEL . '3'] : 0);

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

    $SESSION->bulk_users = array();
}//if_else

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    /* Print Title */
    echo $out;
}else {
    /* Print tabs at the top */
    $current_tab = 'manager_reports';
    $show_roles = 1;
    require('../tabs.php');

    if ($show_advanced) {
        $out  = html_writer::start_tag('div',array('class' => 'advance_set_rpt'));
        $out .= html_writer::link(new moodle_url('/report/manager/company_report/company_report.php',array('advanced' => '0')),get_string('hideadvancedsettings'));
        $out .= html_writer::end_tag('div'); //div_expiration
        echo $out;

        /* Add the filters  */
        $user_filter->display_add();
        $user_filter->display_active();
        flush();

        $selector_users->display();
    }else {
        $out  = html_writer::start_tag('div',array('class' => 'advance_set_rpt'));
        $out .= html_writer::link(new moodle_url('/report/manager/company_report/company_report.php',array('advanced' => '1')),get_string('showadvancedsettings'));
        $out .= html_writer::end_tag('div'); //div_expiration
        echo $out;
    }//if_show_advanced

    $form->display();

    /* Initialise Organization Structure    */
    CompetenceManager::Init_Organization_Structure(COMPANY_STRUCTURE_LEVEL,null,null,0,null,false);
}//if_else


/* Print Footer */
echo $OUTPUT->footer();