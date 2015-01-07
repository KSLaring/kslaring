<?php

/**
 * Report generator - Company report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/company_report/
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  12/10/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../locallib.php');
require_once('companyrptlib.php');
require_once($CFG->dirroot . '/report/generator/company_report/filter/lib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once('company_report_form.php');
require_once('user_selector_form.php');

/* Params */
$show_advanced  = optional_param('advanced',0,PARAM_INT);
$format         = optional_param('format','',PARAM_ALPHA);
$return_url   = new moodle_url('/report/generator/index.php');
$url            = new moodle_url('/report/generator/company_report/company_report.php',array('advanced' => $show_advanced));

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}

require_login();

/* Clean Cookies */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelTree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
require_capability('report/generator:viewlevel4', $site_context,$USER->id);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->requires->js(new moodle_url('/report/generator/js/tracker.js'));
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->verify_https_required();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','local_tracker'),$return_url);
$PAGE->navbar->add(get_string('company_report','report_generator'),$url);

/* My Company   */
$company_report = new company_report();

/* Create the user filter   */
$user_filter = new company_report_filtering(null,$url,null);
/* Selector User Form   */
$selector_users = new generator_company_user_selector_form(null,company_report::company_report_getSelectionDate($user_filter));
if ($data = $selector_users->get_data()) {
    if (!empty($data->addall)) {
        company_report::company_report_AddSelectionAll($user_filter);

    } else if (!empty($data->addsel)) {
        if (!empty($data->ausers)) {
            if (in_array(0, $data->ausers)) {
                company_report::company_report_AddSelectionAll($user_filter);
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
    $selector_users = new generator_company_user_selector_form(null, company_report::company_report_getSelectionDate($user_filter));
}


/* Show Form */
$form = new generator_company_report_form(null,$show_advanced);
$out = '';

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    /* Report   */
    if (!$SESSION->bulk_users) {
        company_report::company_report_AddSelectionAll($user_filter);
        company_report::set_UsersFilter($SESSION->bulk_users);
        unset($SESSION->bulk_users);
    }else {
        company_report::set_UsersFilter($SESSION->bulk_users);
    }//if_sesion_users_bulk

    $report             = $company_report::company_report_GetTracker();
    $report->return     = $url;

    if ($report->my_users) {
        switch ($data_form[COMPANY_REPORT_FORMAT_LIST]) {
            case COMPANY_REPORT_FORMAT_SCREEN:
                $out = $company_report::company_report_PrintTracker($report);
                break;
            case COMPANY_REPORT_FORMAT_SCREEN_EXCEL:
                $company_report::company_report_DownloadCompanyReport($report);
                break;
            default:
                break;
        }//switch
    }else {
        $return     = '<a href="'.$url .'">'. get_string('company_report_link','report_generator') .'</a>';
        $out        = get_string('no_data', 'report_generator');
        $out       .=  '<br/>' . $return;
    }//if_else_my_users
}//if_else

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    echo $OUTPUT->heading(get_string('company_report','report_generator'));
    echo '<h2>' . $company_report::get_MyCompany()->name . '</h2>';
    echo $out;
}else {
    /* Print tabs at the top */
    $current_tab = 'company_report';
    $show_roles = 1;
    require('../tabs.php');

    /* Print Title */
    echo $OUTPUT->heading(get_string('company_report','report_generator'));
    echo '<h2>' . $company_report::get_MyCompany()->name . '</h2>';

    if ($show_advanced) {
        $out  = html_writer::start_tag('div',array('class' => 'advance_set'));
        $out .= html_writer::link(new moodle_url('/report/generator/company_report/company_report.php',array('advanced' => '0')),get_string('hideadvancedsettings'));
        $out .= html_writer::end_tag('div'); //div_expiration
        echo $out;

    /* Add the filters  */
    $user_filter->display_add();
    $user_filter->display_active();
    flush();

    $selector_users->display();
    }else {
        $out  = html_writer::start_tag('div',array('class' => 'advance_set'));
        $out .= html_writer::link(new moodle_url('/report/generator/company_report/company_report.php',array('advanced' => '1')),get_string('showadvancedsettings'));
        $out .= html_writer::end_tag('div'); //div_expiration
        echo $out;
    }//if_show_advanced
    $form->display();
}//if_else


/* Print Footer */
echo $OUTPUT->footer();