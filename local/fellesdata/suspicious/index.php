<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Fellesdata Suspicious Integration - Index
 *
 * @package         local/fellesdata
 * @subpackage      suspicious
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    28/12/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('../lib/suspiciouslib.php');
require_once('../lib/fellesdatalib.php');
require_once('index_form.php');

require_login();

/* PARAMS */
$action         = optional_param('a',0,PARAM_INT);
$suspiciousId   = optional_param('id',0,PARAM_INT);
$csv            = optional_param('csv',0,PARAM_INT);
$date_from      = optional_param('f',0,PARAM_INT);
$date_to        = optional_param('t',0,PARAM_INT);
$url            = new moodle_url('/local/fellesdata/suspicious/index.php');
$suspicious     = null;
$error          = NONE_ERROR;
$strMessage     = null;
$name           = null;
$out            = '';
$from           = null;
$to             = null;

/* Guess USer -- Logout */
if (isguestuser($USER)) {
    require_logout();
}//if_guestuser

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('suspicious_header','local_fellesdata'));

if ($csv) {
    // Download the file
    if (!suspicious::download_suspicious_file($suspiciousId)) {
        // Header
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('err_process','local_fellesdata'), 'notifysuccess');
        echo $OUTPUT->continue_button($url);

        // Footer
        echo $OUTPUT->footer();
    }
}else {
    if (($suspiciousId) &&
        ($action == 1) || ($action == 2)) {
        $args = array();
        $args[2] = $suspiciousId;
        $args[0] = $action;
        $args[1] = 0;

        // Apply action
        suspicious::apply_action($args,$error);
        $name = suspicious::get_name($suspiciousId);

        switch ($error) {
            case APPROVED:
                $strMessage = get_string('approved','local_fellesdata',$name);

                break;

            case REJECTED:
                $strMessage = get_string('rejected','local_fellesdata',$name);

                break;

            default:
                $strMessage = get_string('err_process','local_fellesdata');

                break;
        }//switch_error

        // Header
        echo $OUTPUT->header();

        echo $OUTPUT->notification($strMessage, 'notifysuccess');
        if ($date_from && $date_to) {
            $url->param('t',$date_to);
            $url->param('f',$date_from);
        }
        echo $OUTPUT->continue_button($url);

        // Footer
        echo $OUTPUT->footer();
    }else {
        // get suspicious data to show
        // No data --> From today until today
        $date = getdate(time());
        if ($date_from) {
            $from = $date_from;
        }else {
            $from   = mktime(23, 0, 0, $date['mon'], $date['mday']-1, $date['year']);
        }
        if ($date_to) {
            $to = $date_to;
        }else {
            $to = $from;
        }

        $suspicious = suspicious::get_suspicious_files($from,$to);

        // Form
        $form = new suspicious_form(null,array($date_from,$date_to));
        if($data = $form->get_data()) {
            // get data connected with the filter
            $suspicious = suspicious::get_suspicious_files($data->date_from,$data->date_to);
        }//if_form

        // Header
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('suspicious_header','local_fellesdata'));

        $form->display();

        if (isset($data->date_from)) {
            $date_from = $data->date_from;
        }
        if (isset($data->date_to)) {
            $date_to = $data->date_to;
        }

        echo suspicious::display_suspicious_table($suspicious,$date_from,$date_to);

        // Footer
        echo $OUTPUT->footer();
    }
}




